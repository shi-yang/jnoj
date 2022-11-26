package biz

import (
	"context"
	"jnoj/pkg/sandbox"
	"os"
	"path/filepath"

	sandboxV1 "jnoj/api/sandbox/v1"

	"jnoj/app/sandbox/internal/conf"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/google/uuid"
)

// Sandbox is a Sandbox model.
type Sandbox struct {
	Hello string
}

// SandboxRepo is a Sandbox repo.
type SandboxRepo interface {
}

// SandboxUsecase is a Sandbox usecase.
type SandboxUsecase struct {
	repo SandboxRepo
	log  *log.Helper
	conf *conf.Sandbox
}

// NewSandboxUsecase new a Sandbox usecase.
func NewSandboxUsecase(c *conf.Sandbox, repo SandboxRepo, logger log.Logger) *SandboxUsecase {
	return &SandboxUsecase{repo: repo, conf: c, log: log.NewHelper(logger)}
}

// CreateSandbox creates a Sandbox, and returns the new Sandbox.
func (uc *SandboxUsecase) CreateSandbox(ctx context.Context, g *Sandbox) (*Sandbox, error) {
	uc.log.WithContext(ctx).Infof("CreateSandbox: %v", g.Hello)
	return nil, nil
}

func (uc *SandboxUsecase) Run(ctx context.Context, req *sandboxV1.RunRequest) (res *sandboxV1.RunResponse) {
	res = new(sandboxV1.RunResponse)
	u, _ := uuid.NewUUID()
	workDir := filepath.Join("/tmp/sandbox", u.String())
	// defer os.RemoveAll(workDir)
	uc.log.Info("Compile start...", workDir)
	err := sandbox.Compile(workDir, req.Source, &sandbox.Languages[req.Language])
	uc.log.Info("Compile done...", workDir)
	if err != nil {
		res.CompileMsg = err.Error()
		return
	}
	uc.log.Info("Run...", workDir)
	runRes := sandbox.Run(workDir, &sandbox.Languages[req.Language], []byte(req.Stdin), req.MemoryLimit, req.TimeLimit)
	uc.log.Info("Run done...", workDir)
	res.ExitCode = int32(runRes.ExitCode)
	res.Memory = runRes.Memory
	res.Time = runRes.Time
	res.Stdout = runRes.Stdout
	res.Stderr = runRes.Stderr
	res.ErrMsg = runRes.Err
	if req.CheckerSource != nil {
		err := sandbox.Compile(workDir, *req.CheckerSource, checkerLanguage)
		if err != nil {
			uc.log.Info("sandbox.Compile err:", err)
		}
		_ = os.WriteFile(filepath.Join(workDir, "user.stdout"), []byte(res.Stdout), 0444)
		_ = os.WriteFile(filepath.Join(workDir, "data.in"), []byte(req.Stdin), 0444)
		_ = os.WriteFile(filepath.Join(workDir, "data.out"), []byte(*req.Answer), 0444)
		checkerRes := sandbox.Run(workDir, checkerLanguage, []byte(""), 256, 2000)
		res.CheckerExitCode = int32(checkerRes.ExitCode)
		res.CheckerStdout = checkerRes.Stderr
	}
	return
}
