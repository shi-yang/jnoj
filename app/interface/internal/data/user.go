package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type userRepo struct {
	data *Data
	log  *log.Helper
}

// NewUserRepo .
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
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

func (r *userRepo) GetUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	res := User{}
	err := r.data.db.WithContext(ctx).
		Where(&User{
			Username: u.Username,
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
		Phone:    res.Phone,
		Password: res.Password,
	}, nil
}

func (r *userRepo) CreateUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	res := User{
		Username: u.Username,
		Password: u.Password,
		Phone:    u.Phone,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.User{
		ID: res.ID,
	}, err
}

func (r *userRepo) Update(ctx context.Context, g *biz.User) (*biz.User, error) {
	return g, nil
}

func (r *userRepo) FindByID(ctx context.Context, id int) (*biz.User, error) {
	var o User
	err := r.data.db.WithContext(ctx).
		First(&o, "id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.User{
		ID:       o.ID,
		Username: o.Username,
		Nickname: o.Nickname,
	}, nil
}

func (r *userRepo) GetUserProfileCalendar(ctx context.Context, req *v1.GetUserProfileCalendarRequest) (*v1.GetUserProfileCalendarResponse, error) {
	res := new(v1.GetUserProfileCalendarResponse)
	var (
		start, end time.Time
	)
	if req.Year == 0 {
		end = time.Now()
		start = end.AddDate(-1, 0, 0)
	} else {
		start = time.Date(int(req.Year), 1, 1, 0, 0, 0, 0, time.UTC)
		end = time.Date(int(req.Year)+1, 1, 1, 0, 0, 0, 0, time.UTC)
	}
	db := r.data.db.WithContext(ctx).
		Select("DATE_FORMAT(created_at, '%Y/%m/%d') as date, count(*)").
		Table("submission").
		Where("user_id = ? and entity_type = ?", req.Id, biz.SubmissionEntityTypeCommon).
		Where("created_at >= ? and created_at < ?", start, end)
	db.Group("date")
	rows, _ := db.Rows()
	for rows.Next() {
		var r v1.GetUserProfileCalendarResponse_ProfileCalendar
		rows.Scan(&r.Date, &r.Count)
		res.Total += r.Count
		res.TotalActiveDays++
		res.SubmissionCalendar = append(res.SubmissionCalendar, &r)
	}
	r.data.db.WithContext(ctx).
		Select("year(created_at) as date").
		Table("submission").
		Group("date").
		Scan(&res.ActiveYears)
	res.Start = start.Format("2006/01/02")
	res.End = end.Format("2006/01/02")
	return res, nil
}
