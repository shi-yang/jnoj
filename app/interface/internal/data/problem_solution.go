package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type ProblemSolution struct {
	ID        int
	Name      string
	Content   string
	Type      string
	ProblemID int
	UserID    int
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

// ListProblemSolutions .
func (r *problemRepo) ListProblemSolutions(ctx context.Context, req *v1.ListProblemSolutionsRequest) ([]*biz.ProblemSolution, int64) {
	res := []ProblemSolution{}
	count := int64(0)
	db := r.data.db.WithContext(ctx)
	db.Where("problem_id = ?", req.Id)
	db.Find(&res).
		Count(&count)
	rv := make([]*biz.ProblemSolution, 0)
	for _, v := range res {
		rv = append(rv, &biz.ProblemSolution{
			ID:        v.ID,
			Name:      v.Name,
			Type:      v.Type,
			CreatedAt: v.CreatedAt,
			UpdatedAt: v.UpdatedAt,
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
	return &biz.ProblemSolution{
		ID:        res.ID,
		ProblemID: res.ProblemID,
		Name:      res.Name,
		Content:   res.Content,
		Type:      res.Type,
		UserID:    res.UserID,
		CreatedAt: res.CreatedAt,
		UpdatedAt: res.UpdatedAt,
	}, err
}

// CreateProblemSolution .
func (r *problemRepo) CreateProblemSolution(ctx context.Context, p *biz.ProblemSolution) (*biz.ProblemSolution, error) {
	res := ProblemSolution{
		ProblemID: p.ProblemID,
		Name:      p.Name,
		Content:   p.Content,
		Type:      p.Type,
		UserID:    p.UserID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ProblemSolution{
		ID: res.ID,
	}, err
}

// UpdateProblemSolution .
func (r *problemRepo) UpdateProblemSolution(ctx context.Context, p *biz.ProblemSolution) (*biz.ProblemSolution, error) {
	res := ProblemSolution{
		ID:      p.ID,
		Name:    p.Name,
		Content: p.Content,
		Type:    p.Type,
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
		Delete(&ProblemSolution{ID: id}).
		Error
	return err
}
