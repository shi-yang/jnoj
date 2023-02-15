package biz

import (
	"context"
	"errors"
	"fmt"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/conf"
	"jnoj/internal/middleware/auth"
	"math/rand"
	"net/smtp"
	"time"

	"github.com/jordan-wright/email"

	"github.com/go-kratos/kratos/v2/log"
	"golang.org/x/crypto/bcrypt"
)

// User is a User model.
type User struct {
	ID        int
	Username  string
	Nickname  string
	Email     string
	Phone     string
	Password  string
	CreatedAt time.Time
}

// UserRepo is a User repo.
type UserRepo interface {
	CreateUser(context.Context, *User) (*User, error)
	GetUser(context.Context, *User) (*User, error)
	Update(context.Context, *User) (*User, error)
	FindByID(context.Context, int) (*User, error)
	GetUserProfileCalendar(context.Context, *v1.GetUserProfileCalendarRequest) (*v1.GetUserProfileCalendarResponse, error)

	GetCaptcha(ctx context.Context, key string) (string, error)
	SaveCaptcha(ctx context.Context, key string, value string) error
}

// UserUsecase is a User usecase.
type UserUsecase struct {
	repo UserRepo
	log  *log.Helper
	c    *conf.Service
}

// NewUserUsecase new a User usecase.
func NewUserUsecase(repo UserRepo, c *conf.Service, logger log.Logger) *UserUsecase {
	return &UserUsecase{
		repo: repo,
		c:    c,
		log:  log.NewHelper(logger),
	}
}

func (uc *UserUsecase) Login(ctx context.Context, req *v1.LoginRequest) (string, error) {
	user, err := uc.repo.GetUser(ctx, &User{Username: req.Username})
	if err != nil {
		return "", v1.ErrorUserExist("username not exist.")
	}
	if validatePassword(req.Password, user.Password) {
		return auth.GenerateToken(user.ID)
	}
	return "", fmt.Errorf("login faild")
}

// Register creates a User, and returns the new User.
func (uc *UserUsecase) Register(ctx context.Context, u *User, captcha string) (int, string, error) {
	uc.log.WithContext(ctx).Infof("CreateUser: %v", u.Username)

	if _, err := uc.repo.GetUser(ctx, &User{Username: u.Username}); err == nil {
		return 0, "", fmt.Errorf("username exist")
	}
	if u.Email != "" {
		if _, err := uc.repo.GetUser(ctx, &User{Email: u.Email}); err == nil {
			return 0, "", fmt.Errorf("email exist")
		}
	}
	if u.Phone != "" {
		if _, err := uc.repo.GetUser(ctx, &User{Phone: u.Phone}); err == nil {
			return 0, "", fmt.Errorf("phone exist")
		}
	}
	if err := uc.VerifyCaptcha(ctx, u.Email, u.Phone, captcha); err != nil {
		return 0, "", v1.ErrorCaptchaError(err.Error())
	}
	u.Password, _ = generatePasswordHash(u.Password)
	user, err := uc.repo.CreateUser(ctx, u)
	if err != nil {
		return 0, "", fmt.Errorf(err.Error())
	}
	token, err := auth.GenerateToken(user.ID)
	return user.ID, token, err
}

// GetCaptcha 获取验证码
func (uc *UserUsecase) GetCaptcha(ctx context.Context, email, phone string) error {
	var key string
	rnd := rand.New(rand.NewSource(time.Now().Unix()))
	code := fmt.Sprintf("%06v", rnd.Int31n(1000000))
	uc.log.Info("captcha:", code)
	if email != "" {
		key = fmt.Sprintf("captcha:email:%s", email)
		uc.sendEmailCaptcha(ctx, email, code)
	} else {
		key = fmt.Sprintf("captcha:phone:%s", phone)
		uc.sendPhoneCaptcha(ctx, phone, code)
	}
	uc.repo.SaveCaptcha(ctx, key, code)
	return nil
}

func (uc *UserUsecase) sendEmailCaptcha(ctx context.Context, emailAddress, code string) error {
	em := email.NewEmail()
	em.From = uc.c.Smtp.Username
	em.To = []string{emailAddress}
	em.Subject = "Captcha Code"
	em.Text = []byte(fmt.Sprintf("Your captcha: %s", code))
	return em.Send(uc.c.Smtp.Identity, smtp.PlainAuth(uc.c.Smtp.Identity, uc.c.Smtp.Username, uc.c.Smtp.Password, uc.c.Smtp.Host))
}

func (uc *UserUsecase) sendPhoneCaptcha(ctx context.Context, phone, code string) error {
	return errors.New("not support")
}

// VerifyCaptcha 验证验证码
func (uc *UserUsecase) VerifyCaptcha(ctx context.Context, email, phone, captcha string) error {
	var key string
	if email != "" {
		key = fmt.Sprintf("captcha:email:%s", email)
	} else {
		key = fmt.Sprintf("captcha:phone:%s", phone)
	}
	uc.log.Info(key)
	res, err := uc.repo.GetCaptcha(ctx, key)
	if err != nil {
		return err
	}
	if res != captcha {
		return errors.New("wrong captcha")
	}
	return nil
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

func (uc *UserUsecase) GetUserProfileCalendar(ctx context.Context, req *v1.GetUserProfileCalendarRequest) (*v1.GetUserProfileCalendarResponse, error) {
	return uc.repo.GetUserProfileCalendar(ctx, req)
}
