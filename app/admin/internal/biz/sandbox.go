package biz

import (
	"context"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-kratos/kratos/v2/middleware/recovery"
	"github.com/go-kratos/kratos/v2/registry"
	"github.com/go-kratos/kratos/v2/transport/grpc"

	sandboxV1 "jnoj/api/sandbox/v1"
)

// SandboxUsecase is a Sandbox usecase.
type SandboxUsecase struct {
	sandboxClient sandboxV1.SandboxServiceClient
	log           *log.Helper
}

// NewSandboxUsecase new a Submission usecase.
func NewSandboxUsecase(
	sandboxClient sandboxV1.SandboxServiceClient,
	logger log.Logger,
) *SandboxUsecase {
	return &SandboxUsecase{
		sandboxClient: sandboxClient,
		log:           log.NewHelper(logger),
	}
}

func NewSandboxClient(r registry.Discovery) sandboxV1.SandboxServiceClient {
	conn, err := grpc.DialInsecure(
		context.Background(),
		grpc.WithEndpoint("discovery:///jnoj.sandbox.service"),
		grpc.WithDiscovery(r),
		grpc.WithMiddleware(
			recovery.Recovery(),
		),
		grpc.WithTimeout(time.Second*60),
	)
	if err != nil {
		panic(err)
	}
	c := sandboxV1.NewSandboxServiceClient(conn)
	return c
}
