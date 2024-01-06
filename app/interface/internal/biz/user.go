package biz

import (
	"context"
	"crypto/tls"
	"encoding/json"
	"errors"
	"fmt"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/conf"
	"jnoj/internal/middleware/auth"
	"jnoj/pkg/password"
	"math/rand"
	"net/smtp"
	"strconv"
	"strings"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/jordan-wright/email"
	"github.com/wenlng/go-captcha/captcha"
)

// User is a User model.
type User struct {
	ID          int
	Username    string
	Nickname    string
	Avatar      string
	Email       string
	Phone       string
	Password    string
	Role        int
	Status      int
	CreatedAt   time.Time
	UserProfile *UserProfile
}

type UserProfile struct {
	UserID    int
	Realname  string
	Location  string
	Bio       string
	Gender    int
	School    string
	Birthday  *time.Time
	Company   string
	Job       string
	CreatedAt time.Time
	UpdatedAt time.Time
}

// 用户角色
const (
	UserRoleRegular    = iota // 常规用户
	UserRoleVIP               // vip用户
	UserRoleOfficial          // 官方用户
	UserRoleAdmin             // 管理员
	UserRoleSuperAdmin        // 超级管理员
)

// 用户状态
const (
	UserStatusEnable  = iota // 可用
	UserStatusDisable        // 禁用
)

// UserRepo is a User repo.
type UserRepo interface {
	ListUsers(context.Context, *v1.ListUsersRequest) []*User
	CreateUser(context.Context, *User) (*User, error)
	GetUser(context.Context, *User) (*User, error)
	UpdateUser(context.Context, *User) (*User, error)
	GetUserProfile(context.Context, int) (*UserProfile, error)
	UpdateUserProfile(context.Context, *UserProfile) (*UserProfile, error)
	UpdateUserAvatar(context.Context, *User, *v1.UpdateUserAvatarRequest) (*User, error)
	GetUserProfileCalendar(context.Context, *v1.GetUserProfileCalendarRequest) (*v1.GetUserProfileCalendarResponse, error)
	GetUserProfileProblemsetProblemSolved(ctx context.Context, uid int, page int, pageSize int) (*v1.GetUserProfileProblemSolvedResponse, error)
	GetUserProfileContestProblemSolved(ctx context.Context, uid int, page int, pageSize int) (*v1.GetUserProfileProblemSolvedResponse, error)
	GetUserProfileGroupProblemSolved(ctx context.Context, uid int, page int, pageSize int) (*v1.GetUserProfileProblemSolvedResponse, error)
	GetUserProfileCount(ctx context.Context, uid int) (*v1.GetUserProfileCountResponse, error)
	ListUserProfileUserBadges(ctx context.Context, uid int) (*v1.ListUserProfileUserBadgesResponse, error)

	AddCache(ctx context.Context, key string, value string, ttl time.Duration) error
	DelCache(ctx context.Context, key string) error
	GetCache(ctx context.Context, key string) (string, error)
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
	uc.log.WithContext(ctx).Infof("Login: %v", req.Username)
	if v, err := uc.repo.GetCache(ctx, req.CaptchaKey); err != nil || v != "ok" {
		return "", v1.ErrorCaptchaError(err.Error())
	}
	uc.repo.DelCache(ctx, req.CaptchaKey)
	user, err := uc.repo.GetUser(ctx, &User{Username: req.Username})
	if err != nil {
		return "", v1.ErrorInvalidUsernameOrPassword(err.Error())
	}
	if !password.ValidatePassword(req.Password, user.Password) {
		return "", v1.ErrorInvalidUsernameOrPassword("")
	}
	if user.Status == UserStatusDisable {
		return "", v1.ErrorUserDisable("")
	}
	return auth.GenerateToken(user.ID, user.Role)
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
	if err := uc.verifyOTP(ctx, u.Email, u.Phone, captcha); err != nil {
		return 0, "", v1.ErrorCaptchaError(err.Error())
	}
	u.Password, _ = password.GeneratePasswordHash(u.Password)
	user, err := uc.repo.CreateUser(ctx, u)
	if err != nil {
		return 0, "", fmt.Errorf(err.Error())
	}
	token, err := auth.GenerateToken(user.ID, user.Role)
	return user.ID, token, err
}

