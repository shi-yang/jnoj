package server

import (
	redisRegistry "jnoj/internal/contrib/registry/redis"

	"github.com/go-kratos/kratos/v2/registry"
	"github.com/google/wire"

	"jnoj/app/sandbox/internal/conf"
)

// ProviderSet is server providers.
var ProviderSet = wire.NewSet(NewGRPCServer, NewRegistrar)

func NewRegistrar(conf *conf.Data) registry.Registrar {
	return redisRegistry.New(conf.Redis.Addr, redisRegistry.WithHealthCheck(true))
}
