package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Problem is a Problem model.
type Problem struct {
	ID            int
	Name          string
	UserID        int
	TimeLimit     int64
	MemoryLimit   int64
	AcceptedCount int
	SubmitCount   int
	CreatedAt     time.Time
	UpdatedAt     time.Time

	Statements  []*ProblemStatement
	SampleTests []*SampleTest
}

// ProblemRepo is a Problem repo.
type ProblemRepo interface {
	ListProblems(context.Context, *v1.ListProblemsRequest) ([]*Problem, int64)
	GetProblem(context.Context, int) (*Problem, error)
	CreateProblem(context.Context, *Problem) (*Problem, error)
	UpdateProblem(context.Context, *Problem) (*Problem, error)
	DeleteProblem(context.Context, int) error
	ProblemTestRepo
	ProblemCheckerRepo
	ProblemStatementRepo
	ProblemSolutionRepo
	ProblemCheckerRepo
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
		Id: int32(p.ID),
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
