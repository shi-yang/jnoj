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
	oldSource    = "root:123456@tcp(mysql:3306)/jnoj2?charset=utf8mb4&parseTime=True&loc=Local"
	newSource    = "root:123456@tcp(mysql:3306)/jnoj2?charset=utf8mb4&parseTime=True&loc=Local"
	testDataPath = "data"
	bucket       = Bucket{
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
	createDefaultProblemset()
	migrateFuncMap := []func(){
		migrateContestUser,
		migrateContest,
		migrateContestAnnouncement,
		migrateContestProblem,
		migrateGroupUser,
		migrateGroup,
		migrateProblem,
		migratePolygonProblem,
		migratePolygonStatus,
		migrateSetting,
		migrateSolution,
		migrateSolutionInfo,
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
	log.Println("migrate test data start")
	//migrateTestData()
	log.Println("migrate test data done")
}

func createDefaultProblemset() {
	nDB.FirstOrCreate(&v2.Problemset{ID: 1, Name: "默认题单", UserID: 1})
}

func migrateContest() {
	log.Println("migrateContest...")
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

func migrateContestAnnouncement() {
}

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
	nDB.Create(resv2)
}

func migrateContestUser() {
	var resv1 []*v1.ContestUser
	var resv2 []*v2.ContestUser
	oDB.Model(&v1.ContestUser{}).Find(&resv1)
	for _, v := range resv1 {
		resv2 = append(resv2, &v2.ContestUser{
			ID:        v.ID,
			ContestID: v.ContestID,
			UserID:    v.UserID,
			Role:      1, // ContestRoleOfficialPlayer
		})
	}
	nDB.Create(resv2)
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

func migrateProblem() {
	var resv1 []*v1.Problem
	var resv2 []*v2.Problem
	var resStatementV2 []*v2.ProblemStatement
	var problemset []*v2.ProblemsetProblem
	var problemVerification []*v2.ProblemVerification
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
			Status:        1,
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
			spj, err := os.ReadFile(path.Join(testDataPath, strconv.Itoa(v.ID), "spj.cc"))
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
		resv2 = append(resv2, problemV2)
		problemVerification = append(problemVerification, &v2.ProblemVerification{
			ProblemID:          v.ID,
			VerificationStatus: 3,
		})
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
	if err := nDB.Create(problemVerification).Error; err != nil {
		log.Println(err)
	}
}

// 迁移 Polygon 题目，需要注意，V2中不再有单独的Polygon表，因此需要合并
// 合并过程中可能会导致数据重复或者舍弃不一致的数据，优先保留 Problem 表中的数据
func migratePolygonProblem() {
	var resv1 []*v1.PolygonProblem
	oDB.Model(&v1.PolygonProblem{}).Find(&resv1)
	converter := md.NewConverter("", true, nil)
	for _, v := range resv1 {
		var p v1.Problem
		err := oDB.Model(&v1.Problem{}).First(&p, "polygon_problem_id = ?", v.ID).Error
		if err == nil {
			// 如果 problem 中已经有该 polygon 表数据了，则舍弃
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

// 迁移测试数据
// 差异点：
// 1、在v1中，题目样例储存在MySQL数据库中，题目测试点在文件系统中。
// 2、在v2中，样例和测试点在数据库和SeaweedFS中都有储存，没有采用文件系统来进行储存
func migrateTestData() {
	// 第一步，转存数据库中题目的测试样例
	store := objectstorage.NewSeaweed()
	var resv1 []*v1.Problem
	oDB.Model(&v1.Problem{}).Find(&resv1)
	for index, v := range resv1 {
		intmp, _ := gophp.Unserialize([]byte(v.SampleInput))
		outtmp, _ := gophp.Unserialize([]byte(v.SampleOutput))
		if intmp == nil || outtmp == nil {
			continue
		}
		in := intmp.([]interface{})
		out := outtmp.([]interface{})
		for k, test := range in {
			log.Println(index, "/", len(resv1), " - ", k, "/", len(in))
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
	}
	// 第二步，转存文件系统中的测试点
	problems, err := os.ReadDir(testDataPath)
	if err != nil {
		panic(err)
	}
	for index, problem := range problems {
		if !problem.IsDir() {
			continue
		}
		problemId, _ := strconv.Atoi(problem.Name())
		testLists, err := os.ReadDir(path.Join(testDataPath, problem.Name()))
		if err != nil {
			log.Println(err)
			continue
		}
		for tindex, test := range testLists {
			log.Println(index, "/", len(problems), " - ", tindex, "/", len(testLists))
			if !strings.Contains(test.Name(), "in") {
				continue
			}
			nameTmp := strings.Split(test.Name(), ".")
			name := strings.Join(nameTmp[:len(nameTmp)-1], ".")
			// 读取测试点输入、测试点输出
			in, _ := os.ReadFile(path.Join(testDataPath, problem.Name(), name+".in"))
			out, err := os.ReadFile(path.Join(testDataPath, problem.Name(), name+".out"))
			if err != nil {
				out, err = os.ReadFile(path.Join(testDataPath, problem.Name(), name+".ans"))
				if err != nil {
					continue
				}
			}
			o := &v2.ProblemTest{
				ProblemID:  problemId,
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
				o.InputPreview = string(out)
			} else {
				o.InputPreview = string(out[:32]) + "..."
			}
			err = nDB.Create(o).Error
			if err != nil {
				log.Println(err)
			}
			// 保存文件
			storeName := fmt.Sprintf(v2.ProblemTestInputPath, problemId, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader(in))
			if err != nil {
				log.Println(err)
			}
			storeName = fmt.Sprintf(v2.ProblemTestOutputPath, problemId, o.ID)
			err = store.PutObject(bucket, storeName, bytes.NewReader(out))
			if err != nil {
				log.Println(err)
			}
		}
	}
}
