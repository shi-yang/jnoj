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
	ID             int
	ProblemID      int
	Time           int
	Memory         int
	Verdict        int
	Language       int
	Score          int
	UserID         int
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
	GetLastSubmission(ctx context.Context, entityType, entityID, userId, problemId int) (*Submission, error)
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
	// 检查访问权限
	problemId := 0
	if req.ProblemId != nil {
		problemId = int(*req.ProblemId)
	}
	isOIModeRunning, hasPermission := uc.checkerPermission(
		ctx, int(req.EntityType),
		int(req.EntityId),
		problemId,
		0,
	)
	if !hasPermission {
		return nil, 0
	}
	submissions, count := uc.repo.ListSubmissions(ctx, req)
	if isOIModeRunning {
		for i := 0; i < len(submissions); i++ {
			submissions[i].Time = 0
			submissions[i].Memory = 0
			submissions[i].Verdict = 0
			submissions[i].Score = 0
			submissions[i].SubmissionInfo = nil
		}
	}
	return submissions, count
}

// checkerPermission 检查访问权限
func (uc *SubmissionUsecase) checkerPermission(ctx context.Context, entityType, entityId, problemId, submissionUserId int) (isOIModeRunning, ok bool) {
	isOIModeRunning = false
	uid, _ := auth.GetUserID(ctx)
	if entityType == SubmissionEntityTypeContest {
		contest, err := uc.contestRepo.GetContest(ctx, int(entityId))
		if err != nil {
			return isOIModeRunning, false
		}
		runningStatus := contest.GetRunningStatus()
		role := contest.Role
		// 比赛未结束时，仅 比赛管理员 admin 或者当前选手可查看
		if runningStatus != ContestRunningStatusFinished {
			// OI 提交之后无反馈
			if contest.Type == ContestTypeOI {
				isOIModeRunning = true
			}
			if role != ContestRoleAdmin && int(submissionUserId) != uid {
				return isOIModeRunning, false
			}
		}
	} else if entityType == SubmissionEntityTypeProblemFile {
		// 处理提交至出题时的文件
		problem, _ := uc.problemRepo.GetProblem(ctx, problemId)
		if !problem.HasPermission(ctx, ProblemPermissionUpdate) {
			return false, false
		}
	}
	return false, true
}

// GetSubmission get a Submission
func (uc *SubmissionUsecase) GetSubmission(ctx context.Context, id int) (*Submission, error) {
	s, err := uc.repo.GetSubmission(ctx, id)
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	info, _ := uc.repo.GetSubmissionInfo(ctx, id)
	// 检查访问权限
	isOIModeRunning, hasPermission := uc.checkerPermission(ctx, s.EntityType, s.EntityID, s.ProblemID, s.UserID)
	if !hasPermission {
		return nil, v1.ErrorForbidden("forbidden")
	}
	// OI 提交之后无反馈
	if isOIModeRunning {
		s.Verdict = SubmissionVerdictPending
		s.Time = 0
		s.Memory = 0
		s.Score = 0
		info = nil
	}
	s.SubmissionInfo = info
	return s, nil
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
		// 用户没有参赛时，自动注册
		if role := uc.contestRepo.GetContestUserRole(ctx, s.EntityID, s.UserID); role == ContestRoleGuest {
			uc.contestRepo.CreateContestUser(ctx, &ContestUser{
				ContestID: s.EntityID,
				UserID:    s.UserID,
				Role:      ContestRoleUnofficialPlayer,
			})
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

// GetLastSubmission 获取最后提交
func (uc *SubmissionUsecase) GetLastSubmission(ctx context.Context, entityType, entityID, problemId int) (*Submission, error) {
	uid, _ := auth.GetUserID(ctx)
	s, err := uc.repo.GetLastSubmission(ctx, entityType, entityID, uid, problemId)
	if err != nil {
		return nil, err
	}
	// 检查访问权限
	isOIModeRunning, _ := uc.checkerPermission(ctx, s.EntityType, s.EntityID, s.ProblemID, s.UserID)
	if isOIModeRunning {
		s.Verdict = SubmissionVerdictPending
		s.Time = 0
		s.Memory = 0
		s.Score = 0
	}
	return s, nil
}
