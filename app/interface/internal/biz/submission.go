package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Submission is a Submission model.
type Submission struct {
	ID            int
	ProblemID     int
	Time          int
	Memory        int
	Verdict       int
	Language      int
	Score         int
	UserID        int
	Source        string
	EntityID      int
	EntityType    int
	ProblemNumber int
	ProblemName   string
	User          User
	CreatedAt     time.Time
}

type SubmissionResult struct {
	Score             int
	Verdict           int
	CompileMsg        string
	Memory            int64
	Time              int64
	TotalTestCount    int
	AcceptedTestCount int
	Tests             []*SubmissionTest
}

type SubmissionTest struct {
	Verdict         int
	Stdin           string
	Stdout          string
	Stderr          string
	Answer          string
	Time            int64
	Memory          int64
	ExitCode        int
	Score           int
	CheckerStdout   string
	CheckerExitCode int
}

const (
	SubmissionVerdictPending = iota + 1
	SubmissionVerdictCompileError
	SubmissionVerdictWrongAnswer
	SubmissionVerdictAccepted
	SubmissionVerdictPresentationError
	SubmissionVerdictTimeLimit
	SubmissionVerdictMemoryLimit
	SubmissionVerdictRuntimeError
	SubmissionVerdictSysemError
)

const (
	SubmissionEntityTypeCommon = iota
	SubmissionEntityTypeContest
	SubmissionEntityTypeProblemFile
)

const (
	CheckerVerdictOK                = 0
	CheckerVerdictWrongAnswer       = 1
	CheckerVerdictPresentationError = 2
	CheckerVerdictFail              = 3
	CheckerVerdictPartiallyCorrect  = 16
	CheckerVerdictSystemError
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
	contestRepo   ContestRepo
	sandboxClient sandboxV1.SandboxServiceClient
	log           *log.Helper
}

// NewSubmissionUsecase new a Submission usecase.
func NewSubmissionUsecase(
	repo SubmissionRepo,
	problemRepo ProblemRepo,
	contestRepo ContestRepo,
	sandboxClient sandboxV1.SandboxServiceClient,
	logger log.Logger,
) *SubmissionUsecase {
	return &SubmissionUsecase{
		repo:          repo,
		problemRepo:   problemRepo,
		contestRepo:   contestRepo,
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
	s.Verdict = SubmissionVerdictPending
	// 处理比赛的提交
	if s.EntityType == SubmissionEntityTypeContest {
		// TODO 判断提交权限
		_, err := uc.contestRepo.GetContest(ctx, s.EntityID)
		if err != nil {
			return nil, v1.ErrorContestNotFound(err.Error())
		}
		if !uc.contestRepo.ExistContestUser(ctx, s.EntityID, s.UserID) {
			uc.contestRepo.CreateContestUser(ctx, &ContestUser{ContestID: s.EntityID, UserID: s.UserID})
		}
		contestProblem, err := uc.contestRepo.GetContestProblemByNumber(ctx, s.EntityID, s.ProblemNumber)
		if err != nil {
			return nil, v1.ErrorContestProblemNotFound(err.Error())
		}
		contestProblem.SubmitCount += 1
		uc.contestRepo.UpdateContestProblem(ctx, contestProblem)
		s.ProblemID = contestProblem.ProblemID
	}
	// 处理直接提交至题目
	if s.ProblemID != 0 {
		problem, err := uc.problemRepo.GetProblem(ctx, s.ProblemID)
		if err != nil {
			return nil, v1.ErrorProblemNotFound(err.Error())
		}
		problem.SubmitCount += 1
		uc.problemRepo.UpdateProblem(ctx, problem)
	}
	res, err := uc.repo.CreateSubmission(ctx, s)
	if err != nil {
		return nil, err
	}
	uc.sandboxClient.RunSubmission(ctx, &sandboxV1.RunSubmissionRequest{
		SubmissionId: int64(res.ID),
	})
	return res, nil
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
