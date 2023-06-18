package biz

import (
	"context"
	v1 "jnoj/api/admin/v1"
	"jnoj/pkg/password"
	"math/rand"
	"sort"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/robfig/cron/v3"
)

// User is a User model.
type User struct {
	ID        int
	Username  string
	Nickname  string
	Realname  string
	Email     string
	Phone     string
	Password  string
	Role      int
	Status    int
	CreatedAt time.Time
}

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

// UserExpiration 用户有效期
type UserExpiration struct {
	ID          int
	UserID      int
	Type        int // 类型：角色、可用状态
	PeriodValue int // 期间的值
	EndValue    int // 到期后的值
	Status      int // 有效期事件状态
	StartTime   time.Time
	EndTime     time.Time
	CreatedAt   time.Time
}

const (
	UserExpirationTypeRole = iota
	UserExpirationTypeStatus
)

const (
	UserExpirationStatusPending = iota
	UserExpirationStatusInProgress
	UserExpirationStatusCompleted
)

// UserRepo is a User repo.
type UserRepo interface {
	GetUser(context.Context, *User) (*User, error)
	CreateUser(context.Context, *User) (*User, error)
	UpdateUser(context.Context, *User) (*User, error)
	ListUsers(context.Context, *v1.ListUsersRequest) ([]*User, int64)
	CreateUserExpiration(context.Context, *UserExpiration) error
	ListUserExpirations(ctx context.Context, userId []int, statuses []int) []*UserExpiration
	DeleteUserExpiration(context.Context, int) error
	UpdateUserExpiration(context.Context, *UserExpiration) error
}

// UserUsecase is a User usecase.
type UserUsecase struct {
	repo UserRepo
	log  *log.Helper
}

// NewUserUsecase new a User usecase.
func NewUserUsecase(repo UserRepo, logger log.Logger) *UserUsecase {
	uc := &UserUsecase{repo: repo, log: log.NewHelper(logger)}
	c := cron.New()
	c.AddFunc("@hourly", func() {
		uc.CronCheckUserExpiration(context.TODO())
	})
	c.Start()
	return uc
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

// BatchCreateUser 批量创建用户
func (uc *UserUsecase) BatchCreateUser(ctx context.Context, req *v1.BatchCreateUserRequest) (*v1.BatchCreateUserResponse, error) {
	res := new(v1.BatchCreateUserResponse)
	// 生成长度为8，包含数字和小写字母、不含易混淆字符的密码
	newPassword := func() string {
		rand.Seed(time.Now().UnixNano())
		var letterRunes = []rune("23456789abcdefghijkmnpqrstuvwxyz")
		b := make([]rune, 8)
		for i := range b {
			b[i] = letterRunes[rand.Intn(len(letterRunes))]
		}
		return string(b)
	}
	for _, v := range req.Users {
		// 查询用户是否已经存在
		_, err := uc.repo.GetUser(ctx, &User{Username: v.Username})
		if err == nil {
			res.Failed = append(res.Failed, &v1.BatchCreateUserResponse_User{
				Username: v.Username,
				Reason:   "user already exists",
			})
			continue
		}
		passwd := newPassword()
		newUser := &User{
			Username: v.Username,
			Nickname: v.Nickname,
		}
		newUser.Password, _ = password.GeneratePasswordHash(passwd)
		if newUser.Nickname == "" {
			newUser.Nickname = v.Username
		}
		_, err = uc.repo.CreateUser(ctx, newUser)
		if err != nil {
			res.Failed = append(res.Failed, &v1.BatchCreateUserResponse_User{
				Username: newUser.Username,
				Reason:   err.Error(),
			})
		} else {
			res.Success = append(res.Success, &v1.BatchCreateUserResponse_User{
				Username: newUser.Username,
				Password: passwd,
			})
		}
	}
	return res, nil
}

// UpdateUser updates a User, and returns the updated User.
func (uc *UserUsecase) UpdateUser(ctx context.Context, u *User) (*User, error) {
	if u.Password != "" {
		u.Password, _ = password.GeneratePasswordHash(u.Password)
	}
	return uc.repo.UpdateUser(ctx, u)
}

// ListUsers list all Users.
func (uc *UserUsecase) ListUsers(ctx context.Context, req *v1.ListUsersRequest) ([]*User, int64) {
	return uc.repo.ListUsers(ctx, req)
}

// CreateUserExpiration 创建用户有效期
func (uc *UserUsecase) CreateUserExpiration(ctx context.Context, ue *UserExpiration) error {
	return uc.repo.CreateUserExpiration(ctx, ue)
}

// DeleteUserExpiration 删除用户有效期
func (uc *UserUsecase) DeleteUserExpiration(ctx context.Context, id int) error {
	return uc.repo.DeleteUserExpiration(ctx, id)
}

// ListUserExpirations 用户有效期列表
func (uc *UserUsecase) ListUserExpirations(ctx context.Context, uid []int) []*UserExpiration {
	return uc.repo.ListUserExpirations(ctx, uid, nil)
}

// CronCheckUserExpiration 定期检查用户有效期
func (uc *UserUsecase) CronCheckUserExpiration(ctx context.Context) {
	now := time.Now()
	expirations := uc.repo.ListUserExpirations(ctx, nil, []int{UserExpirationStatusPending, UserExpirationStatusInProgress})
	// 按照结束时间进行排序，结束时间越大，越靠后
	sort.Slice(expirations, func(i, j int) bool {
		return expirations[i].EndTime.After(expirations[j].EndTime)
	})
	for _, e := range expirations {
		if e.StartTime.After(now) {
			continue
		}
		u, err := uc.repo.GetUser(ctx, &User{ID: e.UserID})
		if err != nil {
			e.Status = UserExpirationStatusCompleted
			uc.repo.UpdateUserExpiration(ctx, e)
			continue
		}
		// 满足开始条件
		if e.StartTime.Before(now) && e.Status == UserExpirationStatusPending {
			e.Status = UserExpirationStatusInProgress
			if e.Type == UserExpirationTypeRole && u.Role != e.PeriodValue {
				u.Role = e.PeriodValue
				uc.repo.UpdateUser(ctx, u)
			} else if e.Type == UserExpirationTypeStatus && u.Status != e.PeriodValue {
				u.Status = e.PeriodValue
				uc.repo.UpdateUser(ctx, u)
			}
			uc.repo.UpdateUserExpiration(ctx, e)
		} else if e.EndTime.Before(now) {
			// 满足结束条件
			e.Status = UserExpirationStatusCompleted
			if e.Type == UserExpirationTypeRole && u.Role != e.EndValue {
				u.Role = e.EndValue
				uc.repo.UpdateUser(ctx, u)
			} else if e.Type == UserExpirationTypeStatus && u.Status != e.EndValue {
				u.Status = e.EndValue
				uc.repo.UpdateUser(ctx, u)
			}
			uc.repo.UpdateUserExpiration(ctx, e)
		}
	}
}
