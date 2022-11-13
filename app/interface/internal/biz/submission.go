package biz

import (
	"context"
	"encoding/json"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"strings"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Submission is a Submission model.
type Submission struct {
	ID        int
	ProblemID int
	Time      int
	Memory    int
	Verdict   int
	Language  int
	Score     int
	UserID    int
	Source    string
	CreatedAt time.Time
}

type SubmissionResult struct {
	Score      int
	Verdict    int
	CompileMsg string
	Memory     int64
	Time       int64
	Tests      []*SubmissionTest
}

type SubmissionTest struct {
	Verdict  int
	Stdin    string
	Stdout   string
	Stderr   string
	Answer   string
	Time     int64
	Memory   int64
	ExitCode int
	Score    int
}

const (
	SubmissionVerdictPending = iota + 1
	SubmissionVerdictCompileError
	SubmissionVerdictWrongAnswer
	SubmissionVerdictAccepted
	SubmissionVerdictTimeLimit
	SubmissionVerdictMemoryLimit
	SubmissionVerdictRuntimeError
	SubmissionVerdictSysemError
)

// SubmissionRepo is a Submission repo.
type SubmissionRepo interface {
	ListSubmissions(context.Context, *v1.ListSubmissionsRequest) ([]*Submission, int64)
	GetSubmission(context.Context, int) (*Submission, error)
	CreateSubmission(context.Context, *Submission) (*Submission, error)
	UpdateSubmission(context.Context, *Submission) (*Submission, error)
	DeleteSubmission(context.Context, int) error
	CreateSubmissionInfo(context.Context, int, string) error
	GetSubmissionInfo(context.Context, int) (*SubmissionResult, error)
}

// SubmissionUsecase is a Submission usecase.
type SubmissionUsecase struct {
	repo          SubmissionRepo
	problemRepo   ProblemRepo
	sandboxClient sandboxV1.SandboxServiceClient
	log           *log.Helper
}

// NewSubmissionUsecase new a Submission usecase.
func NewSubmissionUsecase(repo SubmissionRepo, problemRepo ProblemRepo, sandboxClient sandboxV1.SandboxServiceClient, logger log.Logger) *SubmissionUsecase {
	return &SubmissionUsecase{
		repo:          repo,
		problemRepo:   problemRepo,
		sandboxClient: sandboxClient,
		log:           log.NewHelper(logger),
	}
}

// ListSubmissions list Submission
func (uc *SubmissionUsecase) ListSubmissions(ctx context.Context, req *v1.ListSubmissionsRequest) ([]*Submission, int64) {
	return uc.repo.ListSubmissions(ctx, req)
}

// GetSubmission get a Submission
func (uc *SubmissionUsecase) GetSubmission(ctx context.Context, id int) (*Submission, error) {
	return uc.repo.GetSubmission(ctx, id)
}

// CreateSubmission creates a Submission, and returns the new Submission.
func (uc *SubmissionUsecase) CreateSubmission(ctx context.Context, s *Submission) (*Submission, error) {
	s.UserID, _ = auth.GetUserID(ctx)
	res, _ := uc.repo.CreateSubmission(ctx, s)
	s.ID = res.ID
	go func(s *Submission) {
		res := uc.runTest(context.TODO(), s)
		r, _ := json.Marshal(*res)
		err := uc.repo.CreateSubmissionInfo(context.TODO(), s.ID, string(r))
		if err != nil {
			uc.log.Info("CreateSubmissionInfo err:", err)
		}
		s.Verdict = res.Verdict
		s.Memory = int(res.Memory)
		s.Time = int(res.Time)
		uc.repo.UpdateSubmission(context.TODO(), s)
	}(s)
	return nil, nil
}

func (uc *SubmissionUsecase) runTest(ctx context.Context, s *Submission) *SubmissionResult {
	result := new(SubmissionResult)
	result.Verdict = SubmissionVerdictAccepted
	result.Tests = make([]*SubmissionTest, 0)
	tests, _ := uc.problemRepo.ListProblemTests(context.TODO(), &v1.ListProblemTestsRequest{Id: int32(s.ProblemID)})
	problem, _ := uc.problemRepo.GetProblem(ctx, s.ProblemID)
	for _, test := range tests {
		uc.log.Info("runing test start...")
		resp, err := uc.sandboxClient.Run(ctx, &sandboxV1.RunRequest{
			Stdin:       string(test.InputFileContent),
			Source:      s.Source,
			Language:    int32(s.Language),
			MemoryLimit: problem.MemoryLimit,
			TimeLimit:   problem.TimeLimit,
		})
		uc.log.Info("runing test done...")
		if err != nil {
			uc.log.Info("runing test err:", err)
			continue
		}
		if resp.CompileMsg != "" {
			result.Verdict = SubmissionVerdictCompileError
			result.CompileMsg = resp.CompileMsg
			return result
		}
		if resp.Memory > result.Memory {
			result.Memory = resp.Memory
		}
		if resp.Time > result.Time {
			result.Time = resp.Time
		}
		t := SubmissionTest{
			Stdin:    substrLength(test.InputFileContent, 99),
			Stdout:   substrLength([]byte(resp.Stdout), 99),
			Stderr:   substrLength([]byte(resp.Stderr), 99),
			Answer:   substrLength(test.OutputFileContent, 99),
			Memory:   resp.Memory,
			Time:     resp.Time,
			ExitCode: int(resp.ExitCode),
			Verdict:  SubmissionVerdictAccepted,
		}
		// 判断结果
		if resp.Time/1e3 > problem.TimeLimit {
			t.Verdict = SubmissionVerdictTimeLimit
		} else if resp.Memory >= problem.MemoryLimit*1024 {
			t.Verdict = SubmissionVerdictMemoryLimit
		} else if strings.TrimSpace(resp.Stdout) != strings.TrimSpace(string(test.OutputFileContent)) {
			t.Verdict = SubmissionVerdictWrongAnswer
		}
		if t.Verdict != SubmissionVerdictAccepted {
			result.Verdict = t.Verdict
		}
		result.Tests = append(result.Tests, &t)
	}
	return result
}

// substrLength 截取指定长度字符串，超过指定长度在末尾添加 "..."
func substrLength(str []byte, length int) string {
	if len(str) > length {
		return string(str[:length]) + "..."
	}
	return string(str)
}

// UpdateSubmission update a Submission
func (uc *SubmissionUsecase) UpdateSubmission(ctx context.Context, s *Submission) (*Submission, error) {
	return uc.repo.UpdateSubmission(ctx, s)
}

// DeleteSubmission delete a Submission
func (uc *SubmissionUsecase) DeleteSubmission(ctx context.Context, id int) error {
	return uc.repo.DeleteSubmission(ctx, id)
}

func (uc *SubmissionUsecase) GetSubmissionInfo(ctx context.Context, id int) (*SubmissionResult, error) {
	return uc.repo.GetSubmissionInfo(ctx, id)
}
