package biz

import (
	"context"
	"encoding/json"
	"strings"
	"time"

	"jnoj/app/interface/internal/conf"

	consul "github.com/go-kratos/consul/registry"
	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-kratos/kratos/v2/middleware/recovery"
	"github.com/go-kratos/kratos/v2/registry"
	"github.com/go-kratos/kratos/v2/transport/grpc"
	consulAPI "github.com/hashicorp/consul/api"

	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
)

// SandboxUsecase is a Sandbox usecase.
type SandboxUsecase struct {
	sandboxClient sandboxV1.SandboxServiceClient
	problemRepo   ProblemRepo
	log           *log.Helper
}

// NewSandboxUsecase new a Submission usecase.
func NewSandboxUsecase(
	sandboxClient sandboxV1.SandboxServiceClient,
	problemRepo ProblemRepo,
	logger log.Logger,
) *SandboxUsecase {
	return &SandboxUsecase{
		sandboxClient: sandboxClient,
		problemRepo:   problemRepo,
		log:           log.NewHelper(logger),
	}
}

func (uc *SandboxUsecase) Run(ctx context.Context, req *v1.RunRequest) (*v1.RunResponse, error) {
	runRequest := &sandboxV1.RunRequest{
		Stdin:       req.Stdin,
		Source:      req.Source,
		Language:    int32(req.Language),
		TimeLimit:   req.TimeLimit,
		MemoryLimit: req.MemoryLimit,
	}
	// 处理函数题的提交
	if req.LanguageId != nil {
		file, err := uc.problemRepo.GetProblemFile(ctx, &ProblemFile{
			ID:       int(*req.LanguageId),
			Language: int(req.Language),
		})
		if err != nil {
			return nil, v1.ErrorNotFound(err.Error())
		}
		var lang ProblemLanguage
		if err := json.Unmarshal([]byte(file.Content), &lang); err != nil {
			return nil, v1.ErrorBadRequest(err.Error())
		}
		// 替换 @@@
		runRequest.Source = strings.ReplaceAll(lang.MainContent, "@@@", req.Source)
	}
	res, err := uc.sandboxClient.Run(ctx, runRequest)
	if err != nil {
		return nil, err
	}
	results := make([]*v1.RunResult, 0)
	for _, v := range res.Result {
		results = append(results, &v1.RunResult{
			Stdout:   v.Stdout,
			Time:     v.Time,
			Memory:   v.Memory,
			ExitCode: v.ExitCode,
			ErrMsg:   v.ErrMsg,
		})
	}
	return &v1.RunResponse{Results: results, CompileMsg: res.CompileMsg}, nil
}

func NewDiscovery(conf *conf.Registry) registry.Discovery {
	c := consulAPI.DefaultConfig()
	c.Address = conf.Consul.Address
	c.Scheme = conf.Consul.Scheme
	cli, err := consulAPI.NewClient(c)
	if err != nil {
		panic(err)
	}
	r := consul.New(cli, consul.WithHealthCheck(false))
	return r
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
