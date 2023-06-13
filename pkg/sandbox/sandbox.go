package sandbox

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"os"
	"os/exec"
	"path/filepath"
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
	// Stdout 程序输出
	Stdout string
	// Stderr 程序错误输出
	Stderr string
	// RuntimeErr 运行出错信息
	RuntimeErr string
	// Err 记录系统错误信息
	Err string
	// ExitCode 程序退出代码
	ExitCode int
}

const (
	RLIMIT_STACK = 0x3
	STD_MB       = 1 << 20 // 1MB = 1024*1024B
)

func sandboxInit() {
	var r Result
	basedir := os.Args[1]
	containerID := os.Args[2]
	timeLimit, _ := strconv.ParseInt(os.Args[3], 10, 64)
	memoryLimit, _ := strconv.ParseInt(os.Args[4], 10, 64)
	argsRunCommand := os.Args[5]
	runCommand := strings.Split(argsRunCommand, ",")

	// 由于沙箱只能限制程序的 real time，但要返回的是 user time + sys time
	// 为防止用户调用 sleep 之类的函数不能很好的测量 user time + sys time，
	// 因此在此处根据题目给出的时间限制加 2s 作为限制 real time 时间
	timeLimit += 2000
	// 限制内存时，多给 16 MB
	memoryLimit += 16

	// 创建输出文件
	outputFile, err := os.Create(filepath.Join(basedir, "output.txt"))
	if err != nil {
		r.Err = err.Error()
		result, _ := json.Marshal(r)
		_, _ = os.Stdout.Write(result)
		os.Exit(0)
	}
	defer outputFile.Close()

	if err := container.Newcgroup().Install(strconv.Itoa(os.Getpid()), containerID, strconv.FormatInt(memoryLimit, 10)); err != nil {
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
	// 设置当前进程的栈大小限制为 128MB
	var rLimit syscall.Rlimit
	rLimit.Cur = STD_MB << 7
	rLimit.Max = STD_MB << 7
	if err := syscall.Setrlimit(RLIMIT_STACK, &rLimit); err != nil {
		r.Err = err.Error()
		result, _ := json.Marshal(r)
		_, _ = os.Stdout.Write(result)
		os.Exit(0)
	}

	// 执行准备：命令、输入输出等
	var stderr bytes.Buffer
	cmd := exec.Command(runCommand[0], runCommand[1:]...)
	cmd.Stdin = os.Stdin
	cmd.Stderr = &stderr
	cmd.Stdout = outputFile
	cmd.SysProcAttr = &syscall.SysProcAttr{
		Setpgid: true,
	}
	cmd.Env = []string{"PS1=[sandbox] # "}
	// 限制程序运行时间
	isTimeout := false
	time.AfterFunc(time.Duration(timeLimit)*time.Millisecond, func() {
		os.Stderr.WriteString("time limit: timeout")
		isTimeout = true
		_ = syscall.Kill(-cmd.Process.Pid, syscall.SIGKILL)
	})
	// 开始执行
	if err := cmd.Run(); err != nil {
		// 异常结束
		os.Stderr.WriteString(err.Error())
		r.RuntimeErr = err.Error()
	}

	if isTimeout {
		r.Time = int64(time.Duration(time.Duration(timeLimit) * time.Millisecond).Microseconds())
	} else {
		sTime := cmd.ProcessState.SystemTime().Microseconds()
		uTime := cmd.ProcessState.UserTime().Microseconds()
		r.Time = sTime + uTime // 返回微秒 μs
	}

	r.ExitCode = cmd.ProcessState.ExitCode()
	r.Stderr = stderr.String()
	r.Memory = cmd.ProcessState.SysUsage().(*syscall.Rusage).Maxrss
	result, _ := json.Marshal(r)
	_, _ = os.Stdout.Write(result)
}

// Run 运行用户提交的程序
func Run(basedir string, lang *Language, input []byte, memoryLimit int64, timeLimit int64) (result *Result) {
	result = new(Result)
	result.ExitCode = -1
	var stdout, stderr bytes.Buffer
	u, _ := uuid.NewRandom()
	containerID := u.String()
	defer container.Newcgroup().Uninstall(containerID)
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
	outputFile, err := os.Open(filepath.Join(basedir, "output.txt"))
	if err != nil {
		result.Err = err.Error()
		return
	}
	defer outputFile.Close()
	output, _ := io.ReadAll(outputFile)
	result.Stdout = string(output)
	fmt.Println(stderr.String())
	return
}
