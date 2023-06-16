package main

import (
	"bytes"
	"fmt"
	v1 "jnoj/tools/upgrade/v1"
	v2 "jnoj/tools/upgrade/v2"
	"log"
	"os"
	"path"
	"strconv"
	"strings"

	objectstorage "jnoj/pkg/object_storage"

	md "github.com/JohannesKaufmann/html-to-markdown"

	"github.com/google/uuid"
	"github.com/leeqvip/gophp"
	"gorm.io/gorm"
)

var (
	// 旧OJ数据库地址
	oldSource = "root:123456@tcp(mysql:3306)/jnoj?charset=utf8mb4&parseTime=True&loc=Local"
	// 新OJ数据库地址
	newSource = "root:123456@tcp(mysql:3306)/jnoj2?charset=utf8mb4&parseTime=True&loc=Local"
	// 旧OJ对应的 judge/data 目录地址
	problemTestDataPath = "data"
	// 旧OJ对应的 polygon/data 目录地址
	polygonTestDataPath = "data"
	bucket              = Bucket{
		Bucket:    "private",
		SecretId:  "jnoj_access_key1",
		SecretKey: "jnoj_secret_key1",
		Endpoint:  "http://jnoj-s3:8333",
	}
)

var (
	oDB *gorm.DB
	nDB *gorm.DB
)

func init() {
	var err error
	oDB, err = connectDB(oldSource)
	if err != nil {
		panic(err)
	}
	nDB, err = connectDB(newSource)
	if err != nil {
		panic(err)
	}
	// 写入错误日志
	logFile, err := os.OpenFile("./log.txt", os.O_RDWR|os.O_CREATE|os.O_APPEND, 0776)
	if err != nil {
		panic(err)
	}
	log.SetOutput(logFile)
	log.SetFlags(log.LstdFlags | log.Lshortfile | log.LUTC)
}

func main() {
	// 迁移过程，将依次执行以下函数
	migrateFuncMap := []func(){
		createDefaultProblemset,
		migrateContestUser,
		migrateContest,
		migrateContestAnnouncement,
		migrateContestProblem,
		migrateGroupUser,
		migrateGroup,
		migrateProblem,
		migrateSolution,
		migrateSolutionInfo,
		migratePolygonProblem,
		migratePolygonStatus,
		migrateSetting,
		migrateUser,
		migrateUserProfile,
	}
	log.Println("migrate db start")
	for k, v := range migrateFuncMap {
		log.Println("migrate", k, "start...")
		v()
		log.Println("migrate", k, "done")
	}
	log.Println("migrate db done")
}

// 创建默认题单
func createDefaultProblemset() {
	nDB.FirstOrCreate(&v2.Problemset{ID: 1, Name: "默认题单", UserID: 1})
}

// 迁移比赛
func migrateContest() {
	var resv1 []*v1.Contest
	var resv2 []*v2.Contest
	var count []struct {
		ContestID int
		Count     int
	}
	var countMap = make(map[int]int)
	oDB.Model(&v1.Contest{}).Find(&resv1)
	oDB.Model(&v1.ContestUser{}).Select("contest_id, count(contest_id) as count").Group("contest_id").Find(&count)
	for _, v := range count {
		countMap[v.ContestID] = v.Count
	}
	for _, v := range resv1 {
		if v.StartTime.IsZero() || v.EndTime.IsZero() {
			continue
		}
		resv2 = append(resv2, &v2.Contest{
			ID:               v.ID,
			Name:             v.Title,
			StartTime:        v.StartTime,
			EndTime:          v.EndTime,
			FrozenTime:       v.LockBoardTime,
			Type:             ContestType[v.Type],
			Privacy:          ContestStatusMap[v.Status],
			Membership:       0,
			Description:      v.Description,
			ParticipantCount: countMap[v.ID],
			GroupID:          v.GroupID,
			UserID:           v.CreatedBy,
		})
	}
	err := nDB.Create(resv2).Error
	if err != nil {
		log.Println(err)
	}
}

// 迁移比赛公告
func migrateContestAnnouncement() {
}

