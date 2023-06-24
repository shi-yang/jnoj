package biz

import (
	"context"
	v1 "jnoj/api/admin/v1"
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
