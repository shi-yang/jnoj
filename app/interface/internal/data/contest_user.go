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
	VirtualEnd   *time.Time // 虚拟竞赛结束时间
	OldRating    int        // 上场比赛竞赛积分
	NewRating    int        // 本场比赛竞赛积分
	RatedAt      *time.Time // 竞赛积分计算时间
	CreatedAt    time.Time
	User         *User `json:"user" gorm:"foreignKey:UserID"`
}

// ListContestUsers .
func (r *contestRepo) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) ([]*biz.ContestUser, int64) {
	count := int64(0)
	db := r.data.db.WithContext(ctx).
		Select("c.id, c.user_id, c.name, c.role, c.virtual_start, c.virtual_end, c.old_rating, c.new_rating, c.rated_at, user.nickname").
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
	// 查小组备注
	var groupId int
	groupNickname := make(map[int]string)
	r.data.db.WithContext(ctx).Select("group_id").Model(&Contest{ID: int(req.ContestId)}).Scan(&groupId)
	if groupId != 0 {
		var groupUsers []struct {
			UserId   int
			Nickname string
		}
		r.data.db.WithContext(ctx).Select("user_id, nickname").Model(&GroupUser{}).
			Where("group_id = ?", groupId).
			Where("nickname != ''").
			Scan(&groupUsers)
		for _, v := range groupUsers {
			groupNickname[v.UserId] = v.Nickname
		}
	}
	rv := make([]*biz.ContestUser, 0)
	for rows.Next() {
		var v biz.ContestUser
		var virtualStart sql.NullTime
		var virtualEnd sql.NullTime
		var ratedAt sql.NullTime
		rows.Scan(&v.ID, &v.UserID, &v.Name, &v.Role, &virtualStart,
			&virtualEnd, &v.OldRating, &v.NewRating, &ratedAt, &v.UserNickname)
		v.RatingChanged = v.NewRating - v.OldRating
		if virtualStart.Valid {
			v.VirtualStart = &virtualStart.Time
		}
		if virtualEnd.Valid {
			v.VirtualEnd = &virtualEnd.Time
		}
		if ratedAt.Valid {
			v.RatedAt = &ratedAt.Time
		}
		if name, ok := groupNickname[v.UserID]; ok {
			v.Name = name
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
		ID:            res.ID,
		ContestID:     res.ContestID,
		UserID:        res.UserID,
		Name:          res.Name,
		Role:          res.Role,
		VirtualStart:  res.VirtualStart,
		VirtualEnd:    res.VirtualEnd,
		UserNickname:  res.Name,
		OldRating:     res.OldRating,
		NewRating:     res.NewRating,
		RatingChanged: res.NewRating - res.OldRating,
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
		ID:         c.ID,
		ContestID:  c.ContestID,
		UserID:     c.UserID,
		Name:       c.Name,
		Role:       c.Role,
		VirtualEnd: c.VirtualEnd,
	}
	updateColumns := []string{"name", "role"}
	if c.VirtualEnd != nil {
		updateColumns = append(updateColumns, "virtual_end")
	}
	err := r.data.db.WithContext(ctx).
		Select(updateColumns).
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

// GetContestUserRating 查询用户积分
func (r *contestRepo) GetContestUserRating(ctx context.Context, uid int) int {
	var user ContestUser
	err := r.data.db.WithContext(ctx).
		Select("new_rating").
		Model(&ContestUser{}).
		Where("user_id = ?", uid).
		Where("rated_at is not null").
		Order("rated_at desc").
		First(&user).Error
	if errors.Is(err, gorm.ErrRecordNotFound) {
		return -1
	}
	return user.NewRating
}

// SaveContestRating .
func (r *contestRepo) SaveContestRating(ctx context.Context, users []*biz.ContestUser) error {
	tx := r.data.db.WithContext(ctx)
	for _, user := range users {
		err := r.data.db.WithContext(ctx).
			Omit(clause.Associations).
			Select("OldRating", "NewRating", "RatedAt").
			Where("contest_id = ? and user_id = ?", user.ContestID, user.UserID).
			Updates(&user).
			Error
		if err != nil {
			tx.Rollback()
			return err
		}
	}
	tx.Commit()
	return nil
}
