package main

import (
	"bytes"
	"fmt"
	objectstorage "jnoj/pkg/object_storage"
	v1 "jnoj/tools/upgrade/v1"
	v2 "jnoj/tools/upgrade/v2"
	"log"
	"os"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

var (
	oldSource    = "root:123456@tcp(mysql:3306)/jnoj2?charset=utf8mb4&parseTime=True&loc=Local"
	newSource    = "root:123456@tcp(mysql:3306)/jnoj2?charset=utf8mb4&parseTime=True&loc=Local"
	testDataPath = ""
	bucket       = Bucket{}
)

var (
	oDB *gorm.DB
	nDB *gorm.DB
)

func main() {
	var err error
	oDB, err = connectDB(oldSource)
	if err != nil {
		panic(err)
	}
	nDB, err = connectDB(newSource)
	if err != nil {
		panic(err)
	}
	createDefaultProblemset()
	migrateFuncMap := map[string]func(){
		//"contest_user": migrateContestUser,
		// "contest": migrateContest,
		// "contest_announcement": migrateContestAnnouncement,
		// "contest_problem":      migrateContestProblem,
		// "group_user": migrateGroupUser,
		// "group": migrateGroup,
		"problem": migrateProblem,
		// "polygon_problem":      migratePolygonProblem,
		// "polygon_status":       migratePolygonStatus,
		// "setting":              migrateSetting,
		// "solution": migrateSolution,
		// "solution_info":        migrateSolutionInfo,
		// "user":                 migrateUser,
		// "user_profile":         migrateUserProfile,
	}
	log.Println("migrate db start")
	for k, v := range migrateFuncMap {
		log.Println("migrate", k, "start...")
		v()
		log.Println("migrate", k, "done")
	}
	log.Println("migrate db done")
	// log.Println("migrate test data start")
	// migrateTestData()
	// log.Println("migrate test data done")
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
			Type:             v.Type,
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
		resv2 = append(resv2, &v2.ContestProblem{
			ID:        v.ID,
			ContestID: v.ContestID,
			ProblemID: v.ProblemID,
			Number:    v.Num,
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
	oDB.Model(&v1.Problem{}).Find(&resv1)
	for key, v := range resv1 {
		resv2 = append(resv2, &v2.Problem{
			ID:            v.ID,
			Name:          v.Title,
			TimeLimit:     int64(v.TimeLimit * 1000),
			MemoryLimit:   int64(v.MemoryLimit),
			AcceptedCount: v.Accepted,
			SubmitCount:   v.Submit,
			UserID:        v.CreatedBy,
			Status:        v.Status,
			Source:        v.Source,
		})
		resStatementV2 = append(resStatementV2, &v2.ProblemStatement{
			ProblemID: v.ID,
			Name:      v.Title,
			Legend:    v.Description,
			Language:  "中文",
			Input:     v.Input,
			Output:    v.Ouput,
			Note:      v.Hint,
			UserID:    v.CreatedBy,
		})
		problemset = append(problemset, &v2.ProblemsetProblem{
			ProblemID:    v.ID,
			ProblemsetID: 1,
			Order:        key,
		})
	}
	if err := nDB.Create(resv2); err != nil {
		log.Println(err)
	}
	if err := nDB.Create(resStatementV2); err != nil {
		log.Println(err)
	}
	if err := nDB.Create(problemset); err != nil {
		log.Println(err)
	}
}

// 迁移 Polygon 题目，需要注意，V2中不再有单独的Polygon表，因此需要合并
// 合并过程中可能会导致数据重复或者舍弃不一致的数据，优先保留 Problem 表中的数据
func migratePolygonProblem() {
	var resv1 []*v1.PolygonProblem
	var resv2 []*v2.Problem
	var resStatementV2 []*v2.ProblemStatement
	oDB.Model(&v1.PolygonProblem{}).Find(&resv1)
	for _, v := range resv1 {
		var p v2.Problem
		err := oDB.Model(&v1.Problem{}).First(&p, "polygon_id = ?", v.ID).Error
		if err == nil {
			// 如果 problem 中已经有该 polygon 表数据了，则舍弃
			continue
		}
		resv2 = append(resv2, &v2.Problem{
			ID:            v.ID,
			Name:          v.Title,
			TimeLimit:     int64(v.TimeLimit * 1000),
			MemoryLimit:   int64(v.MemoryLimit),
			AcceptedCount: v.Accepted,
			SubmitCount:   v.Submit,
			UserID:        v.CreatedBy,
			Status:        v.Status,
			Source:        v.Source,
		})
		resStatementV2 = append(resStatementV2, &v2.ProblemStatement{
			ProblemID: v.ID,
			Name:      v.Title,
			Legend:    v.Description,
			Language:  "中文",
			Input:     v.Input,
			Output:    v.Ouput,
			Note:      v.Hint,
			UserID:    v.CreatedBy,
		})
	}
	nDB.Create(resv2)
	nDB.Create(resStatementV2)
}

func migratePolygonStatus() {
	var resv1 []*v1.PolygonStatus
	var resv2 []*v2.Submission
	oDB.Model(&v1.PolygonStatus{}).Find(&resv1)
	for _, v := range resv1 {
		resv2 = append(resv2, &v2.Submission{
			ProblemID:  v.ProblemID,
			Time:       v.Time,
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
			Time:      v.Time,
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
	err := nDB.Create(resv2)
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

func migrateTestData() {
	_, err := os.ReadDir(testDataPath)
	if err != nil {
		panic(err)
	}
	var resv1 []*v1.Problem
	var resv2 []*v2.Problem
	var resStatementV2 []*v2.ProblemStatement
	oDB.Model(&v1.Problem{}).Find(&resv1)
	for _, v := range resv1 {
		InputFileContent := []byte("")
		o := &v2.ProblemTest{
			ProblemID:     v.ID,
			Name:          "",
			IsExample:     true,
			InputSize:     int64(len(v.SampleInput)),
			InputPreview:  v.SampleInput,
			OutputSize:    int64(len(v.SampleInput)),
			OutputPreview: v.SampleOutput,
		}
		// 保存文件
		if len(o.InputPreview) > 0 {
			store := objectstorage.NewSeaweed()
			storeName := fmt.Sprintf(v2.ProblemTestInputPath, v.ID, o.ID)
			err := store.PutObject(bucket, storeName, bytes.NewReader(InputFileContent))
			if err != nil {
				log.Println(err)
			}
		}
		err := nDB.Create(o).Error
		if err != nil {
			log.Println(err)
		}
	}
	nDB.Create(resv2)
	nDB.Create(resStatementV2)
}
