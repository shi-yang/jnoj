package data

import (
	"context"
	"encoding/json"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type submissionRepo struct {
	data *Data
	log  *log.Helper
}

type Submission struct {
	ID         int
	ProblemID  int
	Time       int
	Memory     int
	Verdict    int
	Language   int
	Score      int
	UserID     int
	EntityID   int
	EntityType int
	Source     string
	CreatedAt  time.Time
	User       *User    `json:"user" gorm:"foreignKey:UserID"`
	Problem    *Problem `json:"problem" gorm:"foreignKey:ProblemID"`
}

type SubmissionInfo struct {
	SubmissionID int
	RunInfo      string
}

// NewSubmissionRepo .
func NewSubmissionRepo(data *Data, logger log.Logger) biz.SubmissionRepo {
	return &submissionRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListSubmissions .
func (r *submissionRepo) ListSubmissions(ctx context.Context, req *v1.ListSubmissionsRequest) ([]*biz.Submission, int64) {
	res := []Submission{}
	count := int64(0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&Submission{}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		}).
		Preload("Problem.ProblemStatements", func(db *gorm.DB) *gorm.DB {
			return db.Select("problem_id, name")
		})
	if req.ProblemId != nil {
		db.Where("problem_id = ?", *req.ProblemId)
	}
	if req.UserId != 0 {
		db.Where("user_id = ?", req.UserId)
	}
	if len(req.Verdict) > 0 {
		db.Where("verdict in (?)", req.Verdict)
	}
	if req.EntityId != 0 {
		db.Where("entity_id = ?", req.EntityId)
	}
	db.Where("entity_type = ?", req.EntityType)
	db.Count(&count)
	db.
		Limit(page.GetPageSize()).
		Offset(page.GetOffset()).
		Order("id desc")
	db.Find(&res)
	rv := make([]*biz.Submission, 0)
	for _, v := range res {
		s := &biz.Submission{
			ID:         v.ID,
			EntityID:   v.EntityID,
			EntityType: v.EntityType,
			ProblemID:  v.ProblemID,
			Verdict:    v.Verdict,
			Memory:     v.Memory,
			Time:       v.Time,
			Language:   v.Language,
			Score:      v.Score,
			CreatedAt:  v.CreatedAt,
			User: biz.User{
				ID:       v.User.ID,
				Nickname: v.User.Nickname,
			},
		}
		if len(v.Problem.ProblemStatements) > 0 {
			s.ProblemName = v.Problem.ProblemStatements[0].Name
		}
		rv = append(rv, s)
	}
	return rv, count
}

// GetSubmission .
func (r *submissionRepo) GetSubmission(ctx context.Context, id int) (*biz.Submission, error) {
	var res Submission
	err := r.data.db.Model(Submission{}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.Submission{
		ID:         res.ID,
		Score:      res.Score,
		Source:     res.Source,
		Memory:     res.Memory,
		Time:       res.Time,
		Verdict:    res.Verdict,
		Language:   res.Language,
		ProblemID:  res.ProblemID,
		EntityID:   res.EntityID,
		EntityType: res.EntityType,
		UserID:     res.UserID,
		CreatedAt:  res.CreatedAt,
		Nickname:   res.User.Nickname,
	}, err
}

// CreateSubmission .
func (r *submissionRepo) CreateSubmission(ctx context.Context, s *biz.Submission) (*biz.Submission, error) {
	res := Submission{
		Source:     s.Source,
		UserID:     s.UserID,
		Verdict:    s.Verdict,
		Language:   s.Language,
		ProblemID:  s.ProblemID,
		EntityID:   s.EntityID,
		EntityType: s.EntityType,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Submission{
		ID:        res.ID,
		CreatedAt: res.CreatedAt,
	}, err
}

// UpdateSubmission .
func (r *submissionRepo) UpdateSubmission(ctx context.Context, s *biz.Submission) (*biz.Submission, error) {
	res := Submission{
		ID:      s.ID,
		Memory:  s.Memory,
		Time:    s.Time,
		Verdict: s.Verdict,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteSubmission .
func (r *submissionRepo) DeleteSubmission(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(Submission{ID: id}).
		Error
	return err
}

// CreateSubmissionInfo .
func (r *submissionRepo) CreateSubmissionInfo(ctx context.Context, id int, runInfo string) error {
	res := SubmissionInfo{
		SubmissionID: id,
		RunInfo:      runInfo,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return err
}

// GetSubmissionInfo .
func (r *submissionRepo) GetSubmissionInfo(ctx context.Context, id int) (*biz.SubmissionResult, error) {
	res := new(biz.SubmissionResult)
	info := SubmissionInfo{}
	err := r.data.db.WithContext(ctx).First(&info, "submission_id = ?", id).Error
	if err != nil {
		return nil, err
	}
	err = json.Unmarshal([]byte(info.RunInfo), &res)
	if err != nil {
		return nil, err
	}
	return res, nil
}

// GetLastSubmission 获取最后提交
func (r *submissionRepo) GetLastSubmission(ctx context.Context, entityType, entityID, userId, problemId int) (*biz.Submission, error) {
	var res Submission
	db := r.data.db.WithContext(ctx).
		Where("user_id = ?", userId)
	if entityType == biz.SubmissionEntityTypeContest {
		subQuery := r.data.db.WithContext(ctx).Select("problem_id").
			Model(&ContestProblem{}).
			Where("contest_id = ? and number = ?", entityID, problemId)
		db.Where("problem_id in (?)", subQuery)
	} else {
		db.Where("problem_id = ?", problemId)
	}

	err := db.Where("entity_type = ?", entityType).
		Where("entity_id = ?", entityID).
		Last(&res).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.Submission{
		ID:         res.ID,
		Source:     res.Source,
		Memory:     res.Memory,
		Time:       res.Time,
		Verdict:    res.Verdict,
		Language:   res.Language,
		ProblemID:  res.ProblemID,
		EntityID:   res.EntityID,
		EntityType: res.EntityType,
		UserID:     res.UserID,
		CreatedAt:  res.CreatedAt,
	}, err
}
