package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm/clause"
)

type ContestUser struct {
	ID        int
	Name      string
	UserID    int
	CreatedAt time.Time
}

// ListContestUsers .
func (r *contestRepo) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*biz.ContestUser, int64) {
	res := []ContestUser{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Find(&res).
		Count(&count)
	rv := make([]*biz.ContestUser, 0)
	for _, v := range res {
		rv = append(rv, &biz.ContestUser{
			ID: v.ID,
		})
	}
	return rv, count
}

// GetContestUser .
func (r *contestRepo) GetContestUser(ctx context.Context, id int) (*biz.ContestUser, error) {
	var res ContestUser
	err := r.data.db.Model(ContestUser{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.ContestUser{}, err
}

// CreateContestUser .
func (r *contestRepo) CreateContestUser(ctx context.Context, b *biz.ContestUser) (*biz.ContestUser, error) {
	res := ContestUser{Name: b.Name}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ContestUser{
		ID: res.ID,
	}, err
}

// UpdateContestUser .
func (r *contestRepo) UpdateContestUser(ctx context.Context, b *biz.ContestUser) (*biz.ContestUser, error) {
	res := ContestUser{
		ID: b.ID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteContestUser .
func (r *contestRepo) DeleteContestUser(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(ContestUser{ID: id}).
		Error
	return err
}
