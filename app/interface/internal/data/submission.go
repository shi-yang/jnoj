package data

import (
	"context"
	"encoding/json"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
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
	db := r.data.db.WithContext(ctx).
		Model(&Submission{})
	db.Where("problem_id = ?", req.ProblemId)
	db.Count(&count)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db.
		Limit(page.GetPageSize()).
		Offset(page.GetOffset()).
		Order("id desc")
	db.Find(&res)
	rv := make([]*biz.Submission, 0)
	for _, v := range res {
		rv = append(rv, &biz.Submission{
			ID:        v.ID,
			Verdict:   v.Verdict,
			Memory:    v.Memory,
			Time:      v.Time,
			Language:  v.Language,
			Score:     v.Score,
			CreatedAt: v.CreatedAt,
		})
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