// GetCaptcha 获取验证码
func (uc *UserUsecase) GetCaptcha(ctx context.Context, username, email, phone string) (res *v1.GetCaptchaResponse, err error) {
	var key string
	var code string
	if username != "" {
		capt := captcha.GetCaptcha()
		dots, b64, tb64, key1, err := capt.Generate()
		if err != nil {
			return nil, err
		}
		key = key1
		res = &v1.GetCaptchaResponse{
			CaptchaKey:  key,
			ImageBase64: b64,
			ThumbBase64: tb64,
		}
		value, _ := json.Marshal(dots)
		code = string(value)
	} else if email != "" {
		code = uc.generateOTP()
		uc.log.Info("captcha:", code)
		key = fmt.Sprintf("captcha:email:%s", email)
		if err = uc.sendEmailCaptcha(ctx, email, code); err != nil {
			return nil, err
		}
	} else {
		code = uc.generateOTP()
		uc.log.Info("captcha:", code)
		key = fmt.Sprintf("captcha:phone:%s", phone)
		if err = uc.sendPhoneCaptcha(ctx, phone, code); err != nil {
			return nil, err
		}
	}
	if err := uc.repo.AddCache(ctx, key, code, 5*time.Minute); err != nil {
		return nil, err
	}
	return res, nil
}

func (uc *UserUsecase) generateOTP() string {
	rnd := rand.New(rand.NewSource(time.Now().Unix()))
	return fmt.Sprintf("%06v", rnd.Int31n(1000000))
}

func (uc *UserUsecase) sendEmailCaptcha(ctx context.Context, emailAddress, code string) error {
	em := email.NewEmail()
	em.From = fmt.Sprintf("No reply <%s>", uc.c.Smtp.Username)
	em.To = []string{emailAddress}
	em.Subject = "Captcha Code"
	em.Text = []byte(fmt.Sprintf("Your captcha: %s", code))
	if uc.c.Smtp.Ssl {
		tlsConfig := &tls.Config{
			InsecureSkipVerify: true,
			ServerName:         uc.c.Smtp.Host,
		}
		return em.SendWithTLS(
			uc.c.Smtp.Address,
			smtp.PlainAuth("", uc.c.Smtp.Username, uc.c.Smtp.Password, uc.c.Smtp.Host), tlsConfig,
		)
	}
	return em.Send(
		uc.c.Smtp.Address,
		smtp.PlainAuth("", uc.c.Smtp.Username, uc.c.Smtp.Password, uc.c.Smtp.Host),
	)
}

func (uc *UserUsecase) sendPhoneCaptcha(ctx context.Context, phone, code string) error {
	return errors.New("not support")
}

// VerifyCaptcha 验证图形验证码
func (uc *UserUsecase) VerifyCaptcha(ctx context.Context, key, dots string) (bool, error) {
	captchaData, err := uc.repo.GetCache(ctx, key)
	if err != nil {
		return false, err
	}
	src := strings.Split(dots, ",")
	var dct map[int]captcha.CharDot
	if err := json.Unmarshal([]byte(captchaData), &dct); err != nil {
		return false, err
	}
	chkRet := false
	if (len(dct) * 2) == len(src) {
		for i, dot := range dct {
			j := i * 2
			k := i*2 + 1
			sx, _ := strconv.ParseFloat(fmt.Sprintf("%v", src[j]), 64)
			sy, _ := strconv.ParseFloat(fmt.Sprintf("%v", src[k]), 64)
			// 检测点位置
			// chkRet = captcha.CheckPointDist(int64(sx), int64(sy), int64(dot.Dx), int64(dot.Dy), int64(dot.Width), int64(dot.Height))
			// 校验点的位置,在原有的区域上添加额外边距进行扩张计算区域,不推荐设置过大的padding
			// 例如：文本的宽和高为30，校验范围x为10-40，y为15-45，此时扩充5像素后校验范围宽和高为40，则校验范围x为5-45，位置y为10-50
			chkRet = captcha.CheckPointDistWithPadding(int64(sx), int64(sy), int64(dot.Dx), int64(dot.Dy), int64(dot.Width), int64(dot.Height), 5)
			if !chkRet {
				break
			}
		}
	}
	if chkRet {
		if err := uc.repo.AddCache(ctx, key, "ok", 5*time.Minute); err != nil {
			return false, err
		}
	}
	return chkRet, nil
}

