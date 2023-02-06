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
	Status           int
	Description      string
	GroupID          int
	UserID           int
	ParticipantCount int
	CreatedAt        time.Time
	UpdatedAt        time.Time
	DeletedAt        gorm.DeletedAt
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
		Model(&Contest{})
	if req.Name != "" {
		db.Where("name like ?", fmt.Sprintf("%%%s%%", req.Name))
	}
	if req.GroupId != 0 {
		db.Where("group_id = ?", req.GroupId)
	}
	db.Count(&count).
		Order("id desc")
	db.Offset(pager.GetOffset()).
		Limit(pager.GetPageSize()).
		Find(&res)
	rv := make([]*biz.Contest, 0)
	for _, v := range res {
		rv = append(rv, &biz.Contest{
			ID:               v.ID,
			Name:             v.Name,
			StartTime:        v.StartTime,
			EndTime:          v.EndTime,
			ParticipantCount: v.ParticipantCount,
			Type:             v.Type,
			GroupId:          v.GroupID,
		})
	}
	return rv, count
}

// GetContest .
func (r *contestRepo) GetContest(ctx context.Context, id int) (*biz.Contest, error) {
	var c Contest
	err := r.data.db.Model(Contest{}).
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
		Status:           c.Status,
		Description:      c.Description,
		ParticipantCount: c.ParticipantCount,
		UserID:           c.UserID,
		GroupId:          c.GroupID,
		CreatedAt:        c.CreatedAt,
	}
	if uid, ok := auth.GetUserID(ctx); ok {
		res.IsRegistered = r.ExistContestUser(ctx, c.ID, uid)
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
		ID:          c.ID,
		Name:        c.Name,
		StartTime:   c.StartTime,
		EndTime:     c.EndTime,
		FrozenTime:  c.FrozenTime,
		Type:        c.Type,
		Description: c.Description,
		Status:      c.Status,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Select("Name", "StartTime", "EndTime", "FrozenTime", "Type", "Description", "Status").
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

func (r *contestRepo) ListContestStandings(ctx context.Context, id int) (res []*biz.ContestSubmission) {
	var submissions []Submission
	r.data.db.WithContext(ctx).
		Select("id, problem_id, user_id, verdict, score, created_at").
		Where("entity_id = ? and entity_type = ?", id, biz.SubmissionEntityTypeContest).
		Find(&submissions)
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

func (r *contestRepo) AddContestParticipantCount(ctx context.Context, id int, count int) error {
	return r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Model(&Contest{ID: id}).
		UpdateColumn("participant_count", gorm.Expr("participant_count + ?", count)).
		Error
}
