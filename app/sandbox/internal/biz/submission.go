package biz

import (
	"context"
	"encoding/json"
	"fmt"
	"jnoj/app/sandbox/internal/conf"
	"jnoj/pkg/message_queue/rabbitmq"
	"jnoj/pkg/sandbox"
	"os"
	"path/filepath"
	"strconv"
	"time"

	"github.com/go-kratos/kratos/v2/encoding"
	_ "github.com/go-kratos/kratos/v2/encoding/json"
	"github.com/go-kratos/kratos/v2/log"
	"github.com/google/uuid"

	queueV1 "jnoj/api/queue/v1"
)

// Submission is a Submission model.
type Submission struct {
	ID         int
	ProblemID  int
	EntityID   int
	EntityType int
	Time       int
	Memory     int
	Verdict    int
	Language   int
	Score      int
	UserID     int
	Source     string
	CreatedAt  time.Time
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
	RuntimeErr      string
	ExitCode        int
	Score           int
	CheckerStdout   string
	CheckerExitCode int
}

type Problem struct {
	ID            int
	TimeLimit     int64
	MemoryLimit   int64
	Checker       string
	AcceptedCount int
	Tests         []*Test
}

type Test struct {
	ID     int
	Input  []byte
	Output []byte
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
	GetSubmission(context.Context, int) (*Submission, error)
	CreateSubmission(context.Context, *Submission) (*Submission, error)
	UpdateSubmission(context.Context, *Submission) (*Submission, error)
	CreateSubmissionInfo(context.Context, int, string) error

	GetProblem(context.Context, int) (*Problem, error)
	UpdateProblem(context.Context, *Problem) (*Problem, error)
	GetProblemFile(context.Context, int) (*ProblemFile, error)
	ListProblemTests(context.Context, int) []*Test

	GetContestProblemByProblemID(context.Context, int, int) (*ContestProblem, error)
	UpdateContestProblem(context.Context, *ContestProblem) (*ContestProblem, error)
	UpdateProblemTestStdOutput(context.Context, int, []byte, string) error
}

// SubmissionUsecase is a Submission usecase.
type SubmissionUsecase struct {
	conf        *conf.Sandbox
	repo        SubmissionRepo
	sandboxRepo SandboxRepo
	log         *log.Helper
	queueClient *rabbitmq.Client
}

var checkerLanguage *sandbox.Language

// NewSubmissionUsecase new a Submission usecase.
func NewSubmissionUsecase(c *conf.Sandbox, cf *conf.Service, repo SubmissionRepo, sandboxRepo SandboxRepo, logger log.Logger) *SubmissionUsecase {
	checkerLanguage = &sandbox.Language{
		Name: "checker",
		CompileCommand: []string{"g++", "checker.cpp", "-o", "checker.exe", "-I" + c.TestlibPath, "-Wall",
			"-fno-asm", "-O2", "-lm", "--static", "-std=c++11", "-DONLINE_JUDGE", "-save-temps", "-fmax-errors=10"},
		RunCommand:   []string{"./checker.exe", "data.in", "user.stdout", "data.out"},
		CodeFileName: "checker.cpp",
		IsVMRun:      false,
	}
	s := &SubmissionUsecase{
		conf:        c,
		repo:        repo,
		sandboxRepo: sandboxRepo,
		log:         log.NewHelper(logger),
	}
	s.queueClient = rabbitmq.NewClient(cf.MessageQueue.Address, "websocket")
	return s
}

