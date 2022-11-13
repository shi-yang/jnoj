package data

import (
	"jnoj/app/sandbox/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
)

type sandboxRepo struct {
	data *Data
	log  *log.Helper
}

// NewSandboxRepo .
func NewSandboxRepo(data *Data, logger log.Logger) biz.SandboxRepo {
	return &sandboxRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}
