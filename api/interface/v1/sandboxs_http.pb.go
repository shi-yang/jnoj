// Code generated by protoc-gen-go-http. DO NOT EDIT.
// versions:
// - protoc-gen-go-http v2.5.4
// - protoc             v3.19.4
// source: v1/sandboxs.proto

package v1

import (
	context "context"
	http "github.com/go-kratos/kratos/v2/transport/http"
	binding "github.com/go-kratos/kratos/v2/transport/http/binding"
	auth "jnoj/internal/middleware/auth"
)

// This is a compile-time assertion to ensure that this generated file
// is compatible with the kratos package it is being compiled against.
var _ = new(context.Context)
var _ = binding.EncodeURL

const _ = http.SupportPackageIsVersion1

// auth.
const OperationSandboxsServiceRun = "/jnoj.interface.v1.SandboxsService/Run"

type SandboxsServiceHTTPServer interface {
	Run(context.Context, *RunRequest) (*RunResponse, error)
}

func RegisterSandboxsServiceHTTPServer(s *http.Server, srv SandboxsServiceHTTPServer) {
	s.Use("/jnoj.interface.v1.SandboxService/Run", auth.User())
	r := s.Route("/")
	r.POST("/sandboxs", _SandboxsService_Run0_HTTP_Handler(srv))
}

func _SandboxsService_Run0_HTTP_Handler(srv SandboxsServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in RunRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationSandboxsServiceRun)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.Run(ctx, req.(*RunRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*RunResponse)
		return ctx.Result(200, reply)
	}
}

type SandboxsServiceHTTPClient interface {
	Run(ctx context.Context, req *RunRequest, opts ...http.CallOption) (rsp *RunResponse, err error)
}

type SandboxsServiceHTTPClientImpl struct {
	cc *http.Client
}

func NewSandboxsServiceHTTPClient(client *http.Client) SandboxsServiceHTTPClient {
	return &SandboxsServiceHTTPClientImpl{client}
}

func (c *SandboxsServiceHTTPClientImpl) Run(ctx context.Context, in *RunRequest, opts ...http.CallOption) (*RunResponse, error) {
	var out RunResponse
	pattern := "/sandboxs"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationSandboxsServiceRun))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}