// RunSubmission run a Submission
func (uc *SubmissionUsecase) RunSubmission(ctx context.Context, id int) (*Submission, error) {
	s, _ := uc.repo.GetSubmission(ctx, id)
	problem, _ := uc.repo.GetProblem(ctx, s.ProblemID)
	needUpdateAnswer := false
	if s.EntityType == SubmissionEntityTypeProblemFile {
		problemFile, err := uc.repo.GetProblemFile(ctx, s.EntityID)
		if err == nil && problemFile.Type == "model_solution" {
			needUpdateAnswer = true
		}
	}
	uc.log.Infof("RunSubmission = [%+v] needUpdateAnswer=[%+v]\n", s.ID, needUpdateAnswer)
	go func(s *Submission) {
		res := uc.runTests(context.TODO(), s, problem, needUpdateAnswer)
		for index, v := range res.Tests {
			if needUpdateAnswer {
				var outputPreview string
				if len(v.Stdout) > 32 {
					outputPreview = string([]byte(v.Stdout)[:32])
				} else {
					outputPreview = string(v.Stdout)
				}
				uc.repo.UpdateProblemTestStdOutput(context.TODO(), problem.Tests[index].ID, []byte(v.Stdout), outputPreview)
			}
			res.Tests[index].Stdin = substrLength([]byte(v.Stdin), 99)
			res.Tests[index].Stdout = substrLength([]byte(v.Stdout), 99)
			res.Tests[index].Stderr = substrLength([]byte(v.Stderr), 99)
			res.Tests[index].Answer = substrLength([]byte(v.Answer), 99)
		}
		r, _ := json.Marshal(*res)
		err := uc.repo.CreateSubmissionInfo(context.TODO(), s.ID, string(r))
		if err != nil {
			uc.log.Info("CreateSubmissionInfo err:", err)
		}
		s.Verdict = res.Verdict
		s.Memory = int(res.Memory)
		s.Time = int(res.Time)
		uc.repo.UpdateSubmission(context.TODO(), s)
		// 通过时计数
		if s.Verdict == SubmissionVerdictAccepted {
			ctx := context.TODO()
			if s.EntityType == SubmissionEntityTypeCommon {
				problem.AcceptedCount += 1
				uc.repo.UpdateProblem(context.TODO(), problem)
			} else if s.EntityType == SubmissionEntityTypeContest {
				contestProblem, err := uc.repo.GetContestProblemByProblemID(ctx, s.EntityID, s.ProblemID)
				if err != nil {
					return
				}
				contestProblem.AcceptedCount += 1
				uc.repo.UpdateContestProblem(ctx, contestProblem)
			}
		}

		// 向客户端发送测评进度
		go func() {
			m := queueV1.Message{
				Type:    queueV1.Message_SUBMISSION_RESULT,
				UserId:  int32(s.UserID),
				Message: make(map[string]string),
			}
			m.Message["sid"] = strconv.Itoa(s.ID)
			m.Message["status"] = "done"
			m.Message["message"] = fmt.Sprintf("testing on %d/%d", res.AcceptedTestCount, res.TotalTestCount)
			jsonCodec := encoding.GetCodec("json")
			res, _ := jsonCodec.Marshal(&m)
			uc.queueClient.Push(context.TODO(), res)
		}()
	}(s)
	return nil, nil
}

