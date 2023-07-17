package biz

import (
	"context"
	v1 "jnoj/api/admin/v1"
	"time"
)

// UserBadge is a UserBadge model.
type UserBadge struct {
	ID          int
	Name        string
	Type        int
	Image       []byte
	ImageGif    []byte
	ImageURL    string
	ImageGifURL string
}

type UserUserBadge struct {
	ID        int
	UserID    int
	BadgeID   int
	CreatedAt time.Time
	UserBadge *UserBadge
}

const (
	UserBadgeTypeActivity = iota
	UserBadgeTypeLevel
	UserBadgeTypeContest
)

// UserBadgeRepo is a UserBadge repo.
type UserBadgeRepo interface {
	ListUserBadges(context.Context, *v1.ListUserBadgesRequest) ([]*UserBadge, int64)
	GetUserBadge(context.Context, int) (*UserBadge, error)
	CreateUserBadge(context.Context, *UserBadge) (*UserBadge, error)
	UpdateUserBadge(context.Context, *UserBadge) (*UserBadge, error)
	DeleteUserBadge(context.Context, int) error

	ListUserUserBadges(context.Context, *v1.ListUserUserBadgesRequest) ([]*UserUserBadge, int)
	CreateUserUserBadge(context.Context, *UserUserBadge) (*UserUserBadge, error)
	DeleteUserUserBadge(ctx context.Context, uid int, id int) error
}

// ListUserBadges list UserBadge
func (uc *UserUsecase) ListUserBadges(ctx context.Context, req *v1.ListUserBadgesRequest) ([]*UserBadge, int64) {
	return uc.repo.ListUserBadges(ctx, req)
}

// GetUserBadge get a UserBadge
func (uc *UserUsecase) GetUserBadge(ctx context.Context, id int) (*UserBadge, error) {
	return uc.repo.GetUserBadge(ctx, id)
}

// CreateUserBadge creates a UserBadge, and returns the new UserBadge.
func (uc *UserUsecase) CreateUserBadge(ctx context.Context, g *UserBadge) (*UserBadge, error) {
	return uc.repo.CreateUserBadge(ctx, g)
}

// UpdateUserBadge update a UserBadge
func (uc *UserUsecase) UpdateUserBadge(ctx context.Context, p *UserBadge) (*UserBadge, error) {
	return uc.repo.UpdateUserBadge(ctx, p)
}

// DeleteUserBadge delete a UserBadge
func (uc *UserUsecase) DeleteUserBadge(ctx context.Context, id int) error {
	return uc.repo.DeleteUserBadge(ctx, id)
}

// ListUserUserBadges 获取用户的勋章列表
func (uc *UserUsecase) ListUserUserBadges(ctx context.Context, request *v1.ListUserUserBadgesRequest) ([]*UserUserBadge, int) {
	return uc.repo.ListUserUserBadges(ctx, request)
}

// CreateUserUserBadge 创建用户的勋章
func (uc *UserUsecase) CreateUserUserBadge(ctx context.Context, badge *UserUserBadge) (*UserUserBadge, error) {
	return uc.repo.CreateUserUserBadge(ctx, badge)
}

// DeleteUserUserBadge 删除用户的勋章
func (uc *UserUsecase) DeleteUserUserBadge(ctx context.Context, uid int, id int) error {
	return uc.repo.DeleteUserUserBadge(ctx, uid, id)
}
