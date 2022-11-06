package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm/clause"
)

type ProblemTest struct {
	ID        int
	Name      string
	UserID    int
	CreatedAt time.Time
}

// ListProblemTests .
func (r *problemRepo) ListProblemTests(ctx context.Context, req *v1.ListProblemTestsRequest) ([]*biz.ProblemTest, int64) {
	res := []ProblemTest{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Find(&res).
		Count(&count)
	rv := make([]*biz.ProblemTest, 0)
	for _, v := range res {
		rv = append(rv, &biz.ProblemTest{
			ID: int64(v.ID),
		})
	}
	return rv, count
}

// GetProblemTest .
func (r *problemRepo) GetProblemTest(ctx context.Context, id int) (*biz.ProblemTest, error) {
	var res ProblemTest
	err := r.data.db.Model(ProblemTest{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemTest{}, err
}

// CreateProblemTest .
func (r *problemRepo) CreateProblemTest(ctx context.Context, b *biz.ProblemTest) (*biz.ProblemTest, error) {
	res := ProblemTest{Name: b.Content}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ProblemTest{
		ID: int64(res.ID),
	}, err
}

// UpdateProblemTest .
func (r *problemRepo) UpdateProblemTest(ctx context.Context, b *biz.ProblemTest) (*biz.ProblemTest, error) {
	res := ProblemTest{
		ID: int(b.ID),
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteProblemTest .
func (r *problemRepo) DeleteProblemTest(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(ProblemTest{ID: id}).
		Error
	return err
}