func (uc *SubmissionUsecase) runTests(ctx context.Context, s *Submission, problem *Problem, needUpdateAnswer bool) *SubmissionResult {
	result := new(SubmissionResult)
	result.Verdict = SubmissionVerdictAccepted
	result.Tests = make([]*SubmissionTest, 0)

	// 编译源码
	u, _ := uuid.NewUUID()
	workDir := filepath.Join("/tmp/sandbox", u.String())
	defer os.RemoveAll(workDir)
	uc.log.Infof("Submission[%d] Compile start:%s", s.ID, workDir)
	err := sandbox.Compile(workDir, s.Source, &sandbox.Languages[s.Language])
	uc.log.Infof("Submission[%d] Compile done:%s", s.ID, workDir)
	if err != nil {
		result.Verdict = SubmissionVerdictCompileError
		result.CompileMsg = err.Error()
		return result
	}

	// 编译 checker
	// TODO checker 可能被用户进程修改？
	if !needUpdateAnswer {
		err = sandbox.Compile(workDir, problem.Checker, checkerLanguage)
		if err != nil {
			uc.log.Info("sandbox.Compile err:", err)
			return result
		}
	}
	result.TotalTestCount = len(problem.Tests)
	for index, test := range problem.Tests {
		uc.log.Infof("Submission[%d] runing test [%d/%d] start...", s.ID, index+1, len(problem.Tests))
		// 向客户端发送测评进度
		go func() {
			m := queueV1.Message{
				Type:    queueV1.Message_SUBMISSION_RESULT,
				UserId:  int32(s.UserID),
				Message: make(map[string]string),
			}
			m.Message["sid"] = strconv.Itoa(s.ID)
			m.Message["status"] = "running"
			m.Message["message"] = fmt.Sprintf("testing on %d/%d", index+1, result.TotalTestCount)
			jsonCodec := encoding.GetCodec("json")
			res, _ := jsonCodec.Marshal(&m)
			uc.queueClient.Push(context.TODO(), res)
		}()
		runRes := sandbox.Run(workDir, &sandbox.Languages[s.Language], []byte(test.Input), problem.MemoryLimit, problem.TimeLimit)
		var checkerRes *sandbox.Result
		if runRes.RuntimeErr == "" && !needUpdateAnswer {
			// 准备运行 checker 所需文件
			_ = os.WriteFile(filepath.Join(workDir, "user.stdout"), []byte(runRes.Stdout), 0444)
			_ = os.WriteFile(filepath.Join(workDir, "data.in"), []byte(test.Input), 0444)
			_ = os.WriteFile(filepath.Join(workDir, "data.out"), []byte(test.Output), 0444)
			// 执行 checker
			uc.log.Info("Run checker:", workDir)
			checkerRes = sandbox.Run(workDir, checkerLanguage, []byte(""), 256, 10000)
		}
		uc.log.Infof("Submission[%d] runing test [%d/%d] done...", s.ID, index+1, len(problem.Tests))
		// 记录 Memory 最大值
		if runRes.Memory > result.Memory {
			result.Memory = runRes.Memory
		}
		// 记录 Time 最大值
		if runRes.Time > result.Time {
			result.Time = runRes.Time
		}
		// 记录结果
		t := SubmissionTest{
			Stdin:      string(test.Input),
			Stdout:     runRes.Stdout,
			Stderr:     runRes.Stderr,
			Answer:     string(test.Output),
			RuntimeErr: runRes.RuntimeErr,
			Memory:     runRes.Memory,
			Time:       runRes.Time,
			ExitCode:   int(runRes.ExitCode),
			Verdict:    SubmissionVerdictAccepted,
		}
		if checkerRes != nil {
			t.CheckerStdout = substrLength([]byte(checkerRes.Stderr), 99)
			t.CheckerExitCode = int(checkerRes.ExitCode)
		}
		// 判断结果
		if runRes.Time/1e3 > problem.TimeLimit {
			t.Time = problem.TimeLimit * 1e3
			t.Verdict = SubmissionVerdictTimeLimit
			result.Time = problem.TimeLimit * 1e3
		} else if runRes.Memory >= problem.MemoryLimit*1024 {
			t.Memory = problem.MemoryLimit * 1024
			t.Verdict = SubmissionVerdictMemoryLimit
			result.Time = problem.MemoryLimit * 1024
		} else if runRes.RuntimeErr != "" {
			t.Verdict = SubmissionVerdictRuntimeError
		}
		if t.Verdict == SubmissionVerdictAccepted {
			// 根据 checker 运行结果来判断
			if t.CheckerExitCode == CheckerVerdictOK {
				t.Verdict = SubmissionVerdictAccepted
			} else if t.CheckerExitCode == CheckerVerdictPresentationError {
				t.Verdict = SubmissionVerdictPresentationError
			} else if t.CheckerExitCode == CheckerVerdictFail {
				t.Verdict = SubmissionVerdictSysemError
			} else {
				t.Verdict = SubmissionVerdictWrongAnswer
			}
		}
		result.Tests = append(result.Tests, &t)
		if t.Verdict != SubmissionVerdictAccepted {
			result.Verdict = t.Verdict
			break
		} else {
			result.AcceptedTestCount++
		}
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
