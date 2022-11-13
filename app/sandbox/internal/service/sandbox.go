package service

import (
	"context"
	v1 "jnoj/api/sandbox/v1"
	"jnoj/app/sandbox/internal/biz"
)

// SandboxService is a sandbox service.
type SandboxService struct {
	v1.UnimplementedSandboxServiceServer

	uc *biz.SandboxUsecase
}

// NewSandboxService new a sandbox service.
func NewSandboxService(uc *biz.SandboxUsecase) *SandboxService {
	return &SandboxService{uc: uc}
}

func (s *SandboxService) Run(ctx context.Context, req *v1.RunRequest) (*v1.RunResponse, error) {
	res := s.uc.Run(ctx, req)
	return res, nil
}
