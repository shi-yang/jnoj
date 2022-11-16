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
	ID                 int
	Name               string
	TimeLimit          int64
	MemoryLimit        int64
	AcceptedCount      int
	SubmitCount        int
	UserID             int
	CheckerID          int
	VerificationStatus int
	CreatedAt          time.Time
	UpdatedAt          time.Time
	DeletedAt          gorm.DeletedAt
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
			ID:            v.ID,
			Name:          v.Name,
			SubmitCount:   v.SubmitCount,
			AcceptedCount: v.AcceptedCount,
			CreatedAt:     v.CreatedAt,
			UpdatedAt:     v.UpdatedAt,
		})
	}
	return rv, count
}

// GetProblem .
func (r *problemRepo) GetProblem(ctx context.Context, id int) (*biz.Problem, error) {
	var res Problem
	err := r.data.db.Model(&Problem{}).
		First(&res, "id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.Problem{
		ID:            res.ID,
		Name:          res.Name,
		TimeLimit:     res.TimeLimit,
		MemoryLimit:   res.MemoryLimit,
		AcceptedCount: res.AcceptedCount,
		SubmitCount:   res.SubmitCount,
		UserID:        res.UserID,
		CheckerID:     res.CheckerID,
	}, err
}

// CreateProblem .
func (r *problemRepo) CreateProblem(ctx context.Context, p *biz.Problem) (*biz.Problem, error) {
	res := Problem{
		Name:        p.Name,
		UserID:      p.UserID,
		TimeLimit:   p.TimeLimit,
		MemoryLimit: p.MemoryLimit,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Problem{
		ID: res.ID,
	}, err
}

// UpdateProblem .
func (r *problemRepo) UpdateProblem(ctx context.Context, p *biz.Problem) (*biz.Problem, error) {
	update := Problem{
		ID:          p.ID,
		TimeLimit:   p.TimeLimit,
		MemoryLimit: p.MemoryLimit,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&update).Error
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

func (r *problemRepo) UpdateProblemChecker(ctx context.Context, id int, checkerID int) error {
	update := Problem{
		ID:        id,
		CheckerID: checkerID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&update).Error
	return err
}