// verifyOTP 验证OTP验证码
func (uc *UserUsecase) verifyOTP(ctx context.Context, email, phone, captcha string) error {
	var key string
	if email != "" {
		key = fmt.Sprintf("captcha:email:%s", email)
	} else {
		key = fmt.Sprintf("captcha:phone:%s", phone)
	}
	uc.log.Info(key)
	res, err := uc.repo.GetCache(ctx, key)
	if err != nil {
		return err
	}
	if res != captcha {
		return errors.New("wrong captcha")
	}
	return nil
}

func (uc *UserUsecase) ListUsers(ctx context.Context, req *v1.ListUsersRequest) []*User {
	return uc.repo.ListUsers(ctx, req)
}

func (uc *UserUsecase) CreateUser(ctx context.Context, u *User) (*User, error) {
	return uc.repo.CreateUser(ctx, u)
}

func (uc *UserUsecase) GetUser(ctx context.Context, u *User) (*User, error) {
	return uc.repo.GetUser(ctx, u)
}

func (uc *UserUsecase) UpdateUser(ctx context.Context, u *User) (*User, error) {
	return uc.repo.UpdateUser(ctx, u)
}

func (uc *UserUsecase) UpdateUserPassowrd(ctx context.Context, u *User, oldPassword string, newPassword string) (*User, error) {
	if !password.ValidatePassword(oldPassword, u.Password) {
		return nil, v1.ErrorInvalidUsernameOrPassword("")
	}
	password, _ := password.GeneratePasswordHash(newPassword)
	return uc.repo.UpdateUser(ctx, &User{ID: u.ID, Password: password})
}

func (uc *UserUsecase) UpdateUserAvatar(ctx context.Context, user *User, req *v1.UpdateUserAvatarRequest) (*User, error) {
	return uc.repo.UpdateUserAvatar(ctx, user, req)
}

func (uc *UserUsecase) GetUserProfile(ctx context.Context, id int) (*UserProfile, error) {
	return uc.repo.GetUserProfile(ctx, id)
}

func (uc *UserUsecase) UpdateUserProfile(ctx context.Context, u *UserProfile) (*UserProfile, error) {
	return uc.repo.UpdateUserProfile(ctx, u)
}

func (uc *UserUsecase) GetUserProfileCalendar(ctx context.Context, req *v1.GetUserProfileCalendarRequest) (*v1.GetUserProfileCalendarResponse, error) {
	return uc.repo.GetUserProfileCalendar(ctx, req)
}

func (uc *UserUsecase) GetUserProfileProblemSolved(ctx context.Context, req *v1.GetUserProfileProblemSolvedRequest) (*v1.GetUserProfileProblemSolvedResponse, error) {
	if req.Type.String() == "PROBLEMSET" {
		return uc.repo.GetUserProfileProblemsetProblemSolved(ctx, int(req.Id), int(req.Page), int(req.PerPage))
	} else if req.Type.String() == "CONTEST" {
		return uc.repo.GetUserProfileContestProblemSolved(ctx, int(req.Id), int(req.Page), int(req.PerPage))
	}
	return uc.repo.GetUserProfileGroupProblemSolved(ctx, int(req.Id), int(req.Page), int(req.PerPage))
}

// GetUserProfileCount 用户主页-统计
func (uc *UserUsecase) GetUserProfileCount(ctx context.Context, id int) (*v1.GetUserProfileCountResponse, error) {
	return uc.repo.GetUserProfileCount(ctx, id)
}

// ListUserProfileUserBadges 用户主页勋章成就
func (uc *UserUsecase) ListUserProfileUserBadges(ctx context.Context, id int) (*v1.ListUserProfileUserBadgesResponse, error) {
	return uc.repo.ListUserProfileUserBadges(ctx, id)
}
