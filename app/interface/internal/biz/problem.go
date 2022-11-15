package biz

import (
	"context"
	"errors"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Problem is a Problem model.
type Problem struct {
	ID                 int
	Name               string
	UserID             int
	TimeLimit          int64
	MemoryLimit        int64
	AcceptedCount      int
	SubmitCount        int
	VerificationStatus int // 题目完整性
	CreatedAt          time.Time
	UpdatedAt          time.Time

	Statements  []*ProblemStatement
	SampleTests []*SampleTest
}

const (
	VerificationStatusPending = iota + 1
	VerificationStatusFail
	VerificationStatusSuccess
)

// ProblemRepo is a Problem repo.
type ProblemRepo interface {
	ListProblems(context.Context, *v1.ListProblemsRequest) ([]*Problem, int64)
	GetProblem(context.Context, int) (*Problem, error)
	CreateProblem(context.Context, *Problem) (*Problem, error)
	UpdateProblem(context.Context, *Problem) (*Problem, error)
	DeleteProblem(context.Context, int) error

	UpdateProblemChecker(context.Context, int, int) error
	ProblemTestRepo
	ProblemStatementRepo
	ProblemFileRepo
}

// ProblemUsecase is a Problem usecase.
type ProblemUsecase struct {
	repo          ProblemRepo
	sandboxClient sandboxV1.SandboxServiceClient
	log           *log.Helper
}

// NewProblemUsecase new a Problem usecase.
func NewProblemUsecase(repo ProblemRepo, sandboxClient sandboxV1.SandboxServiceClient, logger log.Logger) *ProblemUsecase {
	return &ProblemUsecase{repo: repo, sandboxClient: sandboxClient, log: log.NewHelper(logger)}
}

// ListProblems list Problem
func (uc *ProblemUsecase) ListProblems(ctx context.Context, req *v1.ListProblemsRequest) ([]*Problem, int64) {
	return uc.repo.ListProblems(ctx, req)
}

// GetProblem get a Problem
func (uc *ProblemUsecase) GetProblem(ctx context.Context, id int) (*Problem, error) {
	p, err := uc.repo.GetProblem(ctx, id)
	if err != nil {
		return nil, err
	}
	statements, _ := uc.repo.ListProblemStatements(ctx, &v1.ListProblemStatementsRequest{
		Id: int32(id),
	})
	tests, _ := uc.repo.ListProblemSampleTest(ctx, id)
	for _, v := range statements {
		p.Statements = append(p.Statements, &ProblemStatement{
			ID:       v.ID,
			Input:    v.Input,
			Output:   v.Output,
			Name:     v.Name,
			Legend:   v.Legend,
			Language: v.Language,
		})
	}
	for _, v := range tests {
		p.SampleTests = append(p.SampleTests, &SampleTest{
			Input:  v.Input,
			Output: v.Output,
		})
	}
	return p, nil
}

// CreateProblem creates a Problem, and returns the new Problem.
func (uc *ProblemUsecase) CreateProblem(ctx context.Context, p *Problem) (*Problem, error) {
	userID, _ := auth.GetUserID(ctx)
	p.UserID = userID
	p.MemoryLimit = 256
	p.TimeLimit = 1000
	return uc.repo.CreateProblem(ctx, p)
}

// UpdateProblem update a Problem
func (uc *ProblemUsecase) UpdateProblem(ctx context.Context, p *Problem) (*Problem, error) {
	if !uc.hasUpdatePermission(ctx, p.ID) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	return uc.repo.UpdateProblem(ctx, p)
}

// DeleteProblem delete a Problem
func (uc *ProblemUsecase) DeleteProblem(ctx context.Context, id int) error {
	if !uc.hasUpdatePermission(ctx, id) {
		return v1.ErrorPermissionDenied("permission denied")
	}
	return uc.repo.DeleteProblem(ctx, id)
}

// hasUpdatePermission 是否有更新的权限
// 当前仅创建者才能更新
func (uc *ProblemUsecase) hasUpdatePermission(ctx context.Context, id int) bool {
	p, _ := uc.GetProblem(ctx, id)
	userID, _ := auth.GetUserID(ctx)
	uc.log.Info(userID)
	return p.UserID == userID
}

// VerifyProblem 验证题目完整性
// 1. 题目描述 ProblemStatement
// 2. 存在测试点、样例
// 3. 存在 model_solution 标程，并可运行
// 4. 基于 model_solution 生成测试点的输出
func (uc *ProblemUsecase) VerifyProblem(ctx context.Context, id int) error {
	t, _ := uc.repo.ListProblemSampleTest(ctx, id)
	if len(t) == 0 {
		errors.New("不存在测试点")
	}
	statements, _ := uc.repo.ListProblemStatements(ctx, &v1.ListProblemStatementsRequest{Id: int32(id)})
	if len(statements) == 0 {
		errors.New("不存在题目描述")
	}
	solutions, _ := uc.repo.ListProblemFiles(ctx, &v1.ListProblemFilesRequest{Id: int32(id)})
	modelSolutionIndex := -1
	for k, v := range solutions {
		if v.Type == ProblemFileTypeModelSolution {
			modelSolutionIndex = k
		}
	}
	if modelSolutionIndex == -1 {
		errors.New("不存在标程")
	}
	if err := uc.RunProblemFile(ctx, id); err != nil {
		errors.New("标程运行出错")
	}
	return nil
}

func (uc *ProblemUsecase) UpdateProblemChecker(ctx context.Context, id int, checkerID int) error {
	return uc.repo.UpdateProblemChecker(ctx, id, checkerID)
}
