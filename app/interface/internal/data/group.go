package data

import (
	"context"
	"errors"
	"fmt"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type groupRepo struct {
	data *Data
	log  *log.Helper
}

type Group struct {
	ID             int
	ParentID       int
	Name           string
	Description    string
	Privacy        int    // 隐私设置
	Membership     int    // 加入资格
	InvitationCode string // 邀请码
	Type           int    // 类型：小组、团队
	MemberCount    int
	UserID         int
	CreatedAt      time.Time

	Team     *Group     `gorm:"foreignKey:ParentID"`
	User     *User      `json:"user" gorm:"foreignKey:UserID"`
	Contests []*Contest `gorm:"GroupID"`
}

// GroupUser .
type GroupUser struct {
	ID        int
	GroupID   int
	UserID    int
	Role      int
	Nickname  string
	CreatedAt time.Time
	User      *User `json:"user" gorm:"foreignKey:UserID"`
}

// NewGroupRepo .
func NewGroupRepo(data *Data, logger log.Logger) biz.GroupRepo {
	return &groupRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListGroups .
func (r *groupRepo) ListGroups(ctx context.Context, req *v1.ListGroupsRequest) ([]*biz.Group, int64) {
	res := []Group{}
	count := int64(0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&Group{}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname, avatar")
		})
	if req.Name != "" {
		db.Where("name like ?", fmt.Sprintf("%%%s%%", req.Name))
	}
	uid, _ := auth.GetUserID(ctx)
	if req.Mygroup != nil && *req.Mygroup && uid != 0 {
		db.Joins("INNER JOIN group_user on group_user.group_id=group.ID AND group_user.user_id=?", uid)
		if req.Sort != "" {
			if req.Sort == "joinedAt" {
				db.Order("group_user.created_at desc")
			} else {
				db.Order("group.created_at desc")
			}
		}
	}
	if req.ParentId != nil {
		db.Where("parent_id = ?", *req.ParentId)
	}
	db.Where("type = ?", int(req.Type))
	db.Count(&count)
	db.Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&res)
	rv := make([]*biz.Group, 0)
	for _, v := range res {
		g := &biz.Group{
			ID:          v.ID,
			Name:        v.Name,
			Membership:  v.Membership,
			Privacy:     v.Privacy,
			Description: v.Description,
			MemberCount: v.MemberCount,
			UserID:      v.UserID,
		}
		if v.User != nil {
			g.UserNickname = v.User.Nickname
			g.UserAvatar = v.User.Avatar
		}
		rv = append(rv, g)
	}
	return rv, count
}

// GetGroup .
func (r *groupRepo) GetGroup(ctx context.Context, id int) (*biz.Group, error) {
	var res Group
	err := r.data.db.Model(&Group{}).
		First(&res, "id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	if res.ParentID != 0 {
		r.data.db.Model(&Group{}).First(&res.Team, "id = ?", res.ParentID)
	}
	g := &biz.Group{
		ID:             res.ID,
		Name:           res.Name,
		Description:    res.Description,
		Membership:     res.Membership,
		Privacy:        res.Privacy,
		InvitationCode: res.InvitationCode,
		MemberCount:    res.MemberCount,
		UserID:         res.UserID,
		Type:           res.Type,
		CreatedAt:      res.CreatedAt,
	}
	if res.Team != nil {
		g.Team = &biz.Group{
			ID:   res.Team.ID,
			Name: res.Team.Name,
		}
	}
	return g, err
}

