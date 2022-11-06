package biz

import (
	"context"
	"fmt"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"golang.org/x/crypto/bcrypt"
)

// User is a User model.
type User struct {
	ID        int
	Username  string
	Email     string
	Phone     string
	Password  string
	CreatedAt time.Time
}

// UserRepo is a User repo.
type UserRepo interface {
	CreateUser(context.Context, *User) (*User, error)
	Update(context.Context, *User) (*User, error)
	FindByID(context.Context, int) (*User, error)
	FindByUsername(context.Context, string) (*User, error)
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

func (uc *UserUsecase) Login(ctx context.Context, req *v1.LoginRequest) (string, error) {
	user, err := uc.repo.FindByUsername(ctx, req.Username)
	if err != nil {
		return "", err
	}
	if validatePassword(req.Password, user.Password) {
		return auth.GenerateToken(user.ID)
	}
	return "", fmt.Errorf("can not login")
}

// Register creates a User, and returns the new User.
func (uc *UserUsecase) Register(ctx context.Context, g *User) (*User, error) {
	uc.log.WithContext(ctx).Infof("CreateUser: %v", g.Username)
	return uc.repo.CreateUser(ctx, g)
}

func (uc *UserUsecase) GetUser(ctx context.Context, id int) (*User, error) {
	return uc.repo.FindByID(ctx, id)
}

// generatePasswordHash Generates password hash from password
func generatePasswordHash(password string) (string, error) {
	hash, err := bcrypt.GenerateFromPassword([]byte(password), bcrypt.DefaultCost)
	if err != nil {
		return "", err
	}
	return string(hash), nil
}

// validatePassword Verifies a password against a hash.
func validatePassword(password, hash string) bool {
	err := bcrypt.CompareHashAndPassword([]byte(hash), []byte(password))
	return err == nil
}
