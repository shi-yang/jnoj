package data

import (
	"context"
	"database/sql"
	"errors"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type ContestUser struct {
	ID           int
	ContestID    int
	UserID       int
	Name         string
	Role         int
	VirtualStart *time.Time // 虚拟竞赛开始时间
	CreatedAt    time.Time
	User         *User `json:"user" gorm:"foreignKey:UserID"`
}

// ListContestUsers .
func (r *contestRepo) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*biz.ContestUser, int64) {
	count := int64(0)
	db := r.data.db.WithContext(ctx).
		Select("c.id, c.user_id, c.name, c.role, c.virtual_start, user.nickname").
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
		var virtualStart sql.NullTime
		rows.Scan(&v.ID, &v.UserID, &v.Name, &v.Role, &virtualStart, &v.UserNickname)
		if virtualStart.Valid {
			v.VirtualStart = &virtualStart.Time
		}
		if v.Name == "" {
			v.Name = v.UserNickname
		}
		rv = append(rv, &v)
	}
	return rv, count
}

// GetContestUser 查询比赛用户信息
func (r *contestRepo) GetContestUser(ctx context.Context, cid int, uid int) *biz.ContestUser {
	var res ContestUser
	err := r.data.db.WithContext(ctx).
		Model(&ContestUser{}).
		Where("contest_id = ? and user_id = ?", cid, uid).
		First(&res).
		Error
	if errors.Is(err, gorm.ErrRecordNotFound) {
		return nil
	}
	return &biz.ContestUser{
		ID:           res.ID,
		Name:         res.Name,
		Role:         res.Role,
		VirtualStart: res.VirtualStart,
		UserNickname: res.Name,
	}
}

// CreateContestUser .
func (r *contestRepo) CreateContestUser(ctx context.Context, b *biz.ContestUser) (*biz.ContestUser, error) {
	res := ContestUser{
		Name:         b.Name,
		UserID:       b.UserID,
		ContestID:    b.ContestID,
		Role:         b.Role,
		VirtualStart: b.VirtualStart,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Model(&Contest{ID: b.ContestID}).
		UpdateColumn("participant_count", gorm.Expr("participant_count + ?", 1))
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
