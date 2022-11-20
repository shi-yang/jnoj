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
	CheckerID     int
	Status        int
	CreatedAt     time.Time
	UpdatedAt     time.Time

	Statements  []*ProblemStatement
	SampleTests []*SampleTest
}

const (
	ProblemStatusPrivate = iota + 1 // 私有
	ProblemStatusPublic             // 公开
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
	ProblemVerificationRepo
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
	p.Status = ProblemStatusPrivate
	return uc.repo.CreateProblem(ctx, p)
}

// UpdateProblem update a Problem
func (uc *ProblemUsecase) UpdateProblem(ctx context.Context, p *Problem) (*Problem, error) {
	return uc.repo.UpdateProblem(ctx, p)
}

// DeleteProblem delete a Problem
func (uc *ProblemUsecase) DeleteProblem(ctx context.Context, id int) error {
	return uc.repo.DeleteProblem(ctx, id)
}

// HasPermission 是否有权限
// t = view 查看权限，需要题目出于公开或者是创建人才能查看
// t = update 修改权限，近题目创建人可以看
func (uc *ProblemUsecase) HasPermission(ctx context.Context, id int, t string) bool {
	p, err := uc.GetProblem(ctx, id)
	if err != nil {
		return false
	}
	userID, _ := auth.GetUserID(ctx)
	if t == "view" {
		return p.UserID == userID || p.Status == ProblemStatusPublic
	}
	uc.log.Info("hasPermission:", p.UserID, userID)
	return p.UserID == userID
}

func (uc *ProblemUsecase) UpdateProblemChecker(ctx context.Context, id int, checkerID int) error {
	return uc.repo.UpdateProblemChecker(ctx, id, checkerID)
}
