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

type problemRepo struct {
	data *Data
	log  *log.Helper
}

type Problem struct {
	ID            int
	Name          string
	TimeLimit     int64
	MemoryLimit   int64
	AcceptedCount int
	SubmitCount   int
	UserID        int
	CreatedAt     time.Time
	UpdatedAt     time.Time
	DeletedAt     gorm.DeletedAt
}

// NewProblemRepo .
func NewProblemRepo(data *Data, logger log.Logger) biz.ProblemRepo {
	return &problemRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListProblems .
func (r *problemRepo) ListProblems(ctx context.Context, req *v1.ListProblemsRequest) ([]*biz.Problem, int64) {
	res := []Problem{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Find(&res).
		Count(&count)
	rv := make([]*biz.Problem, 0)
	for _, v := range res {
		rv = append(rv, &biz.Problem{
			ID: v.ID,
		})
	}
	return rv, count
}

// GetProblem .
func (r *problemRepo) GetProblem(ctx context.Context, id int) (*biz.Problem, error) {
	var res Problem
	err := r.data.db.Model(Problem{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.Problem{}, err
}

// CreateProblem .
func (r *problemRepo) CreateProblem(ctx context.Context, b *biz.Problem) (*biz.Problem, error) {
	res := Problem{Name: b.Name}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Problem{
		ID: res.ID,
	}, err
}

// UpdateProblem .
func (r *problemRepo) UpdateProblem(ctx context.Context, b *biz.Problem) (*biz.Problem, error) {
	res := Problem{
		ID: b.ID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteProblem .
func (r *problemRepo) DeleteProblem(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(Problem{ID: id}).
		Error
	return err
}