// 迁移比赛题目
func migrateContestProblem() {
	var resv1 []*v1.ContestProblem
	var resv2 []*v2.ContestProblem
	oDB.Model(&v1.ContestProblem{}).Find(&resv1)
	for _, v := range resv1 {
		var ac, sc int
		oDB.Select("count(*)").Model(&v1.Solution{}).
			Where("contest_id = ? and problem_id = ?", v.ContestID, v.ProblemID).
			Where("result = 4").
			Scan(&ac)
		oDB.Select("count(*)").Model(&v1.Solution{}).
			Where("contest_id = ? and problem_id = ?", v.ContestID, v.ProblemID).
			Scan(&sc)
		resv2 = append(resv2, &v2.ContestProblem{
			ID:            v.ID,
			ContestID:     v.ContestID,
			ProblemID:     v.ProblemID,
			Number:        v.Num,
			AcceptedCount: ac,
			SubmitCount:   sc,
		})
	}
	err := nDB.Create(resv2).Error
	if err != nil {
		log.Println(err)
	}
}

func migrateContestUser() {
	var resv1 []*v1.ContestUser
	var resv2 []*v2.ContestUser
	oDB.Model(&v1.ContestUser{}).Find(&resv1)
	for _, v := range resv1 {
		u := &v2.ContestUser{
			ID:        v.ID,
			ContestID: v.ContestID,
			UserID:    v.UserID,
			Role:      v2.ContestRoleOfficialPlayer, // ContestRoleOfficialPlayer
		}
		resv2 = append(resv2, u)
	}
	err := nDB.Create(resv2).Error
	if err != nil {
		log.Println(err)
	}
}

func migrateGroupUser() {
	var resv1 []*v1.GroupUser
	var resv2 []*v2.GroupUser
	oDB.Model(&v1.GroupUser{}).Find(&resv1)
	for _, v := range resv1 {
		resv2 = append(resv2, &v2.GroupUser{
			ID:        v.ID,
			UserID:    v.UserID,
			GroupID:   v.GroupID,
			Role:      GroupUserRole[v.Role],
			CreatedAt: v.CreatedAt,
		})
	}
	nDB.Create(resv2)
}

func migrateGroup() {
	var resv1 []*v1.Group
	var resv2 []*v2.Group
	oDB.Model(&v1.Group{}).Find(&resv1)
	for _, v := range resv1 {
		var count int64
		oDB.Model(&v1.GroupUser{}).Where("group_id = ?", v.ID).Count(&count)
		resv2 = append(resv2, &v2.Group{
			ID:             v.ID,
			Name:           v.Name,
			Description:    v.Description,
			Privacy:        GroupStatusMap[v.Status],
			Membership:     GroupJoinPolicyMap[v.JoinPolicy],
			MemberCount:    int(count),
			InvitationCode: uuid.New().String()[:6],
			UserID:         v.CreatedBy,
			CreatedAt:      v.CreatedAt,
		})
	}
	nDB.Create(resv2)
}

