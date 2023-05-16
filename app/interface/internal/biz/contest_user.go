package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"time"
)

// ContestUser is a ContestUser model.
type ContestUser struct {
	ID           int
	UserID       int
	ContestID    int
	Name         string // 自定义参赛名称
	Role         int
	UserNickname string
	VirtualStart *time.Time // 虚拟竞赛开始时间
}

// ContestUserRepo is a ContestUser repo.
type ContestUserRepo interface {
	ListContestUsers(context.Context, *v1.ListContestUsersRequest) ([]*ContestUser, int64)
	CreateContestUser(context.Context, *ContestUser) (*ContestUser, error)
	DeleteContestUser(context.Context, int) error
	GetContestUser(context.Context, int, int) *ContestUser
	UpdateContestUser(context.Context, *ContestUser) (*ContestUser, error)
}

// ListContestUsers list ContestUser
func (uc *ContestUsecase) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*ContestUser, int64) {
	return uc.repo.ListContestUsers(ctx, req)
}

// CreateContestUser creates a ContestUser, and returns the new ContestUser.
func (uc *ContestUsecase) CreateContestUser(ctx context.Context, c *ContestUser, invitationCode string) (*ContestUser, error) {
	contest, err := uc.repo.GetContest(ctx, c.ContestID)
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	// 需要验证码
	if contest.Membership == ContestMembershipInvitationCode && invitationCode != contest.InvitationCode {
		return nil, v1.ErrorBadRequest("invalid invitation code")
	}
	// 需要小组成员
	// TODO
	// if contest.Membership == ContestMembershipGroupUser {
	// }
	if contestUser := uc.repo.GetContestUser(ctx, c.ContestID, c.UserID); contestUser != nil {
		return nil, v1.ErrorContestAlreadyRegistered("already registered")
	}

	c.Role = ContestRoleOfficialPlayer
	// 在比赛结束后参赛，将以虚拟选手的身份进行参加
	if contest.GetRunningStatus() == ContestRunningStatusFinished {
		c.Role = ContestRoleVirtualPlayer
		now := time.Now()
		c.VirtualStart = &now
	}

	res, err := uc.repo.CreateContestUser(ctx, c)
	if err != nil {
		return nil, err
	}
	// 比赛人数 + 1
	_ = uc.repo.AddContestParticipantCount(ctx, c.ContestID, 1)
	return res, nil
}

// UpdateContestUser .
func (uc *ContestUsecase) UpdateContestUser(ctx context.Context, c *ContestUser) (*ContestUser, error) {
	return uc.repo.UpdateContestUser(ctx, c)
}

// DeleteContestUser delete a ContestUser
func (uc *ContestUsecase) DeleteContestUser(ctx context.Context, id int) error {
	return uc.repo.DeleteContestUser(ctx, id)
}
