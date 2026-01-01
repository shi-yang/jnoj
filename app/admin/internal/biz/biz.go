package biz

import (
	"jnoj/app/admin/internal/conf"

	redisRegistry "jnoj/internal/contrib/registry/redis"

	"github.com/go-kratos/kratos/v2/registry"
	"github.com/google/wire"
)

// ProviderSet is biz providers.
var ProviderSet = wire.NewSet(
	NewUserUsecase,
	NewSubmissionUsecase,
	NewSandboxClient,
	NewDiscovery,
	NewSandboxUsecase,
	NewAdminUsecase,
)

func NewDiscovery(conf *conf.Data) registry.Discovery {
	return redisRegistry.New(conf.Redis.Addr, redisRegistry.WithHealthCheck(false))
}
