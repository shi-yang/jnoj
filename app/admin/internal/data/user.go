package data

import (
	"context"
	"fmt"
	"time"

	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type userRepo struct {
	data *Data
	log  *log.Helper
}

// NewUser .
func NewUserRepo(data *Data, logger log.Logger) biz.UserRepo {
	return &userRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

type User struct {
	ID          int
	Username    string
	Nickname    string
	Password    string
	Email       string
	Phone       string
	Role        int
	Status      int
	CreatedAt   time.Time
	UpdatedAt   time.Time
	DeletedAt   gorm.DeletedAt
	UserProfile *UserProfile `gorm:"foreignKey:UserID"`
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

// UserExpiration 用户有效期
type UserExpiration struct {
	ID          int
	UserID      int
	Type        int // 类型：角色、可用状态
	PeriodValue int // 期间的值
	EndValue    int // 结束后的值
	Status      int // 有效期事件状态
	StartTime   time.Time
	EndTime     time.Time
	CreatedAt   time.Time
}

func (r *userRepo) GetUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	res := User{}
	err := r.data.db.WithContext(ctx).
		Where(&User{
			ID:       u.ID,
			Username: u.Username,
			Email:    u.Email,
			Phone:    u.Phone,
		}).
		Preload("UserProfile").
		First(&res).
		Error
	if err != nil {
		return nil, err
	}
	user := &biz.User{
		ID:       res.ID,
		Username: res.Username,
		Nickname: res.Nickname,
		Email:    res.Email,
		Phone:    res.Phone,
		Password: res.Password,
		Role:     res.Role,
		Status:   res.Status,
	}
	if res.UserProfile != nil {
		user.UserProfile = &biz.UserProfile{
			UserID:    res.ID,
			Realname:  res.UserProfile.Realname,
			Location:  res.UserProfile.Location,
			Bio:       res.UserProfile.Bio,
			Gender:    res.UserProfile.Gender,
			School:    res.UserProfile.School,
			Birthday:  res.UserProfile.Birthday,
			Company:   res.UserProfile.Company,
			Job:       res.UserProfile.Job,
			CreatedAt: res.UserProfile.CreatedAt,
			UpdatedAt: res.UserProfile.UpdatedAt,
		}
	}
	return user, nil
}

func (r *userRepo) CreateUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	res := User{
		Username: u.Username,
		Password: u.Password,
		Email:    u.Email,
		Nickname: u.Nickname,
		Phone:    u.Phone,
	}
	tx := r.data.db.WithContext(ctx).Begin()
	err := tx.Omit(clause.Associations).
		Create(&res).Error
	if err != nil {
		return nil, err
	}
	err = tx.Omit(clause.Associations).Create(&UserProfile{
		UserID:   res.ID,
		Realname: u.Realname,
	}).Error
	if err != nil {
		tx.Rollback()
		return nil, err
	}
	tx.Commit()
	return &biz.User{
		ID: res.ID,
	}, err
}

func (r *userRepo) UpdateUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	update := User{
		ID:       u.ID,
		Username: u.Username,
		Password: u.Password,
		Nickname: u.Nickname,
		Role:     u.Role,
		Status:   u.Status,
	}
	updateColumn := []string{"id", "role", "status"}
	if update.Password != "" {
		updateColumn = append(updateColumn, "password")
	}
	if update.Nickname != "" {
		updateColumn = append(updateColumn, "nickname")
	}
	if update.Username != "" {
		updateColumn = append(updateColumn, "username")
	}
	if u.Realname != "" {
		r.data.db.WithContext(ctx).Omit(clause.Associations).
			Select("Realname").
			Where("user_id = ?", u.ID).
			Updates(&UserProfile{Realname: u.Realname})
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Select(updateColumn).
		Updates(&update).Error
	return u, err
}

// ListUsers .
func (r *userRepo) ListUsers(ctx context.Context, req *v1.ListUsersRequest) ([]*biz.User, int64) {
	res := []User{}
	count := int64(0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&User{}).
		Preload("UserProfile")
	if req.Username != "" {
		db.Where("username like ?", fmt.Sprintf("%%%s%%", req.Username))
	}
	if req.Nickname != "" {
		db.Where("nickname like ?", fmt.Sprintf("%%%s%%", req.Nickname))
	}
	if req.Realname != "" {
		db.Where("id in (?)", r.data.db.Select("user_id").WithContext(ctx).Model(&UserProfile{}).
			Where("realname like ?", fmt.Sprintf("%%%s%%", req.Realname)))
	}

	if req.Role != nil {
		db.Where("role in (?)", int(*req.Role))
	}
	if req.Status != nil {
		db.Where("status in (?)", int(*req.Status))
	}
	db.Count(&count)
	db.Order("id desc")
	db.Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&res)
	rv := make([]*biz.User, 0)
	for _, v := range res {
		u := &biz.User{
			ID:        v.ID,
			Nickname:  v.Nickname,
			Username:  v.Username,
			Role:      v.Role,
			Status:    v.Status,
			CreatedAt: v.CreatedAt,
		}
		if v.UserProfile != nil {
			u.UserProfile = &biz.UserProfile{
				UserID:    v.ID,
				Realname:  v.UserProfile.Realname,
				Bio:       v.UserProfile.Bio,
				Gender:    v.UserProfile.Gender,
				School:    v.UserProfile.School,
				Birthday:  v.UserProfile.Birthday,
				Company:   v.UserProfile.Company,
				Job:       v.UserProfile.Job,
				CreatedAt: v.UserProfile.CreatedAt,
				UpdatedAt: v.UserProfile.UpdatedAt,
			}
		}
		rv = append(rv, u)
	}
	return rv, count
}