// CreateGroup .
func (r *groupRepo) CreateGroup(ctx context.Context, g *biz.Group) (*biz.Group, error) {
	res := Group{
		Name:           g.Name,
		Description:    g.Description,
		UserID:         g.UserID,
		ParentID:       g.ParentID,
		Type:           g.Type,
		Privacy:        g.Privacy,
		InvitationCode: g.InvitationCode,
		Membership:     g.Membership,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Group{
		ID: res.ID,
	}, err
}

// UpdateGroup .
func (r *groupRepo) UpdateGroup(ctx context.Context, g *biz.Group) (*biz.Group, error) {
	res := Group{
		ID:             g.ID,
		Name:           g.Name,
		Privacy:        g.Privacy,
		Membership:     g.Membership,
		Description:    g.Description,
		InvitationCode: g.InvitationCode,
	}
	err := r.data.db.WithContext(ctx).
		Select("ID", "Name", "Privacy", "Membership", "Description", "InvitationCode").
		Omit(clause.Associations).
		Updates(&res).Error
	return &biz.Group{
		ID:          res.ID,
		Name:        res.Name,
		Description: res.Description,
	}, err
}

// DeleteGroup .
func (r *groupRepo) DeleteGroup(ctx context.Context, id int) error {
	tx := r.data.db.WithContext(ctx).Begin()
	err := tx.Omit(clause.Associations).
		Unscoped().
		Delete(&Group{ID: id}).
		Error
	if err != nil {
		tx.Rollback()
		return err
	}
	// 删除小组用户关系
	err = tx.Omit(clause.Associations).
		Unscoped().
		Delete(&GroupUser{}, "group_id = ?", id).
		Error
	if err != nil {
		tx.Rollback()
		return err
	}
	// 查询比赛ID
	contestIds := tx.Select("id").Model(&Contest{}).Where("group_id = ?", id)
	// 删除小组比赛的提交
	err = tx.Omit(clause.Associations).
		Unscoped().
		Where("entity_type = ? and entity_id in (?)", biz.SubmissionEntityTypeContest, contestIds).
		Delete(&Submission{}).Error
	if err != nil {
		tx.Rollback()
		return err
	}
	// 删除比赛用户
	err = tx.Omit(clause.Associations).
		Unscoped().
		Where("contest_id in (?)", contestIds).
		Delete(&ContestUser{}).Error
	if err != nil {
		tx.Rollback()
		return err
	}
	// 删除比赛
	err = tx.Omit(clause.Associations).
		Unscoped().
		Where("group_id = ?", id).
		Delete(&Contest{}).Error
	if err != nil {
		tx.Rollback()
		return err
	}
	tx.Commit()
	return nil
}

// ListGroupUsers .
func (r *groupRepo) ListGroupUsers(ctx context.Context, req *v1.ListGroupUsersRequest) ([]*biz.GroupUser, int64) {
	res := []GroupUser{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname, avatar")
		}).
		Where("group_id = ?", req.Id).
		Order("role, id").
		Find(&res).
		Count(&count)
	rv := make([]*biz.GroupUser, 0)
	for _, v := range res {
		u := &biz.GroupUser{
			ID:         v.ID,
			Nickname:   v.Nickname,
			UserAvatar: v.User.Avatar,
			UserID:     v.UserID,
			GroupID:    v.GroupID,
			Role:       v.Role,
			CreatedAt:  v.CreatedAt,
		}
		if u.Nickname == "" {
			u.Nickname = v.User.Nickname
		}
		rv = append(rv, u)
	}
	return rv, count
}

// GetGroupUser .
func (r *groupRepo) GetGroupUser(ctx context.Context, group *biz.Group, uid int) (*biz.GroupUser, error) {
	var res GroupUser
	err := r.data.db.WithContext(ctx).
		Model(&GroupUser{}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		}).
		First(&res, "group_id = ? and user_id = ?", group.ID, uid).
		Error
	// 小组没找到，往小组所属团队查找角色
	if errors.Is(err, gorm.ErrRecordNotFound) {
		if group.Team != nil {
			return r.GetGroupUser(ctx, group.Team, uid)
		}
		return nil, err
	}
	u := &biz.GroupUser{
		ID:        res.ID,
		UserID:    res.UserID,
		GroupID:   res.GroupID,
		Role:      res.Role,
		Nickname:  res.Nickname,
		CreatedAt: res.CreatedAt,
	}
	if u.Nickname == "" {
		u.Nickname = res.User.Nickname
	}
	return u, err
}

// CreateGroupUser .
func (r *groupRepo) CreateGroupUser(ctx context.Context, g *biz.GroupUser) (*biz.GroupUser, error) {
	res := GroupUser{
		UserID:   g.UserID,
		GroupID:  g.GroupID,
		Role:     g.Role,
		Nickname: g.Nickname,
	}
	err := r.data.db.WithContext(ctx).
		Where("user_id = ? and group_id = ?", g.UserID, g.GroupID).
		First(&GroupUser{}).Error
	if err == nil {
		return nil, errors.New("already exists")
	}
	err = r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		FirstOrCreate(&res, map[string]interface{}{
			"user_id":  g.UserID,
			"group_id": g.GroupID,
			"role":     g.Role,
		}).
		Error
	if err != nil {
		return nil, err
	}
	r.updateGroupMember(ctx, g.GroupID)
	return &biz.GroupUser{
		ID: res.ID,
	}, err
}

// UpdateGroupUser .
func (r *groupRepo) UpdateGroupUser(ctx context.Context, g *biz.GroupUser) (*biz.GroupUser, error) {
	res := GroupUser{
		UserID:   g.UserID,
		GroupID:  g.GroupID,
		Role:     g.Role,
		Nickname: g.Nickname,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Where("group_id = ? and user_id = ?", g.GroupID, g.UserID).
		Updates(&res).
		Error
	return &biz.GroupUser{
		ID: res.ID,
	}, err
}

// DeleteGroupUser .
func (r *groupRepo) DeleteGroupUser(ctx context.Context, groupID, userID int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(GroupUser{}, "group_id = ? and user_id = ?", groupID, userID).
		Error
	r.updateGroupMember(ctx, groupID)
	return err
}

// updateGroupMember 更新成员数
func (r *groupRepo) updateGroupMember(ctx context.Context, groupID int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Model(&Group{ID: groupID}).
		UpdateColumn("member_count",
			r.data.db.WithContext(ctx).
				Select("count(*)").
				Model(&GroupUser{}).
				Where("group_id = ?", groupID).
				Where("role in (?)", []int{biz.GroupUserRoleAdmin, biz.GroupUserRoleManager, biz.GroupUserRoleMember})).
		Error
	return err
}
