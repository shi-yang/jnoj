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
	CreatedAt time.Time
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
	return nil, err
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
		Find(&res).
		Count(&count)
	rv := make([]*biz.GroupUser, 0)
	for _, v := range res {
		rv = append(rv, &biz.GroupUser{
			ID: v.ID,
		})
	}
	return rv, count
}

// CreateGroupUser .
func (r *groupRepo) CreateGroupUser(ctx context.Context, g *biz.GroupUser) (*biz.GroupUser, error) {
	res := GroupUser{
		UserID:  g.UserID,
		GroupID: g.GroupID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
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
	return err
}
