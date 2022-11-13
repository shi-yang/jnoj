package biz

import (
	"context"
	"jnoj/pkg/sandbox"
	"path/filepath"

	sandboxV1 "jnoj/api/sandbox/v1"

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
}

// NewSandboxUsecase new a Sandbox usecase.
func NewSandboxUsecase(repo SandboxRepo, logger log.Logger) *SandboxUsecase {
	return &SandboxUsecase{repo: repo, log: log.NewHelper(logger)}
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
	err := sandbox.Compile(workDir, req.Source, &sandbox.Languages[req.Language])
	if err != nil {
		res.CompileMsg = err.Error()
		return
	}
	runRes := sandbox.Run(workDir, &sandbox.Languages[req.Language], []byte(req.Stdin), req.MemoryLimit, req.TimeLimit)
	res.ExitCode = int32(runRes.ExitCode)
	res.Memory = runRes.Memory
	res.Time = runRes.Time
	res.Stdout = runRes.Stdout
	res.Stderr = runRes.Stderr
	res.ErrMsg = runRes.Err
	return
}
