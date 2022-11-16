package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"time"
)

type ProblemVerification struct {
	ID                 int
	ProblemID          int
	VerificationStatus int // 题目完整性
	VerificationInfo   []VerificationInfo
	CreatedAt          time.Time
}

type VerificationInfo struct {
	Action       string
	ErrorMessage string
}

type ProblemVerificationRepo interface {
	CreateOrUpdateProblemVerification(context.Context, *ProblemVerification) error
	GetProblemVerification(context.Context, int) (*ProblemVerification, error)
}

const (
	VerificationStatusPending = iota + 1 // 待验证
	VerificationStatusFail               // 验证失败
	VerificationStatusSuccess            // 验证成功
)

const (
	VerificationInfoActionTest        = "test"
	VerificationInfoActionStatement   = "statement"
	VerificationInfoActionSolution    = "solution"
	VerificationInfoActionChecker     = "checker"
	VerificationInfoActionRunSolution = "runSolution"
)

func (uc *ProblemUsecase) GetProblemVerification(ctx context.Context, id int) (*ProblemVerification, error) {
	return uc.repo.GetProblemVerification(ctx, id)
}

// VerifyProblem 验证题目完整性
// TODO 该函数需要更加完善
func (uc *ProblemUsecase) VerifyProblem(ctx context.Context, id int) error {
	go uc.verifyProblem(ctx, id)
	return nil
}

// 1. 题目描述 ProblemStatement
// 2. 存在测试点、样例
// 3. 存在 model_solution 标程，并可运行
// 4. 基于 model_solution 生成测试点的输出
func (uc *ProblemUsecase) verifyProblem(ctx context.Context, id int) error {
	ctx = context.TODO()
	problem, _ := uc.repo.GetProblem(ctx, id)
	var res ProblemVerification
	res.ProblemID = id
	res.VerificationStatus = VerificationStatusPending
	uc.log.Info("VerificationInfoActionTest")
	t, _ := uc.repo.ListProblemSampleTest(ctx, id)
	if len(t) == 0 {
		res.VerificationInfo = append(res.VerificationInfo, VerificationInfo{
			Action:       VerificationInfoActionTest,
			ErrorMessage: "no sample file",
		})
	}
	uc.log.Info("VerificationInfoActionStatement")
	statements, _ := uc.repo.ListProblemStatements(ctx, &v1.ListProblemStatementsRequest{Id: int32(id)})
	if len(statements) == 0 {
		res.VerificationInfo = append(res.VerificationInfo, VerificationInfo{
			Action:       VerificationInfoActionStatement,
			ErrorMessage: "no statements",
		})
	}
	uc.log.Info("VerificationInfoActionSolution")
	problemFiles, _ := uc.repo.ListProblemFiles(ctx, &v1.ListProblemFilesRequest{Id: int32(id)})
	modelSolutionIndex := -1
	for k, v := range problemFiles {
		if v.Type == ProblemFileTypeModelSolution {
			modelSolutionIndex = k
		}
	}
	if modelSolutionIndex == -1 {
		res.VerificationInfo = append(res.VerificationInfo, VerificationInfo{
			Action:       VerificationInfoActionSolution,
			ErrorMessage: "no model solution",
		})
	} else {
		uc.log.Info("VerificationInfoActionRunSolution")
		if err := uc.RunProblemFile(context.TODO(), problemFiles[modelSolutionIndex].ID); err != nil {
			res.VerificationInfo = append(res.VerificationInfo, VerificationInfo{
				Action:       VerificationInfoActionRunSolution,
				ErrorMessage: "run model solution error",
			})
		}
	}
	uc.log.Info("VerificationInfoActionChecker")
	// 检查 checker
	if problem.CheckerID == 0 {
		res.VerificationInfo = append(res.VerificationInfo, VerificationInfo{
			Action:       VerificationInfoActionChecker,
			ErrorMessage: "no checker file",
		})
	}
	if len(res.VerificationInfo) == 0 {
		res.VerificationStatus = VerificationStatusSuccess
	} else {
		res.VerificationStatus = VerificationStatusFail
	}

	return uc.repo.CreateOrUpdateProblemVerification(context.TODO(), &res)
}
