package service

import (
	"context"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
)

// SandboxService is a contest service.
type SandboxService struct {
	uc *biz.SandboxUsecase
}

// NewSandboxService new a sandbox service.
func NewSandboxService(uc *biz.SandboxUsecase) *SandboxService {
	return &SandboxService{uc: uc}
}

func (s *SandboxService) Run(ctx context.Context, req *v1.RunRequest) (*v1.RunResponse, error) {
	return s.uc.Run(ctx, req)
}
