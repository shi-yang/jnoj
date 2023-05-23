// Code generated by protoc-gen-go-http. DO NOT EDIT.
// versions:
// - protoc-gen-go-http v2.5.4
// - protoc             v3.21.12
// source: v1/problem.proto

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
const OperationProblemServiceListProblems = "/jnoj.interface.v1.ProblemService/ListProblems"

type ProblemServiceHTTPServer interface {
	ListProblems(context.Context, *ListProblemsRequest) (*ListProblemsResponse, error)
}

func RegisterProblemServiceHTTPServer(s *http.Server, srv ProblemServiceHTTPServer) {
	s.Use("/jnoj.interface.v1.ProblemService/ListProblems", auth.User())
	r := s.Route("/")
	r.GET("/problems", _ProblemService_ListProblems0_HTTP_Handler(srv))
}

func _ProblemService_ListProblems0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListProblemsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceListProblems)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListProblems(ctx, req.(*ListProblemsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListProblemsResponse)
		return ctx.Result(200, reply)
	}
}

type ProblemServiceHTTPClient interface {
	ListProblems(ctx context.Context, req *ListProblemsRequest, opts ...http.CallOption) (rsp *ListProblemsResponse, err error)
}

type ProblemServiceHTTPClientImpl struct {
	cc *http.Client
}

func NewProblemServiceHTTPClient(client *http.Client) ProblemServiceHTTPClient {
	return &ProblemServiceHTTPClientImpl{client}
}

func (c *ProblemServiceHTTPClientImpl) ListProblems(ctx context.Context, in *ListProblemsRequest, opts ...http.CallOption) (*ListProblemsResponse, error) {
	var out ListProblemsResponse
	pattern := "/problems"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceListProblems))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}