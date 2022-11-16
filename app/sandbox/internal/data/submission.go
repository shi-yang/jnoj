package data

import (
	"jnoj/app/sandbox/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
)

type submissionRepo struct {
	data *Data
	log  *log.Helper
}

// NewSubmissionRepo .
func NewSubmissionRepo(data *Data, logger log.Logger) biz.SubmissionRepo {
	return &submissionRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}
