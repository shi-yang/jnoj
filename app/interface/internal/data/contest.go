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

type contestRepo struct {
	data *Data
	log  *log.Helper
}

type Contest struct {
	ID          int
	Name        string
	StartTime   time.Time
	EndTime     time.Time
	FrozenTime  *time.Time
	Type        int
	Status      int
	Description string
	GroupID     int
	UserID      int
	CreatedAt   time.Time
	UpdatedAt   time.Time
	DeletedAt   gorm.DeletedAt
}

// NewContestRepo .
func NewContestRepo(data *Data, logger log.Logger) biz.ContestRepo {
	return &contestRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListContests .
func (r *contestRepo) ListContests(ctx context.Context, req *v1.ListContestsRequest) ([]*biz.Contest, int64) {
	res := []Contest{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Find(&res).
		Count(&count)
	rv := make([]*biz.Contest, 0)
	for _, v := range res {
		rv = append(rv, &biz.Contest{
			ID:        v.ID,
			Name:      v.Name,
			StartTime: v.StartTime,
			EndTime:   v.EndTime,
		})
	}
	return rv, count
}

// GetContest .
func (r *contestRepo) GetContest(ctx context.Context, id int) (*biz.Contest, error) {
	var res Contest
	err := r.data.db.Model(Contest{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.Contest{
		ID:          res.ID,
		Name:        res.Name,
		StartTime:   res.StartTime,
		EndTime:     res.EndTime,
		FrozenTime:  res.FrozenTime,
		Type:        res.Type,
		Description: res.Description,
		CreatedAt:   res.CreatedAt,
	}, err
}

// CreateContest .
func (r *contestRepo) CreateContest(ctx context.Context, b *biz.Contest) (*biz.Contest, error) {
	res := Contest{
		Name:      b.Name,
		StartTime: b.StartTime,
		EndTime:   b.EndTime,
		UserID:    b.UserID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Contest{
		ID: res.ID,
	}, err
}

// UpdateContest .
func (r *contestRepo) UpdateContest(ctx context.Context, b *biz.Contest) (*biz.Contest, error) {
	res := Contest{
		ID: b.ID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteContest .
func (r *contestRepo) DeleteContest(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(Contest{ID: id}).
		Error
	return err
}
