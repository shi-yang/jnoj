package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type ProblemFile struct {
	ID        int
	Name      string
	Content   string
	Type      string
	ProblemID int
	UserID    int
	FileType  string
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

// ListProblemFiles .
func (r *problemRepo) ListProblemFiles(ctx context.Context, req *v1.ListProblemFilesRequest) ([]*biz.ProblemFile, int64) {
	res := []ProblemFile{}
	count := int64(0)
	db := r.data.db.WithContext(ctx).
		Where("problem_id = ?", req.Id).
		Where("file_type = ?", req.FileType)
	db.Find(&res).
		Count(&count)
	rv := make([]*biz.ProblemFile, 0)
	for _, v := range res {
		rv = append(rv, &biz.ProblemFile{
			ID:        v.ID,
			Name:      v.Name,
			Type:      v.Type,
			CreatedAt: v.CreatedAt,
			UpdatedAt: v.UpdatedAt,
		})
	}
	return rv, count
}

// GetProblemFile .
func (r *problemRepo) GetProblemFile(ctx context.Context, id int) (*biz.ProblemFile, error) {
	var res ProblemFile
	err := r.data.db.Model(ProblemFile{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemFile{
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

// CreateProblemFile .
func (r *problemRepo) CreateProblemFile(ctx context.Context, p *biz.ProblemFile) (*biz.ProblemFile, error) {
	res := ProblemFile{
		ProblemID: p.ProblemID,
		Name:      p.Name,
		Content:   p.Content,
		Type:      p.Type,
		UserID:    p.UserID,
		FileType:  p.FileType,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ProblemFile{
		ID: res.ID,
	}, err
}

// UpdateProblemFile .
func (r *problemRepo) UpdateProblemFile(ctx context.Context, p *biz.ProblemFile) (*biz.ProblemFile, error) {
	res := ProblemFile{
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

// DeleteProblemFile .
func (r *problemRepo) DeleteProblemFile(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(&ProblemFile{ID: id}).
		Error
	return err
}

func (r *problemRepo) GetProblemChecker(ctx context.Context, id int) (*biz.ProblemFile, error) {
	var res ProblemFile
	err := r.data.db.WithContext(ctx).
		Where("id = (?)", r.data.db.Select("checker_id").Model(&Problem{}).Where("id = ?", id)).
		First(&res).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemFile{
		ID:      res.ID,
		Name:    res.Name,
		Content: res.Content,
	}, nil
}
