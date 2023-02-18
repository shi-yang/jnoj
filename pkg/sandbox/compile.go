package sandbox

import (
	"bytes"
	"errors"
	"fmt"
	"os"
	"os/exec"
	"os/user"
	"path/filepath"
	"strconv"
	"syscall"
	"time"
)

// CompileTimeLimit 设置编译超时时间，单位秒
const CompileTimeLimit = 20

// Compile 编译源码
// basedir 工作目录
// code 代码
// lang 语言
// TODO: 并发编译会编译超时出错
func Compile(basedir string, code string, lang *Language) error {
	var stdout, stderr bytes.Buffer
	codeFilename := lang.CodeFileName
	if _, err := os.Stat(basedir); err != nil {
		err := os.MkdirAll(basedir, os.ModePerm)
		if err != nil {
			return err
		}
		_ = os.Chmod(basedir, os.ModePerm)
	}
	codeFile, err := os.Create(filepath.Join(basedir, codeFilename))
	if err != nil {
		return err
	}
	defer codeFile.Close()
	_, _ = codeFile.WriteString(code)

	// nobody 是 Linux 中的内置账户，是个低权限的普通用户，
	// 用来执行编译过程，以防止程序的 include 指令导致泄露
	u, err := user.Lookup("nobody")
	if err != nil {
		return err
	}
	uid, _ := strconv.Atoi(u.Uid)
	gid, _ := strconv.Atoi(u.Gid)
	cmd := exec.Command(lang.CompileCommand[0], lang.CompileCommand[1:]...)
	cmd.Stdout = &stdout
	cmd.Stderr = &stderr
	cmd.Dir = basedir
	cmd.SysProcAttr = &syscall.SysProcAttr{
		Setpgid: true,
		Credential: &syscall.Credential{
			Uid: uint32(uid),
			Gid: uint32(gid),
		},
	}

	time.AfterFunc(time.Duration(CompileTimeLimit)*time.Second, func() {
		_ = syscall.Kill(-cmd.Process.Pid, syscall.SIGKILL)
	})

	if err := cmd.Run(); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("stderr: %s\nerr: %s\n", stderr.String(), err.Error()))
		// err.Error() == "signal: killed" means compiler is killed by our timer.
		if err.Error() == "signal: killed" {
			return errors.New("compile timeout")
		}
		return errors.New(stderr.String())
	}

	return nil
}
