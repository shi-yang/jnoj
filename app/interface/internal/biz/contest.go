package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"

	"github.com/go-kratos/kratos/v2/log"
)

// Contest is a Contest model.
type Contest struct {
	ID   int
	Name string
}

// ContestRepo is a Contest repo.
type ContestRepo interface {
	ListContests(context.Context, *v1.ListContestsRequest) ([]*Contest, int64)
	GetContest(context.Context, int) (*Contest, error)
	CreateContest(context.Context, *Contest) (*Contest, error)
	UpdateContest(context.Context, *Contest) (*Contest, error)
	DeleteContest(context.Context, int) error
	ContestProblemRepo
	ContestUserRepo
}

// ContestUsecase is a Contest usecase.
type ContestUsecase struct {
	repo ContestRepo
	log  *log.Helper
}

// NewContestUsecase new a Contest usecase.
func NewContestUsecase(repo ContestRepo, logger log.Logger) *ContestUsecase {
	return &ContestUsecase{repo: repo, log: log.NewHelper(logger)}
}

// ListContests list Contest
func (uc *ContestUsecase) ListContests(ctx context.Context, req *v1.ListContestsRequest) ([]*Contest, int64) {
	return uc.repo.ListContests(ctx, req)
}

// GetContest get a Contest
func (uc *ContestUsecase) GetContest(ctx context.Context, id int) (*Contest, error) {
	return uc.repo.GetContest(ctx, id)
}

// CreateContest creates a Contest, and returns the new Contest.
func (uc *ContestUsecase) CreateContest(ctx context.Context, g *Contest) (*Contest, error) {
	return uc.repo.CreateContest(ctx, g)
}

// UpdateContest update a Contest
func (uc *ContestUsecase) UpdateContest(ctx context.Context, p *Contest) (*Contest, error) {
	return uc.repo.UpdateContest(ctx, p)
}

// DeleteContest delete a Contest
func (uc *ContestUsecase) DeleteContest(ctx context.Context, id int) error {
	return uc.repo.DeleteContest(ctx, id)
}
