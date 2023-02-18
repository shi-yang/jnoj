package service

import (
	"context"
	v1 "jnoj/api/sandbox/v1"
)

func (s *SandboxService) RunSubmission(ctx context.Context, req *v1.RunSubmissionRequest) (*v1.RunSubmissionResponse, error) {
	return &v1.RunSubmissionResponse{}, s.suc.CreateSubmission(ctx, int(req.SubmissionId))
}
