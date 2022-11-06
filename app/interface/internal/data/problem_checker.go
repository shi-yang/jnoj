package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm/clause"
)

type ProblemChecker struct {
	ID        int
	Name      string
	UserID    int
	CreatedAt time.Time
}

// ListProblemCheckers .
func (r *problemRepo) ListProblemCheckers(ctx context.Context, req *v1.ListProblemCheckersRequest) ([]*biz.ProblemChecker, int64) {
	res := []ProblemChecker{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Find(&res).
		Count(&count)
	rv := make([]*biz.ProblemChecker, 0)
	for _, v := range res {
		rv = append(rv, &biz.ProblemChecker{
			ID: v.ID,
		})
	}
	return rv, count
}

// GetProblemChecker .
func (r *problemRepo) GetProblemChecker(ctx context.Context, id int) (*biz.ProblemChecker, error) {
	var res ProblemChecker
	err := r.data.db.Model(ProblemChecker{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemChecker{}, err
}

// CreateProblemChecker .
func (r *problemRepo) CreateProblemChecker(ctx context.Context, b *biz.ProblemChecker) (*biz.ProblemChecker, error) {
	res := ProblemChecker{Name: b.Name}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ProblemChecker{
		ID: res.ID,
	}, err
}

// UpdateProblemChecker .
func (r *problemRepo) UpdateProblemChecker(ctx context.Context, b *biz.ProblemChecker) (*biz.ProblemChecker, error) {
	res := ProblemChecker{
		ID: b.ID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteProblemChecker .
func (r *problemRepo) DeleteProblemChecker(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(ProblemChecker{ID: id}).
		Error
	return err
}
