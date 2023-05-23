package service

import (
	"context"

	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
	"google.golang.org/protobuf/types/known/emptypb"
)

// SubmissionService is a user service.
type SubmissionService struct {
	uc  *biz.SubmissionUsecase
	log *log.Helper
}

// NewSubmissionService new a user service.
func NewSubmissionService(uc *biz.SubmissionUsecase, logger log.Logger) *SubmissionService {
	return &SubmissionService{uc: uc, log: log.NewHelper(logger)}
}

// Rejudge 重新测评
func (s SubmissionService) Rejudge(ctx context.Context, req *v1.RejudgeRequest) (*emptypb.Empty, error) {
	s.uc.Rejudge(ctx, int(req.GetContestId()), int(req.GetProblemId()), int(req.GetSubmissionId()))
	return nil, nil
}
