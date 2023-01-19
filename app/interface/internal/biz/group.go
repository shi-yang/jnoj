package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Group is a Group model.
type Group struct {
	ID          int
	Name        string
	Description string
	UserID      int
	MemberCount int
	CreatedAt   time.Time
}

// GroupUser .
type GroupUser struct {
	ID        int
	GroupID   int
	UserID    int
	CreatedAt time.Time
}

// GroupRepo is a Group repo.
type GroupRepo interface {
	ListGroups(context.Context, *v1.ListGroupsRequest) ([]*Group, int64)
	GetGroup(context.Context, int) (*Group, error)
	CreateGroup(context.Context, *Group) (*Group, error)
	UpdateGroup(context.Context, *Group) (*Group, error)
	DeleteGroup(context.Context, int) error
	ListGroupUsers(context.Context, *v1.ListGroupUsersRequest) ([]*GroupUser, int64)
	DeleteGroupUser(ctx context.Context, groupID int, userID int) error
	CreateGroupUser(context.Context, *GroupUser) (*GroupUser, error)
}

// GroupUsecase is a Group usecase.
type GroupUsecase struct {
	repo GroupRepo
	log  *log.Helper
}

// NewGroupUsecase new a Group usecase.
func NewGroupUsecase(repo GroupRepo, logger log.Logger) *GroupUsecase {
	return &GroupUsecase{repo: repo, log: log.NewHelper(logger)}
}

// ListGroups list Group
func (uc *GroupUsecase) ListGroups(ctx context.Context, req *v1.ListGroupsRequest) ([]*Group, int64) {
	return uc.repo.ListGroups(ctx, req)
}

// GetGroup get a Group
func (uc *GroupUsecase) GetGroup(ctx context.Context, id int) (*Group, error) {
	return uc.repo.GetGroup(ctx, id)
}

// CreateGroup creates a Group, and returns the new Group.
func (uc *GroupUsecase) CreateGroup(ctx context.Context, g *Group) (*Group, error) {
	return uc.repo.CreateGroup(ctx, g)
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

// CreateGroupUser .
func (uc *GroupUsecase) CreateGroupUser(ctx context.Context, g *GroupUser) (*GroupUser, error) {
	return uc.repo.CreateGroupUser(ctx, g)
}

// DeleteGroupUser .
func (uc *GroupUsecase) DeleteGroupUser(ctx context.Context, gid, uid int) error {
	return uc.repo.DeleteGroupUser(ctx, gid, uid)
}
