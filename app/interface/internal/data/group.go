package data

import (
	"context"
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
	ID          int
	Name        string
	Description string
	UserID      int
	MemberCount int
	CreatedAt   time.Time
}

// GroupUser .
type GroupUser struct {
	ID        int
	GroupID   int
	UserID    int
	Role      int
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
		Model(&Group{})
	if req.Name != "" {
		db.Where("name like ?", fmt.Sprintf("%%%s%%", req.Name))
	}
	uid, ok := auth.GetUserID(ctx)
	if req.Mygroup != nil && *req.Mygroup && ok {
		db.Joins("RIGHT JOIN GroupUser on GroupUser.GroupID=Group.ID AND GroupUser.UserID=?", uid)
	}
	db.Count(&count)
	db.Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&res)
	rv := make([]*biz.Group, 0)
	for _, v := range res {
		rv = append(rv, &biz.Group{
			ID:          v.ID,
			Name:        v.Name,
			Description: v.Description,
			MemberCount: v.MemberCount,
		})
	}
	return rv, count
}

// GetGroup .
func (r *groupRepo) GetGroup(ctx context.Context, id int) (*biz.Group, error) {
	var res Group
	err := r.data.db.Model(Group{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.Group{
		ID:          res.ID,
		Name:        res.Name,
		Description: res.Description,
		UserID:      res.UserID,
		MemberCount: res.MemberCount,
		CreatedAt:   res.CreatedAt,
	}, err
}

// CreateGroup .
func (r *groupRepo) CreateGroup(ctx context.Context, g *biz.Group) (*biz.Group, error) {
	res := Group{
		Name:        g.Name,
		Description: g.Description,
		UserID:      g.UserID,
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
		ID:          g.ID,
		Name:        g.Name,
		Description: g.Description,
	}
	err := r.data.db.WithContext(ctx).
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
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(Group{ID: id}).
		Error
	return err
}

// ListGroupUsers .
func (r *groupRepo) ListGroupUsers(ctx context.Context, req *v1.ListGroupUsersRequest) ([]*biz.GroupUser, int64) {
	res := []GroupUser{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		}).
		Where("group_id = ?", req.Id).
		Find(&res).
		Count(&count)
	rv := make([]*biz.GroupUser, 0)
	for _, v := range res {
		rv = append(rv, &biz.GroupUser{
			ID:        v.ID,
			Nickname:  v.User.Nickname,
			UserID:    v.UserID,
			GroupID:   v.GroupID,
			Role:      v.Role,
			CreatedAt: v.CreatedAt,
		})
	}
	return rv, count
}

// GetGroupUser .
func (r *groupRepo) GetGroupUser(ctx context.Context, gid int, uid int) (*biz.GroupUser, error) {
	var res GroupUser
	err := r.data.db.WithContext(ctx).
		Model(&GroupUser{}).
		First(&res, "group_id = ? and user_id = ?", gid, uid).
		Error
	return &biz.GroupUser{
		ID:        res.ID,
		UserID:    res.UserID,
		GroupID:   res.GroupID,
		Role:      res.Role,
		CreatedAt: res.CreatedAt,
	}, err
}

// CreateGroupUser .
func (r *groupRepo) CreateGroupUser(ctx context.Context, g *biz.GroupUser) (*biz.GroupUser, error) {
	res := GroupUser{
		UserID:  g.UserID,
		GroupID: g.GroupID,
		Role:    g.Role,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		FirstOrCreate(&res, map[string]interface{}{
			"user_id":  g.UserID,
			"group_id": g.GroupID,
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
		UserID:  g.UserID,
		GroupID: g.GroupID,
		Role:    g.Role,
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
