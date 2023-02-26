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
	ContestID int
	UserID    int
	Name      string
	Role      int
	CreatedAt time.Time
	User      *User `json:"user" gorm:"foreignKey:UserID"`
}

// ListContestUsers .
func (r *contestRepo) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*biz.ContestUser, int64) {
	count := int64(0)
	db := r.data.db.WithContext(ctx).
		Select("c.id, c.user_id, c.name, c.role, user.nickname").
		Table("contest_user as c").
		Joins("LEFT JOIN user on user.id = c.user_id").
		Where("c.contest_id = ?", req.ContestId)
	if req.Name != "" {
		db.Where("c.name = ?", req.Name)
	}
	if req.Role != nil {
		db.Where("c.role = ?", *req.Role)
	}
	db.Count(&count)
	if count == 0 {
		return nil, 0
	}
	rows, _ := db.Rows()
	rv := make([]*biz.ContestUser, 0)
	for rows.Next() {
		var v biz.ContestUser
		rows.Scan(&v.ID, &v.UserID, &v.Name, &v.Role, &v.UserNickname)
		rv = append(rv, &v)
	}
	return rv, count
}

// GetContestUserRole 查询用户角色
func (r *contestRepo) GetContestUserRole(ctx context.Context, cid int, uid int) int {
	var res int
	r.data.db.WithContext(ctx).
		Select("role").
		Model(&ContestUser{}).
		Where("contest_id = ? and user_id = ?", cid, uid).
		Limit(1).
		Scan(&res)
	return res
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

func (r *contestRepo) UpdateContestUser(ctx context.Context, c *biz.ContestUser) (*biz.ContestUser, error) {
	res := ContestUser{
		ID:        c.ID,
		ContestID: c.ContestID,
		UserID:    c.UserID,
		Name:      c.Name,
		Role:      c.Role,
	}
	err := r.data.db.WithContext(ctx).
		Select("Name", "Role").
		Omit(clause.Associations).
		Where("contest_id = ? and user_id = ?", c.ContestID, c.UserID).
		Updates(&res).
		Error
	return &biz.ContestUser{
		ID:   res.ID,
		Name: res.Name,
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
