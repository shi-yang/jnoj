package data

import (
	"context"
	"jnoj/app/sandbox/internal/biz"
	"time"

	"gorm.io/gorm"
)

type ProblemFile struct {
	ID        int
	Name      string
	Language  int
	Content   string
	Type      string
	ProblemID int
	UserID    int
	FileType  string
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

func (r *submissionRepo) GetProblemFile(ctx context.Context, problem *biz.ProblemFile) (*biz.ProblemFile, error) {
	var res ProblemFile
	err := r.data.db.Model(ProblemFile{}).
		First(&res, problem).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemFile{
		ID:        res.ID,
		ProblemID: res.ProblemID,
		Name:      res.Name,
		Language:  res.Language,
		Content:   res.Content,
		Type:      res.Type,
		FileType:  res.FileType,
		UserID:    res.UserID,
		CreatedAt: res.CreatedAt,
		UpdatedAt: res.UpdatedAt,
	}, err
}
