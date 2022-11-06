package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm/clause"
)

type ProblemSolution struct {
	ID        int
	Name      string
	UserID    int
	CreatedAt time.Time
}

// ListProblemSolutions .
func (r *problemRepo) ListProblemSolutions(ctx context.Context, req *v1.ListProblemSolutionsRequest) ([]*biz.ProblemSolution, int64) {
	res := []ProblemSolution{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Find(&res).
		Count(&count)
	rv := make([]*biz.ProblemSolution, 0)
	for _, v := range res {
		rv = append(rv, &biz.ProblemSolution{
			ID: v.ID,
		})
	}
	return rv, count
}

// GetProblemSolution .
func (r *problemRepo) GetProblemSolution(ctx context.Context, id int) (*biz.ProblemSolution, error) {
	var res ProblemSolution
	err := r.data.db.Model(ProblemSolution{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemSolution{}, err
}

// CreateProblemSolution .
func (r *problemRepo) CreateProblemSolution(ctx context.Context, b *biz.ProblemSolution) (*biz.ProblemSolution, error) {
	res := ProblemSolution{Name: b.Name}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ProblemSolution{
		ID: res.ID,
	}, err
}

// UpdateProblemSolution .
func (r *problemRepo) UpdateProblemSolution(ctx context.Context, b *biz.ProblemSolution) (*biz.ProblemSolution, error) {
	res := ProblemSolution{
		ID: b.ID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteProblemSolution .
func (r *problemRepo) DeleteProblemSolution(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(ProblemSolution{ID: id}).
		Error
	return err
}
