package data

import (
	"context"
	"fmt"
	"strings"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"google.golang.org/protobuf/types/known/timestamppb"
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
	Feature          string // 特性：rated
	CreatedAt        time.Time
	UpdatedAt        time.Time
	DeletedAt        gorm.DeletedAt
	Group            *Group            `json:"group" gorm:"foreignKey:GroupID"`
	User             *User             `json:"user" gorm:"foreignKey:UserID"`
	ContestProblems  []*ContestProblem `gorm:"ForeignKey:ContestID"`
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
	if req.RunningStatus != nil {
		if *req.RunningStatus == v1.RunningStatus_FINISHED {
			db.Where("end_time < ?", time.Now())
		}
	}
	if req.EndTime != nil {
		db.Where("end_time > ?", req.EndTime.AsTime())
	}
	db.Count(&count)
	if req.OrderBy != nil {
		if strings.Contains(*req.OrderBy, "start_time") {
			db.Order("start_time desc")
		}
	} else {
		db.Order("id desc")
	}
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
			Feature:          v.Feature,
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
		Feature:          c.Feature,
		GroupId:          c.GroupID,
		CreatedAt:        c.CreatedAt,
	}
	if c.Group != nil {
		res.OwnerName = c.Group.Name
	} else if c.User != nil {
		res.OwnerName = c.User.Nickname
	}
	// 查询登录用户的角色
	if uid, role := auth.GetUserID(ctx); uid != 0 {
		contestUser := r.GetContestUser(ctx, c.ID, uid)
		if contestUser != nil {
			res.Role = contestUser.Role
			res.VirtualStart = contestUser.VirtualStart
			res.VirtualEnd = contestUser.VirtualEnd
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
		if uid == c.UserID || biz.CheckAccess(role, biz.ResourceContest) {
			res.Role = biz.ContestRoleAdmin
		}
	}
	return res, nil
}

// CreateContest .
func (r *contestRepo) CreateContest(ctx context.Context, c *biz.Contest) (*biz.Contest, error) {
	res := Contest{
		Name:       c.Name,
		StartTime:  c.StartTime,
		EndTime:    c.EndTime,
		UserID:     c.UserID,
		Type:       c.Type,
		GroupID:    c.GroupId,
		Membership: c.Membership,
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
		Feature:        c.Feature,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Select("Name", "StartTime", "EndTime", "FrozenTime", "Type", "Description", "Privacy", "Membership", "InvitationCode", "Feature").
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

func (r *contestRepo) ListContestAllSubmissions(ctx context.Context, contest *biz.Contest) (res []*biz.ContestSubmission) {
	var submissions []Submission
	db := r.data.db.WithContext(ctx).
		Select("id, problem_id, user_id, verdict, score, created_at").
		Where("entity_id = ? and entity_type = ?", contest.ID, biz.SubmissionEntityTypeContest)
	db.Find(&submissions)
	var problems []ContestProblem
	r.data.db.WithContext(ctx).
		Select("problem_id, number").
		Where("contest_id = ?", contest.ID).
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

// CronUpdateContestUserStanding 定期更新比赛用户表
// 通过定期查询的方式更新 ContestUser.Rank 字段
func (uc *contestRepo) CronUpdateContestUserStanding(ctx context.Context) {
}

// ListContestStandingStats 获取比赛排名统计列表
func (r *contestRepo) ListContestStandingStats(ctx context.Context, req *v1.ListContestStandingStatsRequest) *v1.ListContestStandingStatsResponse {
	var users []*ContestUser
	db := r.data.db.WithContext(ctx).
		Model(&users)
	db.Where("user_id in (?)", req.UserId)
	if req.GroupId != 0 {
		db.Where("group_id = ?", req.GroupId)
	}
	if len(req.ContestIds) != 0 {
		db.Where("contest_id in (?)", req.ContestIds)
	}
	db.Find(&users)
	var resp = new(v1.ListContestStandingStatsResponse)
	for _, v := range users {
		resp.Data = append(resp.Data, &v1.ListContestStandingStatsResponse_ContestStanding{
			ContestId:   int32(v.ContestID),
			ContestName: v.Name,
			StartTime:   timestamppb.New(v.CreatedAt),
			Rank:        int32(v.Rank),
			Score:       int32(v.Score),
		})
	}
	return nil
}
