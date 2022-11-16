package biz

import (
	"github.com/go-kratos/kratos/v2/log"
)

// SubmissionRepo is a Submission repo.
type SubmissionRepo interface {
}

// SubmissionUsecase is a Submission usecase.
type SubmissionUsecase struct {
	repo SubmissionRepo
	log  *log.Helper
}

// NewSubmissionUsecase new a Submission usecase.
func NewSubmissionUsecase(repo SubmissionRepo, logger log.Logger) *SubmissionUsecase {
	return &SubmissionUsecase{repo: repo, log: log.NewHelper(logger)}
}
