package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
)

// ContestUser is a ContestUser model.
type ContestUser struct {
	ID        int
	UserID    int
	ContestID int
	Nickname  string
}

// ContestUserRepo is a ContestUser repo.
type ContestUserRepo interface {
	ListContestUsers(context.Context, *v1.ListContestUsersRequest) ([]*ContestUser, int64)
	CreateContestUser(context.Context, *ContestUser) (*ContestUser, error)
	DeleteContestUser(context.Context, int) error
	ExistContestUser(context.Context, int, int) bool
}

// ListContestUsers list ContestUser
func (uc *ContestUsecase) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*ContestUser, int64) {
	return uc.repo.ListContestUsers(ctx, req)
}

// CreateContestUser creates a ContestUser, and returns the new ContestUser.
func (uc *ContestUsecase) CreateContestUser(ctx context.Context, c *ContestUser) (*ContestUser, error) {
	if ok := uc.repo.ExistContestUser(ctx, c.ContestID, c.UserID); ok {
		return nil, v1.ErrorContestAlreadyRegistered("already registered")
	}
	res, err := uc.repo.CreateContestUser(ctx, c)
	if err != nil {
		return nil, err
	}
	// 比赛人数 + 1
	_ = uc.repo.AddContestParticipantCount(ctx, c.ContestID, 1)
	return res, nil
}

// DeleteContestUser delete a ContestUser
func (uc *ContestUsecase) DeleteContestUser(ctx context.Context, id int) error {
	return uc.repo.DeleteContestUser(ctx, id)
}
