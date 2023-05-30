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

type contestRepo struct {
	data *Data
	log  *log.Helper
}

type Contest struct {
	ID               int
	Name             string
	StartTime        time.Time
	EndTime          time.Time
	FrozenTime       *time.Time
	Type             int
	Privacy          int    // 隐私设置，私有、公开
	Membership       int    // 参赛资格
	InvitationCode   string // 邀请码
	Description      string
	GroupID          int
	UserID           int
	ParticipantCount int
	CreatedAt        time.Time
	UpdatedAt        time.Time
	DeletedAt        gorm.DeletedAt
	Group            *Group `json:"group" gorm:"foreignKey:GroupID"`
	User             *User  `json:"user" gorm:"foreignKey:UserID"`
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
	pager := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&Contest{}).
		Preload("Group", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, name")
		}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		})
	if req.Name != "" {
		db.Where("name like ?", fmt.Sprintf("%%%s%%", req.Name))
	}
	if req.GroupId != nil {
		db.Where("group_id = ?", *req.GroupId)
	}
	db.Count(&count).
		Order("id desc")
	db.Offset(pager.GetOffset()).
		Limit(pager.GetPageSize()).
		Find(&res)
	rv := make([]*biz.Contest, 0)
	for _, v := range res {
		c := &biz.Contest{
			ID:               v.ID,
			Name:             v.Name,
			StartTime:        v.StartTime,
			EndTime:          v.EndTime,
			ParticipantCount: v.ParticipantCount,
			Type:             v.Type,
			GroupId:          v.GroupID,
			UserID:           v.UserID,
			Membership:       v.Membership,
			Privacy:          v.Privacy,
			UserNickname:     v.User.Nickname,
			OwnerName:        v.User.Nickname,
		}
		if v.Group != nil {
			c.OwnerName = v.Group.Name
		}
		rv = append(rv, c)
	}
	return rv, count
}

// GetContest .
func (r *contestRepo) GetContest(ctx context.Context, id int) (*biz.Contest, error) {
	var c Contest
	err := r.data.db.Model(Contest{}).
		Preload("Group", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, name")
		}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		}).
		First(&c, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	res := &biz.Contest{
		ID:               c.ID,
		Name:             c.Name,
		StartTime:        c.StartTime,
		EndTime:          c.EndTime,
		FrozenTime:       c.FrozenTime,
		Type:             c.Type,
		Privacy:          c.Privacy,
		Membership:       c.Membership,
		InvitationCode:   c.InvitationCode,
		Description:      c.Description,
		ParticipantCount: c.ParticipantCount,
		UserID:           c.UserID,
		GroupId:          c.GroupID,
		CreatedAt:        c.CreatedAt,
	}
	if c.Group != nil {
		res.OwnerName = c.Group.Name
	} else if c.User != nil {
		res.OwnerName = c.User.Nickname
	}
	// 查询登录用户的角色
	if uid, ok := auth.GetUserID(ctx); ok {
		contestUser := r.GetContestUser(ctx, c.ID, uid)
		if contestUser != nil {
			res.Role = contestUser.Role
			res.VirtualStart = contestUser.VirtualStart
		}
		// 若比赛属于小组，且登录用户为小组管理，赋予管理权限
		if c.GroupID != 0 {
			var gu GroupUser
			err = r.data.db.WithContext(ctx).
				Model(&GroupUser{}).
				Where("group_id = ? and user_id = ?", c.GroupID, uid).
				First(&gu).
				Error
			if err == nil && (gu.Role == biz.GroupUserRoleAdmin || gu.Role == biz.GroupUserRoleManager) {
				res.Role = biz.ContestRoleAdmin
			}
		}
		if uid == c.UserID {
			res.Role = biz.ContestRoleAdmin
		}
	}
	return res, err
}

// CreateContest .
func (r *contestRepo) CreateContest(ctx context.Context, c *biz.Contest) (*biz.Contest, error) {
	res := Contest{
		Name:      c.Name,
		StartTime: c.StartTime,
		EndTime:   c.EndTime,
		UserID:    c.UserID,
		Type:      c.Type,
		GroupID:   c.GroupId,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Contest{
		ID: res.ID,
	}, err
}

// UpdateContest .
func (r *contestRepo) UpdateContest(ctx context.Context, c *biz.Contest) (*biz.Contest, error) {
	res := Contest{
		ID:             c.ID,
		Name:           c.Name,
		StartTime:      c.StartTime,
		EndTime:        c.EndTime,
		FrozenTime:     c.FrozenTime,
		Type:           c.Type,
		Description:    c.Description,
		Privacy:        c.Privacy,
		Membership:     c.Membership,
		InvitationCode: c.InvitationCode,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Select("Name", "StartTime", "EndTime", "FrozenTime", "Type", "Description", "Privacy", "Membership", "InvitationCode").
		Updates(&res).Error
	return &biz.Contest{
		ID: res.ID,
	}, err
}

// DeleteContest .
func (r *contestRepo) DeleteContest(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(Contest{ID: id}).
		Error
	return err
}

func (r *contestRepo) ListContestAllSubmissions(ctx context.Context, id int) (res []*biz.ContestSubmission) {
	var submissions []Submission
	db := r.data.db.WithContext(ctx).
		Select("id, problem_id, user_id, verdict, score, created_at").
		Where("entity_id = ? and entity_type = ?", id, biz.SubmissionEntityTypeContest)
	db.Find(&submissions)
	var problems []ContestProblem
	r.data.db.WithContext(ctx).
		Select("problem_id, number").
		Where("contest_id = ?", id).
		Find(&problems)
	var problemMap = make(map[int]int)
	for _, v := range problems {
		problemMap[v.ProblemID] = v.Number
	}
	for _, v := range submissions {
		res = append(res, &biz.ContestSubmission{
			ID:            v.ID,
			ProblemNumber: problemMap[v.ProblemID],
			Verdict:       v.Verdict,
			UserID:        v.UserID,
			Score:         v.Score,
			CreatedAt:     v.CreatedAt,
		})
	}
	return
}
