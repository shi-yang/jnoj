package service

import (
	"context"

	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
)

// AdminService is a admin service.
type AdminService struct {
	log *log.Helper
	uc  *biz.AdminUsecase
}

// NewAdminService new a admin service.
func NewAdminService(uc *biz.AdminUsecase, logger log.Logger) *AdminService {
	return &AdminService{uc: uc, log: log.NewHelper(logger)}
}

func (s AdminService) ListServiceStatuses(ctx context.Context, req *v1.ListServiceStatusesRequest) (*v1.ListServiceStatusesResponse, error) {
	resp := s.uc.ListServiceStatuses(ctx)
	return resp, nil
}
