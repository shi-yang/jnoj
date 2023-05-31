package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Group is a Group model.
type Group struct {
	ID             int
	Name           string
	Description    string
	Privacy        int    // 隐私设置
	Membership     int    // 加入资格
	InvitationCode string // 邀请码
	MemberCount    int
	Role           int // 当前登录用户的角色
	UserID         int
	UserNickname   string
	CreatedAt      time.Time
}

// GroupUser .
type GroupUser struct {
	ID        int
	GroupID   int
	UserID    int
	Role      int
	Nickname  string
	CreatedAt time.Time
}

const (
	GroupPrivacyPrivate = iota
	GroupPrivatePublic
)

const (
	GroupMembershipAllowAnyone = iota
	GroupMembershipInvitationCode
)

const (
	GroupUserRoleAdmin = iota
	GroupUserRoleManager
	GroupUserRoleMember
	GroupUserRoleGuest
)

// GroupRepo is a Group repo.
type GroupRepo interface {
	ListGroups(context.Context, *v1.ListGroupsRequest) ([]*Group, int64)
	GetGroup(context.Context, int) (*Group, error)
	CreateGroup(context.Context, *Group) (*Group, error)
	UpdateGroup(context.Context, *Group) (*Group, error)
	DeleteGroup(context.Context, int) error
	ListGroupUsers(context.Context, *v1.ListGroupUsersRequest) ([]*GroupUser, int64)
	GetGroupUser(ctx context.Context, gid int, uid int) (*GroupUser, error)
	DeleteGroupUser(ctx context.Context, groupID int, userID int) error
	CreateGroupUser(context.Context, *GroupUser) (*GroupUser, error)
	UpdateGroupUser(context.Context, *GroupUser) (*GroupUser, error)
}

// GroupUsecase is a Group usecase.
type GroupUsecase struct {
	repo     GroupRepo
	userRepo UserRepo
	log      *log.Helper
}

// NewGroupUsecase new a Group usecase.
func NewGroupUsecase(repo GroupRepo, userRepo UserRepo, logger log.Logger) *GroupUsecase {
	return &GroupUsecase{repo: repo, userRepo: userRepo, log: log.NewHelper(logger)}
}

// ListGroups list Group
func (uc *GroupUsecase) ListGroups(ctx context.Context, req *v1.ListGroupsRequest) ([]*Group, int64) {
	return uc.repo.ListGroups(ctx, req)
}

// GetGroup get a Group
func (uc *GroupUsecase) GetGroup(ctx context.Context, id int) (*Group, error) {
	g, err := uc.repo.GetGroup(ctx, id)
	if err != nil {
		return nil, err
	}
	// 登录用户角色
	role := uc.GetGroupRole(ctx, g)
	// 邀请码仅对管理员可见
	if role == GroupUserRoleMember || role == GroupUserRoleGuest {
		g.InvitationCode = ""
	}
	return g, nil
}

// CreateGroup creates a Group, and returns the new Group.
func (uc *GroupUsecase) CreateGroup(ctx context.Context, g *Group) (*Group, error) {
	res, err := uc.repo.CreateGroup(ctx, g)
	if err != nil {
		return nil, err
	}
	// 创建者添加为 Admin
	uc.repo.CreateGroupUser(ctx, &GroupUser{
		UserID:  g.UserID,
		GroupID: res.ID,
		Role:    GroupUserRoleAdmin,
	})
	return res, nil
}

// UpdateGroup update a Group
func (uc *GroupUsecase) UpdateGroup(ctx context.Context, p *Group) (*Group, error) {
	return uc.repo.UpdateGroup(ctx, p)
}

// DeleteGroup delete a Group
func (uc *GroupUsecase) DeleteGroup(ctx context.Context, id int) error {
	return uc.repo.DeleteGroup(ctx, id)
}

// ListGroupUsers .
func (uc *GroupUsecase) ListGroupUsers(ctx context.Context, req *v1.ListGroupUsersRequest) ([]*GroupUser, int64) {
	return uc.repo.ListGroupUsers(ctx, req)
}

// GetGroupUser .
func (uc *GroupUsecase) GetGroupUser(ctx context.Context, gid, uid int) (*GroupUser, error) {
	return uc.repo.GetGroupUser(ctx, gid, uid)
}

// CreateGroupUser .
func (uc *GroupUsecase) CreateGroupUser(ctx context.Context, req *v1.CreateGroupUserRequest) (*GroupUser, error) {
	user, err := uc.userRepo.GetUser(ctx, &User{Username: req.Username})
	if err != nil {
		return nil, v1.ErrorBadRequest("user not found")
	}
	group, err := uc.GetGroup(ctx, int(req.Gid))
	if err != nil {
		return nil, v1.ErrorBadRequest("group not found")
	}
	// 邀请码的方式，需要验证邀请码
	role := uc.GetGroupRole(ctx, group)
	if (role != GroupUserRoleAdmin && role != GroupUserRoleManager) &&
		group.Membership == GroupMembershipInvitationCode &&
		group.InvitationCode != req.InvitationCode {
		return nil, v1.ErrorBadRequest("invalid invitation code")
	}
	return uc.repo.CreateGroupUser(ctx, &GroupUser{
		UserID:  user.ID,
		GroupID: int(req.Gid),
		Role:    GroupUserRoleMember,
	})
}

func (uc *GroupUsecase) UpdateGroupUser(ctx context.Context, g *GroupUser) (*GroupUser, error) {
	return uc.repo.UpdateGroupUser(ctx, g)
}

// DeleteGroupUser .
func (uc *GroupUsecase) DeleteGroupUser(ctx context.Context, gid, uid int) error {
	return uc.repo.DeleteGroupUser(ctx, gid, uid)
}

// GetGroupRole 获取登录用户角色
func (uc *GroupUsecase) GetGroupRole(ctx context.Context, group *Group) int {
	group.Role = GroupUserRoleGuest
	uid, _ := auth.GetUserID(ctx)
	if uid != 0 {
		if group.UserID == uid {
			group.Role = GroupUserRoleAdmin
		} else {
			gu, err := uc.repo.GetGroupUser(ctx, group.ID, uid)
			if err == nil {
				group.Role = gu.Role
			}
		}
	}
	return group.Role
}
