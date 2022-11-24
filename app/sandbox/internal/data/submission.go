package data

import (
	"context"
	"time"

	"jnoj/app/sandbox/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
	"go.mongodb.org/mongo-driver/bson"
	"go.mongodb.org/mongo-driver/bson/primitive"
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

type Problem struct {
	ID                 int
	Name               string
	TimeLimit          int64
	MemoryLimit        int64
	AcceptedCount      int
	SubmitCount        int
	UserID             int
	CheckerID          int
	VerificationStatus int
	CreatedAt          time.Time
	UpdatedAt          time.Time
	DeletedAt          gorm.DeletedAt
}

type ProblemFile struct {
	ID        int
	Name      string
	Content   string
	Type      string
	ProblemID int
	UserID    int
	FileType  string
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

type ProblemTest struct {
	ID                primitive.ObjectID `bson:"_id"`
	ProblemID         int                `bson:"problem_id"`
	Order             int                `bson:"order"`
	Content           string             `bson:"content"` // 预览的文件内容
	InputSize         int64              `bson:"input_size"`
	InputFileContent  []byte             `bson:"input_file_content"`
	OutputSize        int64              `bson:"output_size"`
	OutputFileContent []byte             `bson:"output_file_content"`
	Remark            string             `bson:"remark"`
	UserID            int                `bson:"user_id"`
	IsExample         bool               `bson:"is_example"`
	CreatedAt         time.Time          `bson:"created_at"`
	UpdatedAt         time.Time          `bson:"updated_at"`
}

type ContestProblem struct {
	ID            int
	Number        int
	ContestID     int
	ProblemID     int
	AcceptedCount int
	SubmitCount   int
	CreatedAt     time.Time
}

const ProblemTestCollection = "problem_test"

// ListSubmissions .
func (r *submissionRepo) GetProblem(ctx context.Context, id int) (*biz.Problem, error) {
	var p Problem
	err := r.data.db.WithContext(ctx).Model(&Problem{}).
		First(&p, "id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	res := &biz.Problem{
		ID:            p.ID,
		TimeLimit:     p.TimeLimit,
		MemoryLimit:   p.MemoryLimit,
		AcceptedCount: p.AcceptedCount,
	}
	res.Checker, err = r.getProblemChecker(ctx, id)
	if err != nil {
		return res, err
	}
	res.Tests = r.listProblemTests(ctx, id)
	return res, nil
}

func (r *submissionRepo) UpdateProblem(ctx context.Context, p *biz.Problem) (*biz.Problem, error) {
	update := Problem{
		ID:            p.ID,
		AcceptedCount: p.AcceptedCount,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&update).Error
	return nil, err
}

// GetContestProblemByProblemID .
func (r *submissionRepo) GetContestProblemByProblemID(ctx context.Context, cid int, problemID int) (*biz.ContestProblem, error) {
	var res ContestProblem
	err := r.data.db.WithContext(ctx).Model(ContestProblem{}).
		First(&res, "contest_id = ? and problem_id = ?", cid, problemID).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.ContestProblem{
		ID:            res.ID,
		ContestID:     res.ContestID,
		ProblemID:     res.ProblemID,
		Number:        res.Number,
		AcceptedCount: res.AcceptedCount,
	}, nil
}

// UpdateContestProblem .
func (r *submissionRepo) UpdateContestProblem(ctx context.Context, c *biz.ContestProblem) (*biz.ContestProblem, error) {
	res := ContestProblem{
		ID:            c.ID,
		AcceptedCount: c.AcceptedCount,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return &biz.ContestProblem{
		ID:            res.ID,
		AcceptedCount: res.AcceptedCount,
	}, err
}

func (r *submissionRepo) getProblemChecker(ctx context.Context, id int) (string, error) {
	var f ProblemFile
	err := r.data.db.WithContext(ctx).
		Where("id = ?", r.data.db.Select("checker_id").Model(&Problem{}).Where("id = ?", id)).
		First(&f).Error
	if err != nil {
		return "", err
	}
	return f.Content, nil
}

func (r *submissionRepo) listProblemTests(ctx context.Context, id int) []*biz.Test {
	var filter = bson.D{{"problem_id", id}}
	var res []*biz.Test
	db := r.data.mongodb.Collection(ProblemTestCollection)
	cursor, err := db.Find(ctx, filter)
	if err != nil {
		return nil
	}
	defer cursor.Close(ctx)
	for cursor.Next(ctx) {
		var result ProblemTest
		err := cursor.Decode(&result)
		if err != nil {
			r.log.Error("cursor.Next() error:", err)
		}
		res = append(res, &biz.Test{
			Input:  string(result.InputFileContent),
			Output: string(result.OutputFileContent),
		})
	}
	return res
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
		ProblemID: res.ProblemID,
		ContestID: res.ContestID,
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
