package service

import (
	"context"
	v1 "jnoj/api/sandbox/v1"
	"jnoj/app/sandbox/internal/biz"
)

// SandboxService is a sandbox service.
type SandboxService struct {
	v1.UnimplementedSandboxServiceServer

	uc *biz.GreeterUsecase
}

// NewSandboxService new a sandbox service.
func NewSandboxService(uc *biz.GreeterUsecase) *SandboxService {
	return &SandboxService{uc: uc}
}

func (s *SandboxService) Run(ctx context.Context, req *v1.RunRequest) (*v1.RunResponse, error) {
	return nil, nil
}
