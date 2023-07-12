package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type ProblemStatement struct {
	ID        int
	ProblemID int
	Name      string
	Type      int
	Language  string
	Legend    string
	Input     string
	Output    string
	Note      string
	UserID    int
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

// ListProblemStatements .
func (r *problemRepo) ListProblemStatements(ctx context.Context, req *v1.ListProblemStatementsRequest) ([]*biz.ProblemStatement, int64) {
	res := []ProblemStatement{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Where("problem_id = ?", req.Id).
		Find(&res).
		Count(&count)
	rv := make([]*biz.ProblemStatement, 0)
	for _, v := range res {
		rv = append(rv, &biz.ProblemStatement{
			ID:       v.ID,
			Name:     v.Name,
			Legend:   v.Legend,
			Type:     v.Type,
			Input:    v.Input,
			Output:   v.Output,
			Language: v.Language,
			Note:     v.Note,
		})
	}
	return rv, count
}

// GetProblemStatement .
func (r *problemRepo) GetProblemStatement(ctx context.Context, id int) (*biz.ProblemStatement, error) {
	var res ProblemStatement
	err := r.data.db.Model(ProblemStatement{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemStatement{}, err
}

// CreateProblemStatement .
func (r *problemRepo) CreateProblemStatement(ctx context.Context, p *biz.ProblemStatement) (*biz.ProblemStatement, error) {
	res := ProblemStatement{
		ProblemID: p.ProblemID,
		Name:      p.Name,
		UserID:    p.UserID,
		Language:  p.Language,
		Type:      p.Type,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ProblemStatement{
		ID: res.ID,
	}, err
}

// UpdateProblemStatement .
func (r *problemRepo) UpdateProblemStatement(ctx context.Context, b *biz.ProblemStatement) (*biz.ProblemStatement, error) {
	res := ProblemStatement{
		ID:       b.ID,
		Name:     b.Name,
		Type:     b.Type,
		Input:    b.Input,
		Output:   b.Output,
		Language: b.Language,
		Legend:   b.Legend,
		Note:     b.Note,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Select("Name", "Input", "Output", "Legend", "Note", "Type").
		Updates(&res).Error
	return &biz.ProblemStatement{ID: res.ID}, err
}

// DeleteProblemStatement .
func (r *problemRepo) DeleteProblemStatement(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(&ProblemStatement{ID: id}).
		Error
	return err
}
