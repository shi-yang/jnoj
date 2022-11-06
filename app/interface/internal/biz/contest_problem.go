package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"time"
)

// ContestProblem is a ContestProblem model.
type ContestProblem struct {
	ID            int
	Number        int // 题目次序、A、B、C、D
	ContestID     int
	ProblemID     int
	SubmitCount   int
	AcceptedCount int
	CreatedAt     time.Time
}

// ContestProblemRepo is a ContestProblem repo.
type ContestProblemRepo interface {
	ListContestProblems(context.Context, *v1.ListContestProblemsRequest) ([]*ContestProblem, int64)
	GetContestProblem(context.Context, int) (*ContestProblem, error)
	CreateContestProblem(context.Context, *ContestProblem) (*ContestProblem, error)
	UpdateContestProblem(context.Context, *ContestProblem) (*ContestProblem, error)
	DeleteContestProblem(context.Context, int) error
}

// ListContestProblems list ContestProblem
func (uc *ContestUsecase) ListContestProblems(ctx context.Context, req *v1.ListContestProblemsRequest) ([]*ContestProblem, int64) {
	return uc.repo.ListContestProblems(ctx, req)
}

// GetContestProblem get a ContestProblem
func (uc *ContestUsecase) GetContestProblem(ctx context.Context, id int) (*ContestProblem, error) {
	return uc.repo.GetContestProblem(ctx, id)
}

// CreateContestProblem creates a ContestProblem, and returns the new ContestProblem.
func (uc *ContestUsecase) CreateContestProblem(ctx context.Context, g *ContestProblem) (*ContestProblem, error) {
	return uc.repo.CreateContestProblem(ctx, g)
}

// UpdateContestProblem update a ContestProblem
func (uc *ContestUsecase) UpdateContestProblem(ctx context.Context, p *ContestProblem) (*ContestProblem, error) {
	return uc.repo.UpdateContestProblem(ctx, p)
}

// DeleteContestProblem delete a ContestProblem
func (uc *ContestUsecase) DeleteContestProblem(ctx context.Context, id int) error {
	return uc.repo.DeleteContestProblem(ctx, id)
}