// 迁移 Problem
// 注意事项：
// 1. 迁移Problem会保留Problem.ID的顺序
// 2. 迁移题目的同时会迁移测试数据
// 3. 尚不支持迁移题目中出现的图片
// 差异点：
// 1、在v1中，题目样例储存在MySQL数据库中，题目测试点在文件系统中。
// 2、在v2中，样例和测试点在数据库和SeaweedFS中都有储存，没有采用文件系统来进行储存
func migrateProblem() {
	var resv1 []*v1.Problem
	var resv2 []*v2.Problem
	var resStatementV2 []*v2.ProblemStatement
	var problemset []*v2.ProblemsetProblem
	var problemVerification []*v2.ProblemVerification
	store := objectstorage.NewSeaweed()
	converter := md.NewConverter("", true, &md.Options{
		EscapeMode: "none",
	})
	oDB.Model(&v1.Problem{}).Find(&resv1)
	for key, v := range resv1 {
		problemV2 := &v2.Problem{
			ID:            v.ID,
			Name:          v.Title,
			TimeLimit:     int64(v.TimeLimit * 1000),
			MemoryLimit:   int64(v.MemoryLimit),
			AcceptedCount: v.Accepted,
			SubmitCount:   v.Submit,
			CheckerID:     1002,
			UserID:        v.CreatedBy,
			Status:        1, // 私有
			Source:        v.Source,
		}
		statement := &v2.ProblemStatement{
			ProblemID: v.ID,
			Name:      v.Title,
			Language:  "中文",
			UserID:    v.CreatedBy,
		}
		statement.Legend, _ = converter.ConvertString(v.Description)
		statement.Input, _ = converter.ConvertString(v.Input)
		statement.Output, _ = converter.ConvertString(v.Output)
		statement.Note, _ = converter.ConvertString(v.Hint)
		resStatementV2 = append(resStatementV2, statement)
		// 只有公开的题目才将其加入到默认题库中
		if v.Status == 1 {
			problemset = append(problemset, &v2.ProblemsetProblem{
				ProblemID:    v.ID,
				ProblemsetID: 1,
				Order:        key + 1,
			})
		}
		// 处理SPJ
		if v.Spj {
			spj, err := os.ReadFile(path.Join(problemTestDataPath, strconv.Itoa(v.ID), "spj.cc"))
			if err == nil {
				checker := &v2.ProblemFile{
					ProblemID: v.ID,
					Language:  1, // C++
					Content:   string(spj),
					FileType:  string(v2.ProblemFileFileTypeChecker),
					FileSize:  int64(len(spj)),
				}
				err := nDB.Create(checker).Error
				if err != nil {
					log.Println("problem spj err:", v.ID, err)
				}
				// 修改题目spj
				problemV2.CheckerID = checker.ID
			}
		}
		// 处理子任务
		subtask, err := os.ReadFile(path.Join(problemTestDataPath, strconv.Itoa(v.ID), "config"))
		if err == nil {
			f := &v2.ProblemFile{
				ProblemID: v.ID,
				Language:  1, // C++
				Content:   string(subtask),
				FileType:  string(v2.ProblemFileFileTypeSubtask),
				FileSize:  int64(len(subtask)),
			}
			err := nDB.Create(f).Error
			if err != nil {
				log.Println("problem subtask err:", v.ID, err)
			}
		}
		resv2 = append(resv2, problemV2)
		problemVerification = append(problemVerification, &v2.ProblemVerification{
			ProblemID:          v.ID,
			VerificationStatus: 3, // 通过验证
		})

		// 处理样例测试数据
		intmp, _ := gophp.Unserialize([]byte(v.SampleInput))
		outtmp, _ := gophp.Unserialize([]byte(v.SampleOutput))
		if intmp == nil || outtmp == nil {
			continue
		}
		in := intmp.([]interface{})
		out := outtmp.([]interface{})
		for k, test := range in {
			if test == nil || out[k] == nil {
				continue
			}
			o := &v2.ProblemTest{
				ProblemID:     v.ID,
				Name:          "",
				IsExample:     true,
				InputSize:     int64(len(test.(string))),
				InputPreview:  test.(string),
				OutputSize:    int64(len(out[k].(string))),
				OutputPreview: out[k].(string),
			}
			err := nDB.Create(o).Error
			if err != nil {
				log.Println(err)
			}
			// 保存文件
			store := objectstorage.NewSeaweed()
			storeName := fmt.Sprintf(v2.ProblemTestInputPath, v.ID, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader([]byte(test.(string))))
			if err != nil {
				log.Println(err)
			}
			storeName = fmt.Sprintf(v2.ProblemTestOutputPath, v.ID, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader([]byte(out[k].(string))))
			if err != nil {
				log.Println(err)
			}
		}

		testLists, err := os.ReadDir(path.Join(problemTestDataPath, strconv.Itoa(v.ID)))
		if err != nil {
			log.Println(err)
			continue
		}
		for _, test := range testLists {
			if !strings.Contains(test.Name(), "in") {
				continue
			}
			nameTmp := strings.Split(test.Name(), ".")
			name := strings.Join(nameTmp[:len(nameTmp)-1], ".")
			// 读取测试点输入、测试点输出
			in, _ := os.ReadFile(path.Join(problemTestDataPath, strconv.Itoa(v.ID), name+".in"))
			out, err := os.ReadFile(path.Join(problemTestDataPath, strconv.Itoa(v.ID), name+".out"))
			if err != nil {
				out, err = os.ReadFile(path.Join(problemTestDataPath, strconv.Itoa(v.ID), name+".ans"))
				if err != nil {
					continue
				}
			}
			o := &v2.ProblemTest{
				ProblemID:  v.ID,
				Name:       name,
				InputSize:  int64(len(in)),
				OutputSize: int64(len(out)),
			}
			// 读取 32 个字符作为内容
			if len(in) < 32 {
				o.InputPreview = string(in)
			} else {
				o.InputPreview = string(in[:32]) + "..."
			}
			if len(out) < 32 {
				o.OutputPreview = string(out)
			} else {
				o.OutputPreview = string(out[:32]) + "..."
			}
			err = nDB.Create(o).Error
			if err != nil {
				log.Println(err)
			}
			// 保存文件
			storeName := fmt.Sprintf(v2.ProblemTestInputPath, v.ID, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader(in))
			if err != nil {
				log.Println(err)
			}
			storeName = fmt.Sprintf(v2.ProblemTestOutputPath, v.ID, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader(out))
			if err != nil {
				log.Println(err)
			}
		}
	}
	if err := nDB.Create(resv2).Error; err != nil {
		log.Println(err)
	}
	if err := nDB.Create(resStatementV2).Error; err != nil {
		log.Println(err)
	}
	if err := nDB.Create(problemset).Error; err != nil {
		log.Println(err)
	}
	nDB.Model(&v2.Problemset{ID: 1}).
		UpdateColumn("problem_count", len(problemset))
	if err := nDB.Create(problemVerification).Error; err != nil {
		log.Println(err)
	}
}

