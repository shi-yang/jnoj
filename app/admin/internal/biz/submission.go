package biz

import (
	"context"
	v1 "jnoj/api/admin/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Submission is a Submission model.
type Submission struct {
	ID             int
	ProblemID      int
	Time           int
	Memory         int
	Verdict        int
	Language       int
	Score          int
	UserID         int
	Nickname       string
	Source         string
	EntityID       int
	EntityType     int
	ProblemNumber  int
	ProblemName    string
	User           User
	CreatedAt      time.Time
	SubmissionInfo *SubmissionResult
}

type SubmissionResult struct {
	Score             float32
	Verdict           int
	CompileMsg        string
	Memory            int64
	Time              int64
	TotalTestCount    int
	HasSubtask        bool
	AcceptedTestCount int
	Subtasks          []*SubmissionSubtaskResult // 子任务
}

type SubmissionSubtaskResult struct {
	Score   float32           // 分数
	Time    int64             // 时间
	Memory  int64             // 内存
	Verdict int               // 结果
	Tests   []*SubmissionTest // 测试点
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
	Score           float32
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
	SubmissionEntityTypeProblemset = iota
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
	ListSubmissions(ctx context.Context, req *v1.ListSubmissionsRequest) ([]*Submission, int64)
}

// SubmissionUsecase is a Submission usecase.
type SubmissionUsecase struct {
	repo          SubmissionRepo
	sandboxClient sandboxV1.SandboxServiceClient
	log           *log.Helper
}

// NewSubmissionUsecase new a Submission usecase.
func NewSubmissionUsecase(
	repo SubmissionRepo,
	sandboxClient sandboxV1.SandboxServiceClient,
	logger log.Logger,
) *SubmissionUsecase {
	return &SubmissionUsecase{
		repo:          repo,
		sandboxClient: sandboxClient,
		log:           log.NewHelper(logger),
	}
}

// Rejudge 重新测评
func (uc *SubmissionUsecase) Rejudge(ctx context.Context, contestId, problemId, submissionId int) {
	var submissions []*Submission
	if contestId != 0 {
		var t = v1.SubmissionEntityType_CONTEST
		submissions, _ = uc.repo.ListSubmissions(ctx, &v1.ListSubmissionsRequest{
			EntityType: &t,
			EntityId:   int32(contestId),
		})
	} else if problemId != 0 {
		submissions, _ = uc.repo.ListSubmissions(ctx, &v1.ListSubmissionsRequest{
			ProblemId: int32(problemId),
		})
	} else if submissionId != 0 {
		submissions, _ = uc.repo.ListSubmissions(ctx, &v1.ListSubmissionsRequest{
			Ids: []int32{int32(submissionId)},
		})
	}
	for _, v := range submissions {
		uc.sandboxClient.RunSubmission(ctx, &sandboxV1.RunSubmissionRequest{
			SubmissionId: int64(v.ID),
		})
	}
}
