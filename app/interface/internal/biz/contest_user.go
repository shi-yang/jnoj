package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
)

// ContestUser is a ContestUser model.
type ContestUser struct {
	ID   int
	Name string
}

// ContestUserRepo is a ContestUser repo.
type ContestUserRepo interface {
	ListContestUsers(context.Context, *v1.ListContestUsersRequest) ([]*ContestUser, int64)
	GetContestUser(context.Context, int) (*ContestUser, error)
	CreateContestUser(context.Context, *ContestUser) (*ContestUser, error)
	UpdateContestUser(context.Context, *ContestUser) (*ContestUser, error)
	DeleteContestUser(context.Context, int) error
}

// ListContestUsers list ContestUser
func (uc *ContestUsecase) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*ContestUser, int64) {
	return uc.repo.ListContestUsers(ctx, req)
}

// GetContestUser get a ContestUser
func (uc *ContestUsecase) GetContestUser(ctx context.Context, id int) (*ContestUser, error) {
	return uc.repo.GetContestUser(ctx, id)
}

// CreateContestUser creates a ContestUser, and returns the new ContestUser.
func (uc *ContestUsecase) CreateContestUser(ctx context.Context, g *ContestUser) (*ContestUser, error) {
	return uc.repo.CreateContestUser(ctx, g)
}

// UpdateContestUser update a ContestUser
func (uc *ContestUsecase) UpdateContestUser(ctx context.Context, p *ContestUser) (*ContestUser, error) {
	return uc.repo.UpdateContestUser(ctx, p)
}

// DeleteContestUser delete a ContestUser
func (uc *ContestUsecase) DeleteContestUser(ctx context.Context, id int) error {
	return uc.repo.DeleteContestUser(ctx, id)
}