// 迁移 Polygon 题目
// 注意事项:
// 1. V2中不再有单独的Polygon表，因此需要合并
// 2. 合并过程中可能会导致数据重复或者舍弃不一致的数据，如果 Problem 中已经存在，则舍弃该 Polygon 中的数据
// 3. Polygon 题目ID会发生改变，迁移会造成 Polygon.ID 在 Problem 迁移后进行自增
// 4. 迁移的同时会迁移测试数据
// 5. 尚不支持迁移题目中出现的图片
// 6. 迁移会在 v1 的数据库中建立一张名为 polygon_migrate_v2 的表，来储存映射，以防万一
func migratePolygonProblem() {
	var resv1 []*v1.PolygonProblem
	type PolygonMigrateV2 struct {
		ID        int `gorm:"primaryKey"`
		PolygonID int
		ProblemID int
	}
	var polygonMigrates []*PolygonMigrateV2
	err1 := oDB.Migrator().CreateTable(&PolygonMigrateV2{}).Error()
	if err1 != "" {
		log.Println(err1)
		return
	}

	oDB.Model(&v1.PolygonProblem{}).Find(&resv1)
	converter := md.NewConverter("", true, &md.Options{
		EscapeMode: "none",
	})
	store := objectstorage.NewSeaweed()
	for index, v := range resv1 {
		log.Println("migratePolygonProblem, ", index, " / ", len(resv1))
		var p v1.Problem
		err := oDB.Model(&v1.Problem{}).First(&p, "polygon_problem_id = ?", v.ID).Error
		// 如果 problem 中已经有该 polygon 表数据了，则舍弃
		if err == nil {
			continue
		}
		p2 := &v2.Problem{
			Name:          v.Title,
			TimeLimit:     int64(v.TimeLimit * 1000),
			MemoryLimit:   int64(v.MemoryLimit),
			AcceptedCount: v.Accepted,
			SubmitCount:   v.Submit,
			UserID:        v.CreatedBy,
			Status:        v.Status,
			Source:        v.Source,
		}
		nDB.Create(&p2)
		polygonMigrates = append(polygonMigrates, &PolygonMigrateV2{
			PolygonID: v.ID,
			ProblemID: p2.ID,
		})
		statement := &v2.ProblemStatement{
			ProblemID: p2.ID,
			Name:      v.Title,
			Language:  "中文",
			UserID:    v.CreatedBy,
		}
		statement.Legend, _ = converter.ConvertString(v.Description)
		statement.Input, _ = converter.ConvertString(v.Input)
		statement.Output, _ = converter.ConvertString(v.Output)
		statement.Note, _ = converter.ConvertString(v.Hint)
		nDB.Create(statement)
		// 处理SPJ
		if v.Spj {
			spj, err := os.ReadFile(path.Join(polygonTestDataPath, strconv.Itoa(v.ID), "spj.cc"))
			if err == nil {
				checker := &v2.ProblemFile{
					ProblemID: p2.ID,
					Language:  1, // C++
					Content:   string(spj),
					FileType:  string(v2.ProblemFileFileTypeChecker),
					FileSize:  int64(len(spj)),
				}
				err := nDB.Create(checker).Error
				if err != nil {
					log.Println("problem spj err:", v.ID, err)
				}
				// 修改题目spj
				nDB.Model(&v2.Problem{ID: p2.ID}).
					UpdateColumn("checker_id", checker.ID)
			}
		}
		// 处理子任务
		subtask, err := os.ReadFile(path.Join(polygonTestDataPath, strconv.Itoa(v.ID), "config"))
		if err == nil {
			f := &v2.ProblemFile{
				ProblemID: p2.ID,
				Language:  1, // C++
				Content:   string(subtask),
				FileType:  string(v2.ProblemFileFileTypeSubtask),
				FileSize:  int64(len(subtask)),
			}
			err := nDB.Create(f).Error
			if err != nil {
				log.Println("polygon subtask err:", "v1.id", v.ID, " v2.id", p2.ID, err)
			}
		}

		// 迁移样例测试数据
		intmp, _ := gophp.Unserialize([]byte(v.SampleInput))
		outtmp, _ := gophp.Unserialize([]byte(v.SampleOutput))
		if intmp == nil || outtmp == nil {
			continue
		}
		in := intmp.([]interface{})
		out := outtmp.([]interface{})
		for k, test := range in {
			if test == nil || out[k] == nil {
				continue
			}
			o := &v2.ProblemTest{
				ProblemID:     p2.ID,
				Name:          fmt.Sprintf("sample_%d", k+1),
				IsExample:     true,
				InputSize:     int64(len(test.(string))),
				InputPreview:  substr32(test.(string)),
				OutputSize:    int64(len(out[k].(string))),
				OutputPreview: substr32(out[k].(string)),
			}
			err := nDB.Create(o).Error
			if err != nil {
				log.Println("create polygon problem sample err: ", p2.ID, err.Error())
				continue
			}
			// 保存文件
			store := objectstorage.NewSeaweed()
			storeName := fmt.Sprintf(v2.ProblemTestInputPath, v.ID, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader([]byte(test.(string))))
			if err != nil {
				log.Println("create polygon problem in sample err: ", p2.ID, err.Error())
			}
			storeName = fmt.Sprintf(v2.ProblemTestOutputPath, v.ID, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader([]byte(out[k].(string))))
			if err != nil {
				log.Println("create polygon problem out sample err: ", p2.ID, err.Error())
			}
		}
		// 迁移测试数据
		testLists, err := os.ReadDir(path.Join(polygonTestDataPath, strconv.Itoa(v.ID)))
		if err != nil {
			log.Println(err)
			continue
		}
		for _, test := range testLists {
			if !strings.Contains(test.Name(), "in") {
				continue
			}
			nameTmp := strings.Split(test.Name(), ".")
			name := strings.Join(nameTmp[:len(nameTmp)-1], ".")
			// 读取测试点输入、测试点输出
			in, _ := os.ReadFile(path.Join(polygonTestDataPath, strconv.Itoa(v.ID), name+".in"))
			out, err := os.ReadFile(path.Join(polygonTestDataPath, strconv.Itoa(v.ID), name+".out"))
			if err != nil {
				out, err = os.ReadFile(path.Join(polygonTestDataPath, strconv.Itoa(v.ID), name+".ans"))
				if err != nil {
					continue
				}
			}
			o := &v2.ProblemTest{
				ProblemID:  p2.ID,
				Name:       name,
				InputSize:  int64(len(in)),
				OutputSize: int64(len(out)),
			}
			// 读取 32 个字符作为内容
			if len(in) < 32 {
				o.InputPreview = string(in)
			} else {
				o.InputPreview = string(in[:32]) + "..."
			}
			if len(out) < 32 {
				o.OutputPreview = string(out)
			} else {
				o.OutputPreview = string(out[:32]) + "..."
			}
			err = nDB.Create(o).Error
			if err != nil {
				log.Println(err)
			}
			// 保存文件
			storeName := fmt.Sprintf(v2.ProblemTestInputPath, p2.ID, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader(in))
			if err != nil {
				log.Println(err)
			}
			storeName = fmt.Sprintf(v2.ProblemTestOutputPath, p2.ID, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader(out))
			if err != nil {
				log.Println(err)
			}
		}
	}
	err := oDB.Create(polygonMigrates).Error
	if err != nil {
		log.Println("polygon migrate err", err.Error())
	}
}

