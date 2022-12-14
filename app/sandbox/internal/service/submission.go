package service

import (
	"context"
	v1 "jnoj/api/sandbox/v1"
)

func (s *SandboxService) RunSubmission(ctx context.Context, req *v1.RunSubmissionRequest) (*v1.RunSubmissionResponse, error) {
	s.suc.RunSubmission(ctx, int(req.SubmissionId))
	return nil, nil
}
