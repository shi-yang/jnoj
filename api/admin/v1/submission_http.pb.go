// Code generated by protoc-gen-go-http. DO NOT EDIT.
// versions:
// - protoc-gen-go-http v2.5.4
// - protoc             v3.21.12
// source: v1/submission.proto

package v1

import (
	context "context"
	http "github.com/go-kratos/kratos/v2/transport/http"
	binding "github.com/go-kratos/kratos/v2/transport/http/binding"
	emptypb "google.golang.org/protobuf/types/known/emptypb"
	auth "jnoj/internal/middleware/auth"
)

// This is a compile-time assertion to ensure that this generated file
// is compatible with the kratos package it is being compiled against.
var _ = new(context.Context)
var _ = binding.EncodeURL

const _ = http.SupportPackageIsVersion1

// auth.
const OperationSubmissionServiceRejudge = "/jnoj.interface.v1.SubmissionService/Rejudge"

type SubmissionServiceHTTPServer interface {
	Rejudge(context.Context, *RejudgeRequest) (*emptypb.Empty, error)
}

func RegisterSubmissionServiceHTTPServer(s *http.Server, srv SubmissionServiceHTTPServer) {
	s.Use("/jnoj.interface.v1.ProblemService/Rejudge", auth.User())
	r := s.Route("/")
	r.POST("/rejudge", _SubmissionService_Rejudge0_HTTP_Handler(srv))
}

func _SubmissionService_Rejudge0_HTTP_Handler(srv SubmissionServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in RejudgeRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationSubmissionServiceRejudge)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.Rejudge(ctx, req.(*RejudgeRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*emptypb.Empty)
		return ctx.Result(200, reply)
	}
}

type SubmissionServiceHTTPClient interface {
	Rejudge(ctx context.Context, req *RejudgeRequest, opts ...http.CallOption) (rsp *emptypb.Empty, err error)
}

type SubmissionServiceHTTPClientImpl struct {
	cc *http.Client
}

func NewSubmissionServiceHTTPClient(client *http.Client) SubmissionServiceHTTPClient {
	return &SubmissionServiceHTTPClientImpl{client}
}

func (c *SubmissionServiceHTTPClientImpl) Rejudge(ctx context.Context, in *RejudgeRequest, opts ...http.CallOption) (*emptypb.Empty, error) {
	var out emptypb.Empty
	pattern := "/rejudge"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationSubmissionServiceRejudge))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}