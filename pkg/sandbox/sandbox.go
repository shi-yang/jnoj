package sandbox

import (
	"bytes"
	"encoding/json"
	"io"
	"os"
	"os/exec"
	"strconv"
	"strings"
	"syscall"
	"time"

	"jnoj/pkg/sandbox/container"

	"github.com/docker/docker/pkg/reexec"
	"github.com/google/uuid"
)

func init() {
	reexec.Register("sandboxInit", sandboxInit)
	if reexec.Init() {
		os.Exit(0)
	}
}

type Result struct {
	// Runtime 运行时间
	Time int64
	// Memory 消耗内存
	Memory int64
	// Stdin 程序输入
	Stdin string
	// Stdout 程序输出
	Stdout string
	// Stderr 程序错误输出
	Stderr     string
	RuntimeErr string // 运行出错
	Err        string
	ExitCode   int
}

func sandboxInit() {
	var r Result
	basedir := os.Args[1]
	containerID := os.Args[2]
	timeLimit, _ := strconv.ParseInt(os.Args[3], 10, 64)
	memoryLimit, _ := strconv.ParseInt(os.Args[4], 10, 64)
	argsRunCommand := os.Args[5]
	input, _ := io.ReadAll(os.Stdin)
	runCommand := strings.Split(argsRunCommand, ",")

	// 由于沙箱只能限制程序的 real time，但要返回的是 user time + sys time
	// 为防止用户调用 sleep 之类的函数不能很好的测量 user time + sys time，
	// 因此在此处根据题目给出的时间限制加 1s 作为限制 real time 时间
	timeLimit += 1000
	// 限制内存时，多给 4 MB
	memoryLimit += 4

	if err := container.InitCGroup(strconv.Itoa(os.Getpid()), containerID, strconv.FormatInt(memoryLimit, 10)); err != nil {
		r.Err = err.Error()
		result, _ := json.Marshal(r)
		_, _ = os.Stdout.Write(result)
		os.Exit(0)
	}

	if err := container.InitNamespace(basedir); err != nil {
		r.Err = err.Error()
		result, _ := json.Marshal(r)
		_, _ = os.Stdout.Write(result)
		os.Exit(0)
	}
	var stdout, stderr bytes.Buffer
	cmd := exec.Command(runCommand[0], runCommand[1:]...)
	cmd.Stdin = bytes.NewBuffer(input)
	cmd.Stdout = &stdout
	cmd.Stderr = &stderr
	cmd.SysProcAttr = &syscall.SysProcAttr{
		Setpgid: true,
	}
	cmd.Env = []string{"PS1=[sandbox] # "}

	time.AfterFunc(time.Duration(timeLimit)*time.Millisecond, func() {
		_ = syscall.Kill(-cmd.Process.Pid, syscall.SIGKILL)
	})
	err := cmd.Run()
	// 异常结束
	if err != nil {
		os.Stderr.WriteString(err.Error())
		r.RuntimeErr = err.Error()
	}

	sTime := cmd.ProcessState.SystemTime().Microseconds()
	uTime := cmd.ProcessState.UserTime().Microseconds()

	r.ExitCode = cmd.ProcessState.ExitCode()
	r.Stdin = string(input)
	r.Stdout = stdout.String()
	r.Stderr = stderr.String()
	r.Time = sTime + uTime // 返回微秒 μs
	r.Memory = cmd.ProcessState.SysUsage().(*syscall.Rusage).Maxrss
	result, _ := json.Marshal(r)
	_, _ = os.Stdout.Write(result)
}

// Run 运行用户提交的程序
func Run(basedir string, lang *Language, input []byte, memoryLimit int64, timeLimit int64) *Result {
	var result Result
	result.ExitCode = -1
	var stdout, stderr bytes.Buffer
	u, _ := uuid.NewRandom()
	containerID := u.String()
	defer container.CleanCGroup(containerID)
	cmd := reexec.Command("sandboxInit",
		basedir,
		containerID,
		strconv.FormatInt(timeLimit, 10),
		strconv.FormatInt(memoryLimit, 10),
		strings.Join(lang.RunCommand, ","),
	)
	cmd.Stdin = bytes.NewBuffer(input)
	cmd.Stdout = &stdout
	cmd.Stderr = &stderr
	cmd.SysProcAttr = &syscall.SysProcAttr{
		Cloneflags: syscall.CLONE_NEWNS |
			syscall.CLONE_NEWUTS |
			syscall.CLONE_NEWIPC |
			syscall.CLONE_NEWPID |
			syscall.CLONE_NEWNET |
			syscall.CLONE_NEWUSER,
		UidMappings: []syscall.SysProcIDMap{
			{
				ContainerID: 0,
				HostID:      os.Getuid(),
				Size:        1,
			},
		},
		GidMappings: []syscall.SysProcIDMap{
			{
				ContainerID: 0,
				HostID:      os.Getgid(),
				Size:        1,
			},
		},
	}
	_ = cmd.Run()
	_ = json.Unmarshal(stdout.Bytes(), &result)
	return &result
}
