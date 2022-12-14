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
	SampleTests []*Test
}

const (
	ProblemStatusPrivate = iota + 1 // 私有
	ProblemStatusPublic             // 公开
)

// 题目权限
type ProblemPermissionType int32

const (
	ProblemPermissionView   ProblemPermissionType = 0 // 查看权限
	ProblemPermissionUpdate ProblemPermissionType = 1 // 修改权限
)

// HasPermission 是否有权限
// 查看权限，需要题目出于公开或者是创建人才能查看
// 修改权限，仅题目创建人可以看
func (p *Problem) HasPermission(ctx context.Context, t ProblemPermissionType) bool {
	userID, _ := auth.GetUserID(ctx)
	if t == ProblemPermissionView {
		return p.UserID == userID || p.Status == ProblemStatusPublic
	}
	return p.UserID == userID
}

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
	tests, _ := uc.repo.ListProblemTestContent(ctx, id, true)
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
		p.SampleTests = append(p.SampleTests, &Test{
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

func (uc *ProblemUsecase) UpdateProblemChecker(ctx context.Context, id int, checkerID int) error {
	return uc.repo.UpdateProblemChecker(ctx, id, checkerID)
}
