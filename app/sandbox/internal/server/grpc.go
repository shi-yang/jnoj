package server

import (
	v1 "jnoj/api/sandbox/v1"
	"jnoj/app/sandbox/internal/conf"
	"jnoj/app/sandbox/internal/service"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-kratos/kratos/v2/middleware/recovery"
	kgrpc "github.com/go-kratos/kratos/v2/transport/grpc"
	"google.golang.org/grpc"
)

// NewGRPCServer new a gRPC server.
func NewGRPCServer(c *conf.Server, sandbox *service.SandboxService, logger log.Logger) *kgrpc.Server {
	var opts = []kgrpc.ServerOption{
		kgrpc.Middleware(
			recovery.Recovery(),
		),
		kgrpc.Options(grpc.MaxRecvMsgSize(500 * 1024 * 1024)),
	}
	if c.Grpc.Network != "" {
		opts = append(opts, kgrpc.Network(c.Grpc.Network))
	}
	if c.Grpc.Addr != "" {
		opts = append(opts, kgrpc.Address(c.Grpc.Addr))
	}
	if c.Grpc.Timeout != nil {
		opts = append(opts, kgrpc.Timeout(c.Grpc.Timeout.AsDuration()))
	}
	srv := kgrpc.NewServer(opts...)
	v1.RegisterSandboxServiceServer(srv, sandbox)
	return srv
}
