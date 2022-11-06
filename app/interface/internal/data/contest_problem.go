package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm/clause"
)

type ContestProblem struct {
	ID        int
	Number    int
	UserID    int
	CreatedAt time.Time
}

// ListContestProblems .
func (r *contestRepo) ListContestProblems(ctx context.Context, req *v1.ListContestProblemsRequest) ([]*biz.ContestProblem, int64) {
	res := []ContestProblem{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Find(&res).
		Count(&count)
	rv := make([]*biz.ContestProblem, 0)
	for _, v := range res {
		rv = append(rv, &biz.ContestProblem{
			ID: v.ID,
		})
	}
	return rv, count
}

// GetContestProblem .
func (r *contestRepo) GetContestProblem(ctx context.Context, id int) (*biz.ContestProblem, error) {
	var res ContestProblem
	err := r.data.db.Model(ContestProblem{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.ContestProblem{}, err
}

// CreateContestProblem .
func (r *contestRepo) CreateContestProblem(ctx context.Context, b *biz.ContestProblem) (*biz.ContestProblem, error) {
	res := ContestProblem{Number: b.Number}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ContestProblem{
		ID: res.ID,
	}, err
}

// UpdateContestProblem .
func (r *contestRepo) UpdateContestProblem(ctx context.Context, b *biz.ContestProblem) (*biz.ContestProblem, error) {
	res := ContestProblem{
		ID: b.ID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteContestProblem .
func (r *contestRepo) DeleteContestProblem(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(ContestProblem{ID: id}).
		Error
	return err
}
