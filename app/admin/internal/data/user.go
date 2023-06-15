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
	ID        int
	Username  string
	Nickname  string
	Password  string
	Email     string
	Phone     string
	Role      int
	Status    int
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
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
		First(&res).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.User{
		ID:       res.ID,
		Username: res.Username,
		Nickname: res.Nickname,
		Email:    res.Email,
		Phone:    res.Phone,
		Password: res.Password,
		Role:     res.Role,
		Status:   res.Status,
	}, nil
}

func (r *userRepo) CreateUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	res := User{
		Username: u.Username,
		Password: u.Password,
		Email:    u.Email,
		Nickname: u.Nickname,
		Phone:    u.Phone,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
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
	updateColumn := []string{"id", "username", "nickname", "role", "status"}
	if update.Password != "" {
		updateColumn = append(updateColumn, "password")
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
		Model(&User{})
	if req.Username != "" {
		db.Where("username like ?", fmt.Sprintf("%%%s%%", req.Username))
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
		rv = append(rv, u)
	}
	return rv, count
}
