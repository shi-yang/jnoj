package biz

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"jnoj/app/sandbox/internal/conf"
	"jnoj/pkg/sandbox"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"

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
	RuntimeErr      string
	ExitCode        int
	Score           float32
	CheckerStdout   string
	CheckerExitCode int
}

type Problem struct {
	ID            int
	Type          int
	TimeLimit     int64
	MemoryLimit   int64
	Checker       string
	AcceptedCount int
}

const (
	ProblemTypeDefault = iota
	ProblemTypeFunction
)

type Test struct {
	ID     int
	Order  int
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

const CheckerPath = "/tmp/sandbox/checker/%d"

// ProblemLanguage 语言文件
type ProblemLanguage struct {
	UserContent string
	MainContent string
}

// SubmissionRepo is a Submission repo.
type SubmissionRepo interface {
	GetSubmission(context.Context, int) (*Submission, error)
	UpdateSubmission(context.Context, *Submission) (*Submission, error)
	CreateSubmissionInfo(context.Context, int, string) error

	GetProblem(context.Context, int) (*Problem, error)
	UpdateProblem(context.Context, *Problem) (*Problem, error)
	GetProblemFile(context.Context, *ProblemFile) (*ProblemFile, error)
	ListProblemTests(context.Context, int) []*Test

	GetContestProblemByProblemID(context.Context, int, int) (*ContestProblem, error)
	UpdateContestProblem(context.Context, *ContestProblem) (*ContestProblem, error)
	UpdateProblemTestStdOutput(context.Context, int, []byte, string) error

	RunSubmissionFromQueue(context.Context, func(ctx context.Context, id int) error) error
	SendSubmissionToQueue(context.Context, int) error
	SendWebsocketMessage(context.Context, *queueV1.Message) error
}

// SubmissionUsecase is a Submission usecase.
type SubmissionUsecase struct {
	conf        *conf.Sandbox
	repo        SubmissionRepo
	sandboxRepo SandboxRepo
	log         *log.Helper
}

var checkerLanguage *sandbox.Language

// NewSubmissionUsecase new a Submission usecase.
func NewSubmissionUsecase(c *conf.Sandbox, repo SubmissionRepo, sandboxRepo SandboxRepo, logger log.Logger) *SubmissionUsecase {
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
	err := s.repo.RunSubmissionFromQueue(context.Background(), s.RunSubmission)
	if err != nil {
		log.Fatal(err)
	}
	return s
}

// CreateSubmission .
func (uc *SubmissionUsecase) CreateSubmission(ctx context.Context, id int) error {
	return uc.repo.SendSubmissionToQueue(ctx, id)
}

// RunSubmission run a Submission
func (uc *SubmissionUsecase) RunSubmission(ctx context.Context, id int) error {
	var source string
	s, _ := uc.repo.GetSubmission(ctx, id)
	problem, _ := uc.repo.GetProblem(ctx, s.ProblemID)
	isGenerateOutput := false
	if s.EntityType == SubmissionEntityTypeProblemFile {
		problemFile, err := uc.repo.GetProblemFile(ctx, &ProblemFile{ID: s.EntityID})
		if err == nil && problemFile.Type == ProblemFileTypeModelSolution {
			isGenerateOutput = true
		}
	}
	source = s.Source
	// 函数题
	if problem.Type == ProblemTypeFunction {
		// 查询对应的语言文件
		lang, err := uc.repo.GetProblemFile(ctx, &ProblemFile{
			ProblemID: problem.ID,
			Language:  s.Language,
			FileType:  string(ProblemFileFileTypeLanguage),
		})
		if err != nil {
			s.Verdict = SubmissionVerdictSysemError
			uc.repo.UpdateSubmission(context.TODO(), s)
			return err
		}
		var problemLang ProblemLanguage
		if err := json.Unmarshal([]byte(lang.Content), &problemLang); err != nil {
			s.Verdict = SubmissionVerdictSysemError
			uc.repo.UpdateSubmission(context.TODO(), s)
			return err
		}
		// @@@替换
		source = strings.ReplaceAll(problemLang.MainContent, "@@@", s.Source)
	}
	problemTest, _ := uc.prepareProblemTest(ctx, problem.ID)
	uc.log.Infof("RunSubmission = [%+v] isGenerateOutput=[%+v]\n", s.ID, isGenerateOutput)
	res := uc.runTests(ctx, s.ID, source, s.Language, s.UserID, problem, problemTest, isGenerateOutput)
	for i, subtask := range res.Subtasks {
		for j, v := range subtask.Tests {
			if isGenerateOutput {
				var outputPreview string
				if len(v.Stdout) > 32 {
					outputPreview = string([]byte(v.Stdout)[:32])
				} else {
					outputPreview = string(v.Stdout)
				}
				// 保存输出
				uc.repo.UpdateProblemTestStdOutput(ctx, problemTest.Subtasks[i].TestData[j].ID, []byte(v.Stdout), outputPreview)
			}
			res.Subtasks[i].Tests[j].Stdin = substrLength([]byte(v.Stdin), 99)
			res.Subtasks[i].Tests[j].Stdout = substrLength([]byte(v.Stdout), 99)
			res.Subtasks[i].Tests[j].Stderr = substrLength([]byte(v.Stderr), 99)
			res.Subtasks[i].Tests[j].Answer = substrLength([]byte(v.Answer), 99)
		}
	}
	r, _ := json.Marshal(*res)
	err := uc.repo.CreateSubmissionInfo(ctx, s.ID, string(r))
	if err != nil {
		uc.log.Info("CreateSubmissionInfo err:", err)
	}
	s.Verdict = res.Verdict
	s.Memory = int(res.Memory)
	s.Time = int(res.Time)
	s.Score = int(res.Score)
	uc.repo.UpdateSubmission(ctx, s)
	// 通过时计数
	if s.Verdict == SubmissionVerdictAccepted {
		if s.EntityType == SubmissionEntityTypeProblemset {
			problem.AcceptedCount += 1
			uc.repo.UpdateProblem(ctx, problem)
		} else if s.EntityType == SubmissionEntityTypeContest {
			contestProblem, err := uc.repo.GetContestProblemByProblemID(ctx, s.EntityID, s.ProblemID)
			if err != nil {
				uc.log.Error(err)
			}
			contestProblem.AcceptedCount += 1
			uc.repo.UpdateContestProblem(ctx, contestProblem)
		}
	}

	// 向客户端发送测评进度
	uc.repo.SendWebsocketMessage(ctx, &queueV1.Message{
		Type:   queueV1.Message_SUBMISSION_RESULT,
		UserId: int32(s.UserID),
		Message: map[string]string{
			"sid":     strconv.Itoa(s.ID),
			"status":  "done",
			"message": fmt.Sprintf("testing on %d/%d", res.AcceptedTestCount, res.TotalTestCount),
		},
	})
	return nil
}

func (uc *SubmissionUsecase) prepareProblemTest(ctx context.Context, problemId int) (*ProblemSubtask, error) {
	var (
		subtaskContent string
	)
	tests := uc.repo.ListProblemTests(ctx, problemId)
	subtaskFile, err := uc.repo.GetProblemFile(ctx, &ProblemFile{
		ProblemID: problemId,
		FileType:  string(ProblemFileFileTypeSubtask),
	})
	if err == nil {
		subtaskContent = subtaskFile.Content
	}
	problemSubtask := &ProblemSubtask{}
	subtasks, err := uc.GetProblemSubtaskContent(subtaskContent)
	// 子任务配置文件不存在，则平分每个测试点作为子任务
	if err != nil {
		task := Subtask{
			Score: 100,
		}
		task.TestData = append(task.TestData, tests...)
		problemSubtask.Subtasks = append(problemSubtask.Subtasks, task)
		problemSubtask.TotalTest = len(tests)
	} else {
		// 子任务存在
		problemSubtask.HasSubtask = true
		problemSubtask.Subtasks = append(problemSubtask.Subtasks, subtasks...)
		for i, task := range problemSubtask.Subtasks {
			for _, order := range task.Tests {
				for _, test := range tests {
					if test.Order == order {
						problemSubtask.Subtasks[i].TestData = append(problemSubtask.Subtasks[i].TestData, test)
					}
				}
			}
			problemSubtask.TotalTest += len(problemSubtask.Subtasks[i].TestData)
		}
	}
	return problemSubtask, nil
}

func (uc *SubmissionUsecase) runTests(
	ctx context.Context,
	submissionId int,
	source string,
	langCode int,
	userId int,
	problem *Problem,
	problemTest *ProblemSubtask,
	isGenerateOutput bool,
) *SubmissionResult {
	uc.log.Info(source)
	result := new(SubmissionResult)
	result.Verdict = SubmissionVerdictAccepted
	result.Subtasks = make([]*SubmissionSubtaskResult, 0)
	result.TotalTestCount = problemTest.TotalTest
	result.HasSubtask = problemTest.HasSubtask

	// 编译源码
	u, _ := uuid.NewUUID()
	workDir := filepath.Join("/tmp/sandbox", u.String())
	defer os.RemoveAll(workDir)
	err := sandbox.Compile(workDir, source, &sandbox.Languages[langCode])
	if err != nil {
		result.Verdict = SubmissionVerdictCompileError
		result.CompileMsg = err.Error()
		return result
	}

	// 准备 checker
	if !isGenerateOutput {
		err = uc.prepareChecker(workDir, problem)
		if err != nil {
			uc.log.Info("sandbox.Compile err:", err)
			result.Verdict = SubmissionVerdictSysemError
			return result
		}
	}
	// 子任务
	currentTest := 0
	for _, subtask := range problemTest.Subtasks {
		// 子任务的测试点
		var subtaskResult SubmissionSubtaskResult
		subtaskResult.Verdict = SubmissionVerdictAccepted
		for _, test := range subtask.TestData {
			currentTest++
			uc.log.Infof("Submission[%d] runing test [%d/%d] start...", submissionId, currentTest, problemTest.TotalTest)
			// 向客户端发送测评进度
			go func() {
				uc.repo.SendWebsocketMessage(ctx, &queueV1.Message{
					Type:   queueV1.Message_SUBMISSION_RESULT,
					UserId: int32(userId),
					Message: map[string]string{
						"sid":     strconv.Itoa(submissionId),
						"status":  "running",
						"message": fmt.Sprintf("testing on %d/%d", currentTest, problemTest.TotalTest),
					},
				})
			}()
			// 开始运行
			runRes := sandbox.Run(workDir, &sandbox.Languages[langCode], []byte(test.Input), problem.MemoryLimit, problem.TimeLimit)
			var checkerRes *sandbox.Result
			// 准备运行 checker 所需文件
			if runRes.RuntimeErr == "" && !isGenerateOutput {
				_ = os.WriteFile(filepath.Join(workDir, "user.stdout"), []byte(runRes.Stdout), 0444)
				_ = os.WriteFile(filepath.Join(workDir, "data.in"), []byte(test.Input), 0444)
				_ = os.WriteFile(filepath.Join(workDir, "data.out"), []byte(test.Output), 0444)
				// 执行 checker
				uc.log.Info("Run checker:", workDir)
				checkerRes = sandbox.Run(workDir, checkerLanguage, []byte(""), 256, 10000)
			}
			uc.log.Infof("Submission[%d] runing test [%d/%d] done...", submissionId, currentTest, problemTest.TotalTest)
			// 记录 Memory 最大值
			if runRes.Memory > subtaskResult.Memory {
				subtaskResult.Memory = runRes.Memory
			}
			// 记录 Time 最大值
			if runRes.Time > subtaskResult.Time {
				subtaskResult.Time = runRes.Time
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
				subtaskResult.Time = problem.TimeLimit * 1e3
			} else if runRes.Memory >= problem.MemoryLimit*1024 {
				t.Memory = problem.MemoryLimit * 1024
				t.Verdict = SubmissionVerdictMemoryLimit
				subtaskResult.Memory = problem.MemoryLimit * 1024
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
			if t.Verdict == SubmissionVerdictAccepted && !problemTest.HasSubtask {
				t.Score = 100 / float32(problemTest.TotalTest)
			}
			subtaskResult.Tests = append(subtaskResult.Tests, &t)
			// 记录结果
			if t.Verdict != SubmissionVerdictAccepted {
				result.Verdict = t.Verdict
				subtaskResult.Verdict = t.Verdict
				// 有子任务的情况下，一旦遇到不是 Accepted，剩下的可以跳过
				if problemTest.HasSubtask {
					break
				}
			} else {
				result.AcceptedTestCount++
				if !problemTest.HasSubtask {
					subtaskResult.Score = 100 / float32(problemTest.TotalTest)
				}
			}
		}
		if subtaskResult.Verdict == SubmissionVerdictAccepted {
			result.Score += float32(subtask.Score)
			subtaskResult.Score = float32(subtask.Score)
		} else {
			subtaskResult.Score = 0
		}
		if result.Time < subtaskResult.Time {
			result.Time = subtaskResult.Time
		}
		if result.Memory < subtaskResult.Memory {
			result.Memory = subtaskResult.Memory
		}
		if !problemTest.HasSubtask && problemTest.TotalTest != 0 {
			result.Score = 100 * float32(result.AcceptedTestCount) / float32(problemTest.TotalTest)
		}
		result.Subtasks = append(result.Subtasks, &subtaskResult)
	}
	return result
}

func (uc *SubmissionUsecase) prepareChecker(workDir string, problem *Problem) error {
	// checker 将放到 tmp 临时目录下
	checkerPath := fmt.Sprintf(CheckerPath, problem.ID)
	tmpChecker, err := os.ReadFile(filepath.Join(checkerPath, "checker.txt"))
	// checker 内容有变化，重新编译
	if err != nil || (err == nil && string(tmpChecker) != problem.Checker) {
		os.WriteFile(filepath.Join(checkerPath, "checker.txt"), []byte(problem.Checker), 0444)
		// 编译一个checker
		if err = sandbox.Compile(checkerPath, problem.Checker, checkerLanguage); err != nil {
			return err
		}
		// 复制到 workDir
		return copy(filepath.Join(checkerPath, "checker.exe"), filepath.Join(workDir, "checker.exe"))
	}
	return copy(filepath.Join(checkerPath, "checker.exe"), filepath.Join(workDir, "checker.exe"))
}

// copy 复制文件
func copy(src, dst string) error {
	_, err := os.Stat(src)
	if err != nil {
		return err
	}
	source, err := os.Open(src)
	if err != nil {
		return err
	}
	defer source.Close()

	_, err = os.Stat(dst)
	if err == nil {
		return fmt.Errorf("file %s already exists", dst)
	}
	destination, err := os.Create(dst)
	if err != nil {
		return err
	}
	defer destination.Close()

	if err != nil {
		panic(err)
	}
	buf := make([]byte, 2048)
	for {
		n, err := source.Read(buf)
		if err != nil && err != io.EOF {
			return err
		}
		if n == 0 {
			break
		}
		if _, err := destination.Write(buf[:n]); err != nil {
			return err
		}
	}
	return err
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