// CreateUserExpiration 创建用户有效期
func (r *userRepo) CreateUserExpiration(ctx context.Context, e *biz.UserExpiration) error {
	userExpiration := UserExpiration{
		UserID:      e.UserID,
		Type:        e.Type,
		PeriodValue: e.PeriodValue,
		EndValue:    e.EndValue,
		StartTime:   e.StartTime,
		EndTime:     e.EndTime,
		Status:      e.Status,
	}
	return r.data.db.WithContext(ctx).Create(&userExpiration).Error
}

// DeleteUserExpiration 删除用户有效期
func (r *userRepo) DeleteUserExpiration(ctx context.Context, id int) error {
	return r.data.db.WithContext(ctx).Delete(&UserExpiration{ID: id}).Error
}

// ListUserExpirations 获取用户有效期列表
func (r *userRepo) ListUserExpirations(ctx context.Context, userID []int, statuses []int) []*biz.UserExpiration {
	res := []UserExpiration{}
	db := r.data.db.WithContext(ctx).
		Model(&UserExpiration{})
	if len(statuses) > 0 {
		db.Where("status in (?)", statuses)
	}
	if len(userID) > 0 {
		db.Where("user_id in (?)", userID)
	}
	db.Find(&res)
	rv := make([]*biz.UserExpiration, 0)
	for _, v := range res {
		rv = append(rv, &biz.UserExpiration{
			ID:          v.ID,
			UserID:      v.UserID,
			Type:        v.Type,
			PeriodValue: v.PeriodValue,
			EndValue:    v.EndValue,
			StartTime:   v.StartTime,
			EndTime:     v.EndTime,
			Status:      v.Status,
		})
	}
	return rv
}

// UpdateUserExpiration 更新用户有效期
func (r *userRepo) UpdateUserExpiration(ctx context.Context, e *biz.UserExpiration) error {
	update := UserExpiration{
		ID:     e.ID,
		Status: e.Status,
	}
	updateColumn := []string{"id", "status"}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Select(updateColumn).
		Updates(&update).Error
	return err
}
