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
	ID        int
	ProblemID int
	ContestID int
	Time      int
	Memory    int
	Verdict   int
	Language  int
	Score     int
	UserID    int
	Source    string
	CreatedAt time.Time
	User      *User    `json:"user" gorm:"foreignKey:UserID"`
	Problem   *Problem `json:"problem" gorm:"foreignKey:ProblemID"`
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
	if req.ProblemId != 0 {
		db.Where("problem_id = ?", req.ProblemId)
	}
	if req.ContestId != 0 {
		db.Where("contest_id = ?", req.ContestId)
	}
	db.Count(&count)
	db.
		Limit(page.GetPageSize()).
		Offset(page.GetOffset()).
		Order("id desc")
	db.Find(&res)
	rv := make([]*biz.Submission, 0)
	for _, v := range res {
		s := &biz.Submission{
			ID:        v.ID,
			Verdict:   v.Verdict,
			Memory:    v.Memory,
			Time:      v.Time,
			Language:  v.Language,
			Score:     v.Score,
			CreatedAt: v.CreatedAt,
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
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.Submission{
		ID:        res.ID,
		Source:    res.Source,
		Memory:    res.Memory,
		Time:      res.Time,
		Verdict:   res.Verdict,
		Language:  res.Language,
		CreatedAt: res.CreatedAt,
	}, err
}

// CreateSubmission .
func (r *submissionRepo) CreateSubmission(ctx context.Context, s *biz.Submission) (*biz.Submission, error) {
	res := Submission{
		Source:    s.Source,
		UserID:    s.UserID,
		Language:  s.Language,
		ProblemID: s.ProblemID,
		ContestID: s.ContestID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Submission{
		ID: res.ID,
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
