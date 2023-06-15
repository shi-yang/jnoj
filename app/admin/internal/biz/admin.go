package biz

import (
	"context"

	"github.com/go-kratos/kratos/v2/log"
)

// AdminRepo is a Admin repo.
type AdminRepo interface {
}

// AdminUsecase is a Admin usecase.
type AdminUsecase struct {
	log *log.Helper
}

// NewAdminUsecase new a Admin usecase.
func NewAdminUsecase(
	logger log.Logger,
) *AdminUsecase {
	return &AdminUsecase{
		log: log.NewHelper(logger),
	}
}

func (uc *AdminUsecase) ListServiceStatuses(ctx context.Context) {

}
