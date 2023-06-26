package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"
)

// ContestUser is a ContestUser model.
type ContestUser struct {
	ID            int
	UserID        int
	ContestID     int
	Name          string // 自定义参赛名称
	Role          int
	UserNickname  string
	OldRating     int        // 上场比赛竞赛积分
	NewRating     int        // 本场比赛竞赛积分
	RatingChanged int        // 积分增减
	RatedAt       *time.Time // 竞赛积分计算时间
	VirtualStart  *time.Time // 虚拟竞赛开始时间
	VirtualEnd    *time.Time // 虚拟竞赛结束时间
}

// ContestUserRepo is a ContestUser repo.
type ContestUserRepo interface {
	ListContestUsers(context.Context, *v1.ListContestUsersRequest) ([]*ContestUser, int64)
	CreateContestUser(context.Context, *ContestUser) (*ContestUser, error)
	DeleteContestUser(context.Context, int) error
	GetContestUser(context.Context, int, int) *ContestUser
	UpdateContestUser(context.Context, *ContestUser) (*ContestUser, error)
	SaveContestRating(ctx context.Context, users []*ContestUser) error
	GetContestUserRating(ctx context.Context, uid int) int
}

// ListContestUsers list ContestUser
func (uc *ContestUsecase) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*ContestUser, int64) {
	return uc.repo.ListContestUsers(ctx, req)
}

// GetContestUser gets a ContestUser by id.
func (uc *ContestUsecase) GetContestUser(ctx context.Context, cid int, uid int) *ContestUser {
	return uc.repo.GetContestUser(ctx, cid, uid)
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
	if contest.Membership == ContestMembershipGroupUser {
		group, err := uc.groupRepo.GetGroup(ctx, contest.GroupId)
		if err != nil {
			return nil, v1.ErrorBadRequest("invalid group")
		}
		uid, role := auth.GetUserID(ctx)
		_, err = uc.groupRepo.GetGroupUser(ctx, group, uid)
		if uid != group.UserID && err != nil && !CheckAccess(role, ResourceContest) {
			return nil, v1.ErrorForbidden("only group user")
		}
	}
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
	return res, nil
}

// BatchCreateContestUsers 批量添加比赛用户
func (uc *ContestUsecase) BatchCreateContestUsers(ctx context.Context, req *v1.BatchCreateContestUsersRequest) (*v1.BatchCreateContestUsersResponse, error) {
	contest, err := uc.repo.GetContest(ctx, int(req.ContestId))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, ContestPermissionUpdate) {
		return nil, v1.ErrorForbidden("forbidden")
	}
	res := new(v1.BatchCreateContestUsersResponse)
	for _, v := range req.Users {
		u, err := uc.userRepo.GetUser(ctx, &User{Username: v.Username})
		if err != nil {
			res.Failed = append(res.Failed, &v1.BatchCreateContestUsersResponse_ContestUser{
				Username: v.Username,
				Name:     v.Name,
				Reason:   "user not found",
			})
			continue
		}
		if contestUser := uc.repo.GetContestUser(ctx, int(req.ContestId), u.ID); contestUser != nil {
			res.Failed = append(res.Failed, &v1.BatchCreateContestUsersResponse_ContestUser{
				Username: v.Username,
				Name:     v.Name,
				Reason:   "already registered",
			})
			continue
		}
		c := &ContestUser{
			UserID:    u.ID,
			ContestID: int(req.ContestId),
			Name:      v.Name,
			Role:      int(req.Role),
		}
		_, err = uc.repo.CreateContestUser(ctx, c)
		if err != nil {
			res.Failed = append(res.Failed, &v1.BatchCreateContestUsersResponse_ContestUser{
				Username: v.Username,
				Name:     v.Name,
				Reason:   err.Error(),
			})
		} else {
			res.Success = append(res.Success, &v1.BatchCreateContestUsersResponse_ContestUser{
				Username: v.Username,
				Name:     v.Name,
			})
		}
	}
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
