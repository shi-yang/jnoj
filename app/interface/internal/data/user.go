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
		Nickname: u.Nickname,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&update).Error
	return u, err
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
	res.Start = start.Format("2006/01/02")
	res.End = end.Format("2006/01/02")
	db := r.data.db.WithContext(ctx).
		Select("DATE_FORMAT(created_at, '%Y/%m/%d') as date, count(*)").
		Table("submission").
		Where("user_id = ? and entity_type = ?", req.Id, biz.SubmissionEntityTypeProblemset).
		Where("created_at >= ? and created_at < ?", start, end)
	db.Group("date")
	rows, _ := db.Rows()
	for rows.Next() {
		var r v1.GetUserProfileCalendarResponse_ProfileCalendar
		rows.Scan(&r.Date, &r.Count)
		res.TotalSubmission += r.Count
		res.TotalActiveDays++
		res.SubmissionCalendar = append(res.SubmissionCalendar, &r)
	}
	r.data.db.WithContext(ctx).
		Select("year(created_at) as date").
		Table("submission").
		Where("user_id = ? and entity_type = ?", req.Id, biz.SubmissionEntityTypeProblemset).
		Group("date").
		Scan(&res.ActiveYears)
	r.data.db.WithContext(ctx).
		Select("COUNT(DISTINCT(problem_id))").
		Table("submission").
		Where("user_id = ? and entity_type = ?", req.Id, biz.SubmissionEntityTypeProblemset).
		Where("verdict = ?", biz.SubmissionVerdictAccepted).
		Where("created_at >= ? and created_at < ?", start, end).
		Scan(&res.TotalProblemSolved)
	return res, nil
}

func (r *userRepo) GetUserProfileProblemSolved(ctx context.Context, req *v1.GetUserProfileProblemSolvedRequest) (*v1.GetUserProfileProblemSolvedResponse, error) {
	res := new(v1.GetUserProfileProblemSolvedResponse)
	var t []struct {
		ProblemID int
		Verdict   int
	}
	r.data.db.WithContext(ctx).
		Select("problem_id, SUM(case when verdict = ? then 1 else 0 end) as verdict", biz.SubmissionVerdictAccepted).
		Table("submission").
		Where("user_id = ? and entity_type = ?", req.Id, biz.SubmissionEntityTypeProblemset).
		Group("problem_id").
		Order("problem_id").
		Scan(&t)
	for _, v := range t {
		status := v1.GetUserProfileProblemSolvedResponse_Problem_CORRECT
		if v.Verdict == 0 {
			status = v1.GetUserProfileProblemSolvedResponse_Problem_INCORRECT
		}
		res.Problems = append(res.Problems, &v1.GetUserProfileProblemSolvedResponse_Problem{
			Id:     int32(v.ProblemID),
			Status: status,
		})
	}
	return res, nil
}

// GetCaptcha 获取验证码
func (r *userRepo) GetCaptcha(ctx context.Context, key string) (string, error) {
	val, err := r.data.redisdb.Get(ctx, key).Result()
	if err != nil {
		return "", err
	}
	return val, nil
}

// SaveCaptcha 保存验证码，5分钟
func (r *userRepo) SaveCaptcha(ctx context.Context, key string, value string) error {
	return r.data.redisdb.Set(ctx, key, value, time.Minute*5).Err()
}
