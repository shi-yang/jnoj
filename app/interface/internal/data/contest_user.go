package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type ContestUser struct {
	ID        int
	ContestID int
	UserID    int
	CreatedAt time.Time
	User      *User `json:"user" gorm:"foreignKey:UserID"`
}

// ListContestUsers .
func (r *contestRepo) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*biz.ContestUser, int64) {
	res := []ContestUser{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Where("contest_id = ?", req.Id).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		}).
		Find(&res).
		Count(&count)
	rv := make([]*biz.ContestUser, 0)
	for _, v := range res {
		rv = append(rv, &biz.ContestUser{
			ID:        v.ID,
			UserID:    v.UserID,
			ContestID: v.ContestID,
			Nickname:  v.User.Nickname,
		})
	}
	return rv, count
}

// ExistContestUser .
func (r *contestRepo) ExistContestUser(ctx context.Context, cid int, uid int) bool {
	var res int
	r.data.db.WithContext(ctx).
		Model(&ContestUser{}).
		Select("1").
		Where("contest_id = ? and user_id = ?", cid, uid).
		Limit(1).
		Scan(&res)
	return res > 0
}

// CreateContestUser .
func (r *contestRepo) CreateContestUser(ctx context.Context, b *biz.ContestUser) (*biz.ContestUser, error) {
	res := ContestUser{UserID: b.UserID, ContestID: b.ContestID}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ContestUser{
		ID: res.ID,
	}, err
}

// DeleteContestUser .
func (r *contestRepo) DeleteContestUser(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(ContestUser{ID: id}).
		Error
	return err
}