func migratePolygonStatus() {
	var resv1 []*v1.PolygonStatus
	var resv2 []*v2.Submission
	oDB.Model(&v1.PolygonStatus{}).Find(&resv1)
	for _, v := range resv1 {
		resv2 = append(resv2, &v2.Submission{
			ProblemID:  v.ProblemID,
			Time:       v.Time * 1000,
			Memory:     v.Memory,
			Verdict:    VerdictMap[v.Result],
			Language:   v.Language,
			UserID:     v.CreatedBy,
			EntityID:   v.ID,
			EntityType: v2.SubmissionEntityTypeProblemFile,
			Source:     v.Source,
			CreatedAt:  v.CreatedAt,
		})
	}
	nDB.Create(resv2)
}

func migrateSetting() {
}

func migrateSolution() {
	var resv1 []*v1.Solution
	var resv2 []*v2.Submission
	oDB.Model(&v1.Solution{}).Find(&resv1)
	for _, v := range resv1 {
		s := &v2.Submission{
			ID:        v.ID,
			ProblemID: v.ProblemID,
			Time:      v.Time * 1000,
			Memory:    v.Memory,
			Verdict:   VerdictMap[v.Result],
			Language:  v.Language,
			Score:     v.Score,
			UserID:    v.CreatedBy,
			EntityID:  v.ContestID,
			Source:    v.Source,
			CreatedAt: v.CreatedAt,
		}
		if v.ContestID != 0 {
			s.EntityType = v2.SubmissionEntityTypeContest
		} else {
			s.EntityID = 1
		}
		resv2 = append(resv2, s)
	}
	err := nDB.Create(resv2).Error
	if err != nil {
		log.Println(err)
	}
}

func migrateSolutionInfo() {
	// var resv1 []*v1.SolutionInfo
	// var resv2 []*v2.SubmissionInfo
	// oDB.Model(&v1.SolutionInfo{}).Find(&resv1)
	// for _, v := range resv1 {
	// 	resv2 = append(resv2, &v2.SubmissionInfo{
	// 		SubmissionID: v.SolutionID,
	// 		RunInfo:      v.RunInfo,
	// 	})
	// }
	// nDB.Create(resv2)
}

func migrateUser() {
	var resv1 []*v1.User
	var resv2 []*v2.User
	oDB.Model(&v1.User{}).Find(&resv1)
	for _, v := range resv1 {
		resv2 = append(resv2, &v2.User{
			ID:        v.ID,
			Username:  v.Username,
			Nickname:  v.Nickname,
			Password:  v.PasswordHash,
			Email:     v.Email,
			CreatedAt: v.CreatedAt,
			UpdatedAt: v.UpdatedAt,
		})
	}
	nDB.Create(resv2)
}

func migrateUserProfile() {
}

// 读取 32 个字符作为内容
func substr32(str string) string {
	if len(str) < 32 {
		return str
	}
	return str[:32]
}
