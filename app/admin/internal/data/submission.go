package data

import (
	"context"
	"time"

	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
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
	if req.EntityType != nil {
		db.Where("entity_type = ?", req.EntityType)
	}
	if len(req.Ids) > 0 {
		db.Where("id in (?)", req.Ids)
	}
	if req.ProblemId != 0 {
		db.Where("problem_id = ?", req.ProblemId)
	}
	db.Count(&count)
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
			UserID:     v.UserID,
		}
		rv = append(rv, s)
	}
	return rv, count
}

// UpdateSubmission .
func (r *submissionRepo) UpdateSubmission(ctx context.Context, s *biz.Submission) (*biz.Submission, error) {
	res := Submission{
		ID:      s.ID,
		Verdict: s.Verdict,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}
