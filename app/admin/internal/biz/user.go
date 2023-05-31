package biz

import (
	"context"
	v1 "jnoj/api/admin/v1"
	"jnoj/pkg/password"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// User is a User model.
type User struct {
	ID        int
	Username  string
	Nickname  string
	Email     string
	Phone     string
	Password  string
	Role      int
	CreatedAt time.Time
}

const (
	UserRoleRegular    = iota // 常规用户
	UserRoleVIP               // vip用户
	UserRoleOfficial          // 官方用户
	UserRoleAdmin             // 管理员
	UserRoleSuperAdmin        // 超级管理员
)

// UserRepo is a User repo.
type UserRepo interface {
	GetUser(context.Context, *User) (*User, error)
	CreateUser(context.Context, *User) (*User, error)
	UpdateUser(context.Context, *User) (*User, error)
	ListUsers(context.Context, *v1.ListUsersRequest) ([]*User, int64)
}

// UserUsecase is a User usecase.
type UserUsecase struct {
	repo UserRepo
	log  *log.Helper
}

// NewUserUsecase new a User usecase.
func NewUserUsecase(repo UserRepo, logger log.Logger) *UserUsecase {
	return &UserUsecase{repo: repo, log: log.NewHelper(logger)}
}

func (uc *UserUsecase) GetUser(ctx context.Context, u *User) (*User, error) {
	return uc.repo.GetUser(ctx, u)
}

// CreateUser creates a User, and returns the new User.
func (uc *UserUsecase) CreateUser(ctx context.Context, u *User) (*User, error) {
	uc.log.WithContext(ctx).Infof("CreateUser: %v", u.Nickname)
	u.Password, _ = password.GeneratePasswordHash(u.Password)
	return uc.repo.CreateUser(ctx, u)
}

func (uc *UserUsecase) UpdateUser(ctx context.Context, u *User) (*User, error) {
	if u.Password != "" {
		u.Password, _ = password.GeneratePasswordHash(u.Password)
	}
	return uc.repo.UpdateUser(ctx, u)
}

// ListUsers
func (uc *UserUsecase) ListUsers(ctx context.Context, req *v1.ListUsersRequest) ([]*User, int64) {
	return uc.repo.ListUsers(ctx, req)
}
