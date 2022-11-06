package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Problem is a Problem model.
type Problem struct {
	ID         int
	Name       string
	UserID     int
	Statements []ProblemStatement
	CreatedAt  time.Time
	UpdatedAt  time.Time
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
	repo ProblemRepo
	log  *log.Helper
}

// NewProblemUsecase new a Problem usecase.
func NewProblemUsecase(repo ProblemRepo, logger log.Logger) *ProblemUsecase {
	return &ProblemUsecase{repo: repo, log: log.NewHelper(logger)}
}

// ListProblems list Problem
func (uc *ProblemUsecase) ListProblems(ctx context.Context, req *v1.ListProblemsRequest) ([]*Problem, int64) {
	return uc.repo.ListProblems(ctx, req)
}

// GetProblem get a Problem
func (uc *ProblemUsecase) GetProblem(ctx context.Context, id int) (*Problem, error) {
	return uc.repo.GetProblem(ctx, id)
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
	return p.UserID == userID
}
