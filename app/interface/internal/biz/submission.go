package biz

import (
	"context"
	"encoding/json"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

const (
	// 每分钟内能提交多少次
	SubmissionsPerMinute = 3
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
	// 题单提交
	SubmissionEntityTypeProblemset = iota
	// 比赛提交
	SubmissionEntityTypeContest
	// 题目文件提交
	SubmissionEntityTypeProblemFile
	// 题目验题提交
	SubmissionEntityTypeProblemVerify
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
	GetLastMinuteSubmissionCount(ctx context.Context, userId int) int
}

// SubmissionUsecase is a Submission usecase.
type SubmissionUsecase struct {
	repo           SubmissionRepo
	problemRepo    ProblemRepo
	problemsetRepo ProblemsetRepo
	contestRepo    ContestRepo
	sandboxClient  sandboxV1.SandboxServiceClient
	log            *log.Helper
}

// NewSubmissionUsecase new a Submission usecase.
func NewSubmissionUsecase(
	repo SubmissionRepo,
	problemRepo ProblemRepo,
	contestRepo ContestRepo,
	problemsetRepo ProblemsetRepo,
	sandboxClient sandboxV1.SandboxServiceClient,
	logger log.Logger,
) *SubmissionUsecase {
	return &SubmissionUsecase{
		repo:           repo,
		problemRepo:    problemRepo,
		problemsetRepo: problemsetRepo,
		contestRepo:    contestRepo,
		sandboxClient:  sandboxClient,
		log:            log.NewHelper(logger),
	}
}

// ListSubmissions list Submission
func (uc *SubmissionUsecase) ListSubmissions(ctx context.Context, req *v1.ListSubmissionsRequest) ([]*Submission, int64) {
	// 检查访问权限
	problemId := 0
	if req.ProblemId != nil {
		problemId = int(*req.ProblemId)
	}
	isOIModeRunning, _, hasPermission := uc.checkerPermission(
		ctx, int(req.EntityType),
		int(req.EntityId),
		problemId,
		0,
	)
	if !hasPermission {
		return nil, 0
	}
	if isOIModeRunning {
		req.Verdict = nil
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
func (uc *SubmissionUsecase) checkerPermission(ctx context.Context, entityType, entityId, problemId, submissionUserId int) (
	isOIModeRunning, isContestRunning, ok bool) {
	uid, role := auth.GetUserID(ctx)
	if CheckAccess(role, ResourceSubmission) {
		return false, false, true
	}
	if int(submissionUserId) == uid {
		ok = true
	}
	// 处理比赛中的提交
	if entityType == SubmissionEntityTypeContest {
		contest, err := uc.contestRepo.GetContest(ctx, int(entityId))
		if err != nil {
			return
		}
		role := contest.Role
		// 比赛管理员可查看
		if role == ContestRoleAdmin || role == ContestRoleWriter {
			ok = true
		}
		// 比赛未结束时
		runningStatus := contest.GetRunningStatus()
		if runningStatus != ContestRunningStatusFinished {
			isContestRunning = true
			// OI 提交之后无反馈
			if contest.Type == ContestTypeOI {
				isOIModeRunning = true
			}
		}
	} else if entityType == SubmissionEntityTypeProblemFile {
		// 处理提交至出题时的文件
		problem, _ := uc.problemRepo.GetProblem(ctx, problemId)
		if problem.HasPermission(ctx, ProblemPermissionUpdate) {
			ok = true
		}
	} else if entityType == SubmissionEntityTypeProblemset {
		// 处理题单中的提交
		if submissionUserId != 0 && submissionUserId == uid {
			ok = true
		}
		if submissionUserId == 0 {
			ok = true
		}
	} else if entityType == SubmissionEntityTypeProblemVerify {
		// 处理验题中的提交
		problem, _ := uc.problemRepo.GetProblem(ctx, problemId)
		if problem.HasPermission(ctx, ProblemPermissionUpdate) {
			ok = true
		}
	}
	return
}

// GetSubmission get a Submission
func (uc *SubmissionUsecase) GetSubmission(ctx context.Context, id int) (*Submission, error) {
	s, err := uc.repo.GetSubmission(ctx, id)
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	info, _ := uc.repo.GetSubmissionInfo(ctx, id)
	// 检查访问权限
	isOIModeRunning, isContestRunning, hasPermission := uc.checkerPermission(ctx, s.EntityType, s.EntityID, s.ProblemID, s.UserID)
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
	// 比赛未结束不返回测试点信息
	if isContestRunning && s.Verdict != SubmissionVerdictCompileError {
		info = nil
	}
	s.SubmissionInfo = info
	return s, nil
}

// CreateSubmission creates a Submission, and returns the new Submission.
func (uc *SubmissionUsecase) CreateSubmission(ctx context.Context, s *Submission) (*Submission, error) {
	var problem *Problem
	var err error
	s.UserID, _ = auth.GetUserID(ctx)
	s.Verdict = SubmissionVerdictPending
	// 限制每分钟提交次数
	if count := uc.repo.GetLastMinuteSubmissionCount(ctx, s.UserID); count > SubmissionsPerMinute {
		return nil, v1.ErrorSubmissionRateLimit("rate limit")
	}
	// 处理比赛的提交
	if s.EntityType == SubmissionEntityTypeContest {
		contest, err := uc.contestRepo.GetContest(ctx, s.EntityID)
		if err != nil {
			return nil, v1.ErrorContestNotFound(err.Error())
		}
		// 判断提交权限
		if !contest.HasPermission(ctx, ContestPermissionView) {
			return nil, v1.ErrorForbidden("permission denied")
		}
		// 用户没有参赛时，自动注册
		if contestUser := uc.contestRepo.GetContestUser(ctx, s.EntityID, s.UserID); contestUser == nil {
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
		problem, _ = uc.problemRepo.GetProblem(ctx, contestProblem.ProblemID)
		s.ProblemID = contestProblem.ProblemID
	} else if s.EntityType == SubmissionEntityTypeProblemset {
		// 提交至题单
		p, err := uc.problemsetRepo.GetProblemsetProblem(ctx, s.EntityID, s.ProblemNumber)
		if err != nil {
			return nil, v1.ErrorNotFound(err.Error())
		}
		problem, err = uc.problemRepo.GetProblem(ctx, p.ProblemID)
		if err != nil {
			return nil, v1.ErrorNotFound(err.Error())
		}
		// 判断提交权限
		problemset, err := uc.problemsetRepo.GetProblemset(ctx, s.EntityID)
		if err != nil {
			return nil, v1.ErrorNotFound(err.Error())
		}
		// 判断刷题权限
		if problemset.Role == ProblemsetRoleGuest {
			if problemset.Membership == ProblemsetMembershipInvitationCode {
				return nil, v1.ErrorForbidden("permission denied")
			}
		}
		// 自动加入本题单
		uc.problemsetRepo.CreateProblemsetUser(ctx, &ProblemsetUser{
			ProblemsetID: problemset.ID,
			UserID:       s.UserID,
		})
		problem.SubmitCount += 1
		uc.problemRepo.UpdateProblem(ctx, problem)
		s.ProblemID = problem.ID
	} else if s.EntityType == SubmissionEntityTypeProblemVerify {
		// 提交至验题
		problem, err = uc.problemRepo.GetProblem(ctx, s.ProblemNumber)
		if err != nil {
			return nil, v1.ErrorNotFound(err.Error())
		}
		problem.SubmitCount += 1
		uc.problemRepo.UpdateProblem(ctx, problem)
		s.ProblemID = problem.ID
	}
	// 客观题直接判断，不用经过判题机
	if problem != nil && problem.Type == ProblemTypeObjective {
		problem.Statements, _ = uc.problemRepo.ListProblemStatements(ctx, &v1.ListProblemStatementsRequest{
			Id: int32(problem.ID),
		})
		uc.judgeObjectiveProblem(ctx, s, problem)
		res, err := uc.repo.CreateSubmission(ctx, s)
		if err != nil {
			return nil, err
		}
		return res, nil
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

// judgeObjectiveProblem 判断客观题结果
func (uc *SubmissionUsecase) judgeObjectiveProblem(ctx context.Context, s *Submission, problem *Problem) {
	s.Verdict = SubmissionVerdictWrongAnswer
	if len(problem.Statements) > 0 {
		// TODO 暂时不处理多语言，直接取第一个
		statement := problem.Statements[0]
		// 单选题
		if statement.Type == ProblemStatementTypeChoice {
			if s.Source == statement.Output {
				s.Verdict = SubmissionVerdictAccepted
			}
		} else if statement.Type == ProblemStatementTypeMultiple {
			// 多选题
			if isAnswerMatched(s.Source, statement.Output) {
				s.Verdict = SubmissionVerdictAccepted
			}
		} else if statement.Type == ProblemStatementTypeFillBlank {
			// 填空题
			if s.Source == statement.Output {
				s.Verdict = SubmissionVerdictAccepted
			}
		}
	} else {
		s.Verdict = SubmissionVerdictSysemError
	}
}

// 多选题判断选项是否相等
func isAnswerMatched(strA, strB string) bool {
	setA := make(map[string]struct{})
	setB := make(map[string]struct{})

	var arrA, arrB []string
	json.Unmarshal([]byte(strA), &arrA)
	json.Unmarshal([]byte(strB), &arrB)

	// 将元素添加到相应的集合中
	for _, item := range arrA {
		setA[item] = struct{}{}
	}
	for _, item := range arrB {
		setB[item] = struct{}{}
	}

	// 检查集合 B 的元素是否都在集合 A 中
	for item := range setB {
		if _, ok := setA[item]; !ok {
			return false
		}
	}

	// 检查集合 A 的元素是否都在集合 B 中
	for item := range setA {
		if _, ok := setB[item]; !ok {
			return false
		}
	}

	return true
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
	isOIModeRunning, _, _ := uc.checkerPermission(ctx, s.EntityType, s.EntityID, s.ProblemID, s.UserID)
	if isOIModeRunning {
		s.Verdict = SubmissionVerdictPending
		s.Time = 0
		s.Memory = 0
		s.Score = 0
	}
	return s, nil
}
