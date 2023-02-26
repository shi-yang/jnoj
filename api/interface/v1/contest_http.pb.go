// Code generated by protoc-gen-go-http. DO NOT EDIT.
// versions:
// - protoc-gen-go-http v2.5.4
// - protoc             v3.21.12
// source: v1/contest.proto

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
// auth.
const OperationContestServiceCreateContest = "/jnoj.interface.v1.ContestService/CreateContest"
const OperationContestServiceCreateContestProblem = "/jnoj.interface.v1.ContestService/CreateContestProblem"
const OperationContestServiceCreateContestUser = "/jnoj.interface.v1.ContestService/CreateContestUser"
const OperationContestServiceDeleteContestProblem = "/jnoj.interface.v1.ContestService/DeleteContestProblem"
const OperationContestServiceGetContest = "/jnoj.interface.v1.ContestService/GetContest"
const OperationContestServiceGetContestProblem = "/jnoj.interface.v1.ContestService/GetContestProblem"
const OperationContestServiceGetContestProblemLanguage = "/jnoj.interface.v1.ContestService/GetContestProblemLanguage"
const OperationContestServiceListContestAllSubmissions = "/jnoj.interface.v1.ContestService/ListContestAllSubmissions"
const OperationContestServiceListContestProblemLanguages = "/jnoj.interface.v1.ContestService/ListContestProblemLanguages"
const OperationContestServiceListContestProblems = "/jnoj.interface.v1.ContestService/ListContestProblems"
const OperationContestServiceListContestSubmissions = "/jnoj.interface.v1.ContestService/ListContestSubmissions"
const OperationContestServiceListContestUsers = "/jnoj.interface.v1.ContestService/ListContestUsers"
const OperationContestServiceListContests = "/jnoj.interface.v1.ContestService/ListContests"
const OperationContestServiceUpdateContest = "/jnoj.interface.v1.ContestService/UpdateContest"
const OperationContestServiceUpdateContestUser = "/jnoj.interface.v1.ContestService/UpdateContestUser"

type ContestServiceHTTPServer interface {
	CreateContest(context.Context, *CreateContestRequest) (*Contest, error)
	CreateContestProblem(context.Context, *CreateContestProblemRequest) (*ContestProblem, error)
	CreateContestUser(context.Context, *CreateContestUserRequest) (*ContestUser, error)
	DeleteContestProblem(context.Context, *DeleteContestProblemRequest) (*emptypb.Empty, error)
	GetContest(context.Context, *GetContestRequest) (*Contest, error)
	GetContestProblem(context.Context, *GetContestProblemRequest) (*ContestProblem, error)
	GetContestProblemLanguage(context.Context, *GetContestProblemLanguageRequest) (*ContestProblemLanguage, error)
	ListContestAllSubmissions(context.Context, *ListContestAllSubmissionsRequest) (*ListContestAllSubmissionsResponse, error)
	ListContestProblemLanguages(context.Context, *ListContestProblemLanguagesRequest) (*ListContestProblemLanguagesResponse, error)
	ListContestProblems(context.Context, *ListContestProblemsRequest) (*ListContestProblemsResponse, error)
	ListContestSubmissions(context.Context, *ListContestSubmissionsRequest) (*ListContestSubmissionsResponse, error)
	ListContestUsers(context.Context, *ListContestUsersRequest) (*ListContestUsersResponse, error)
	ListContests(context.Context, *ListContestsRequest) (*ListContestsResponse, error)
	UpdateContest(context.Context, *UpdateContestRequest) (*Contest, error)
	UpdateContestUser(context.Context, *UpdateContestUserRequest) (*ContestUser, error)
}

func RegisterContestServiceHTTPServer(s *http.Server, srv ContestServiceHTTPServer) {
	s.Use("/jnoj.interface.v1.ContestService/CreateContest", auth.User())
	s.Use("/jnoj.interface.v1.ContestService/UpdateContest", auth.User())
	s.Use("/jnoj.interface.v1.ContestService/*Delete*", auth.User())
	s.Use("/jnoj.interface.v1.ContestService/ListContests", auth.Guest())
	s.Use("/jnoj.interface.v1.ContestService/GetContest", auth.Guest())
	s.Use("/jnoj.interface.v1.ContestService/ListContestProblems", auth.Guest())
	s.Use("/jnoj.interface.v1.ContestService/GetContestProblem", auth.Guest())
	s.Use("/jnoj.interface.v1.ContestService/CreateContestProblem", auth.User())
	s.Use("/jnoj.interface.v1.ContestService/CreateContestUser", auth.User())
	s.Use("/jnoj.interface.v1.ContestService/UpdateContestUser", auth.User())
	s.Use("/jnoj.interface.v1.ContestService/ListContestUsers", auth.Guest())
	s.Use("/jnoj.interface.v1.ContestService/ListContestSubmissions", auth.Guest())
	s.Use("/jnoj.interface.v1.ContestService/ListContestAllSubmissions", auth.Guest())
	s.Use("/jnoj.interface.v1.ContestService/ListContestProblemLanguages", auth.Guest())
	s.Use("/jnoj.interface.v1.ContestService/GetContestProblemLanguage", auth.Guest())
	r := s.Route("/")
	r.GET("/contests", _ContestService_ListContests0_HTTP_Handler(srv))
	r.GET("/contests/{id}", _ContestService_GetContest0_HTTP_Handler(srv))
	r.POST("/contests", _ContestService_CreateContest0_HTTP_Handler(srv))
	r.PUT("/contests/{id}", _ContestService_UpdateContest0_HTTP_Handler(srv))
	r.GET("/contests/{id}/problems", _ContestService_ListContestProblems0_HTTP_Handler(srv))
	r.GET("/contests/{id}/problems/{number}", _ContestService_GetContestProblem0_HTTP_Handler(srv))
	r.POST("/contests/{id}/problems", _ContestService_CreateContestProblem0_HTTP_Handler(srv))
	r.DELETE("/contests/{id}/problems/{number}", _ContestService_DeleteContestProblem0_HTTP_Handler(srv))
	r.GET("/contests/{id}/problems/{number}/languages", _ContestService_ListContestProblemLanguages0_HTTP_Handler(srv))
	r.GET("/contests/{id}/problems/{number}/languages/{language}", _ContestService_GetContestProblemLanguage0_HTTP_Handler(srv))
	r.GET("/contests/{contest_id}/users", _ContestService_ListContestUsers0_HTTP_Handler(srv))
	r.POST("/contests/{contest_id}/users", _ContestService_CreateContestUser0_HTTP_Handler(srv))
	r.PUT("/contests/{contest_id}/users", _ContestService_UpdateContestUser0_HTTP_Handler(srv))
	r.GET("/contests/{contest_id}/all_submissions", _ContestService_ListContestAllSubmissions0_HTTP_Handler(srv))
	r.GET("/contests/{contest_id}/submissions", _ContestService_ListContestSubmissions0_HTTP_Handler(srv))
}

func _ContestService_ListContests0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListContestsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceListContests)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListContests(ctx, req.(*ListContestsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListContestsResponse)
		return ctx.Result(200, reply)
	}
}

func _ContestService_GetContest0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetContestRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceGetContest)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetContest(ctx, req.(*GetContestRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*Contest)
		return ctx.Result(200, reply)
	}
}

func _ContestService_CreateContest0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateContestRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceCreateContest)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateContest(ctx, req.(*CreateContestRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*Contest)
		return ctx.Result(200, reply)
	}
}

func _ContestService_UpdateContest0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateContestRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceUpdateContest)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateContest(ctx, req.(*UpdateContestRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*Contest)
		return ctx.Result(200, reply)
	}
}

func _ContestService_ListContestProblems0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListContestProblemsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceListContestProblems)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListContestProblems(ctx, req.(*ListContestProblemsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListContestProblemsResponse)
		return ctx.Result(200, reply)
	}
}

func _ContestService_GetContestProblem0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetContestProblemRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceGetContestProblem)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetContestProblem(ctx, req.(*GetContestProblemRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ContestProblem)
		return ctx.Result(200, reply)
	}
}

func _ContestService_CreateContestProblem0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateContestProblemRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceCreateContestProblem)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateContestProblem(ctx, req.(*CreateContestProblemRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ContestProblem)
		return ctx.Result(200, reply)
	}
}

func _ContestService_DeleteContestProblem0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in DeleteContestProblemRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceDeleteContestProblem)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.DeleteContestProblem(ctx, req.(*DeleteContestProblemRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*emptypb.Empty)
		return ctx.Result(200, reply)
	}
}

func _ContestService_ListContestProblemLanguages0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListContestProblemLanguagesRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceListContestProblemLanguages)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListContestProblemLanguages(ctx, req.(*ListContestProblemLanguagesRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListContestProblemLanguagesResponse)
		return ctx.Result(200, reply)
	}
}

func _ContestService_GetContestProblemLanguage0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetContestProblemLanguageRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceGetContestProblemLanguage)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetContestProblemLanguage(ctx, req.(*GetContestProblemLanguageRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ContestProblemLanguage)
		return ctx.Result(200, reply)
	}
}

func _ContestService_ListContestUsers0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListContestUsersRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceListContestUsers)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListContestUsers(ctx, req.(*ListContestUsersRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListContestUsersResponse)
		return ctx.Result(200, reply)
	}
}

func _ContestService_CreateContestUser0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateContestUserRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceCreateContestUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateContestUser(ctx, req.(*CreateContestUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ContestUser)
		return ctx.Result(200, reply)
	}
}

func _ContestService_UpdateContestUser0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateContestUserRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceUpdateContestUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateContestUser(ctx, req.(*UpdateContestUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ContestUser)
		return ctx.Result(200, reply)
	}
}

func _ContestService_ListContestAllSubmissions0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListContestAllSubmissionsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceListContestAllSubmissions)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListContestAllSubmissions(ctx, req.(*ListContestAllSubmissionsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListContestAllSubmissionsResponse)
		return ctx.Result(200, reply)
	}
}

func _ContestService_ListContestSubmissions0_HTTP_Handler(srv ContestServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListContestSubmissionsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationContestServiceListContestSubmissions)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListContestSubmissions(ctx, req.(*ListContestSubmissionsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListContestSubmissionsResponse)
		return ctx.Result(200, reply)
	}
}

type ContestServiceHTTPClient interface {
	CreateContest(ctx context.Context, req *CreateContestRequest, opts ...http.CallOption) (rsp *Contest, err error)
	CreateContestProblem(ctx context.Context, req *CreateContestProblemRequest, opts ...http.CallOption) (rsp *ContestProblem, err error)
	CreateContestUser(ctx context.Context, req *CreateContestUserRequest, opts ...http.CallOption) (rsp *ContestUser, err error)
	DeleteContestProblem(ctx context.Context, req *DeleteContestProblemRequest, opts ...http.CallOption) (rsp *emptypb.Empty, err error)
	GetContest(ctx context.Context, req *GetContestRequest, opts ...http.CallOption) (rsp *Contest, err error)
	GetContestProblem(ctx context.Context, req *GetContestProblemRequest, opts ...http.CallOption) (rsp *ContestProblem, err error)
	GetContestProblemLanguage(ctx context.Context, req *GetContestProblemLanguageRequest, opts ...http.CallOption) (rsp *ContestProblemLanguage, err error)
	ListContestAllSubmissions(ctx context.Context, req *ListContestAllSubmissionsRequest, opts ...http.CallOption) (rsp *ListContestAllSubmissionsResponse, err error)
	ListContestProblemLanguages(ctx context.Context, req *ListContestProblemLanguagesRequest, opts ...http.CallOption) (rsp *ListContestProblemLanguagesResponse, err error)
	ListContestProblems(ctx context.Context, req *ListContestProblemsRequest, opts ...http.CallOption) (rsp *ListContestProblemsResponse, err error)
	ListContestSubmissions(ctx context.Context, req *ListContestSubmissionsRequest, opts ...http.CallOption) (rsp *ListContestSubmissionsResponse, err error)
	ListContestUsers(ctx context.Context, req *ListContestUsersRequest, opts ...http.CallOption) (rsp *ListContestUsersResponse, err error)
	ListContests(ctx context.Context, req *ListContestsRequest, opts ...http.CallOption) (rsp *ListContestsResponse, err error)
	UpdateContest(ctx context.Context, req *UpdateContestRequest, opts ...http.CallOption) (rsp *Contest, err error)
	UpdateContestUser(ctx context.Context, req *UpdateContestUserRequest, opts ...http.CallOption) (rsp *ContestUser, err error)
}

type ContestServiceHTTPClientImpl struct {
	cc *http.Client
}

func NewContestServiceHTTPClient(client *http.Client) ContestServiceHTTPClient {
	return &ContestServiceHTTPClientImpl{client}
}

func (c *ContestServiceHTTPClientImpl) CreateContest(ctx context.Context, in *CreateContestRequest, opts ...http.CallOption) (*Contest, error) {
	var out Contest
	pattern := "/contests"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationContestServiceCreateContest))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) CreateContestProblem(ctx context.Context, in *CreateContestProblemRequest, opts ...http.CallOption) (*ContestProblem, error) {
	var out ContestProblem
	pattern := "/contests/{id}/problems"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationContestServiceCreateContestProblem))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) CreateContestUser(ctx context.Context, in *CreateContestUserRequest, opts ...http.CallOption) (*ContestUser, error) {
	var out ContestUser
	pattern := "/contests/{contest_id}/users"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationContestServiceCreateContestUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) DeleteContestProblem(ctx context.Context, in *DeleteContestProblemRequest, opts ...http.CallOption) (*emptypb.Empty, error) {
	var out emptypb.Empty
	pattern := "/contests/{id}/problems/{number}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceDeleteContestProblem))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "DELETE", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) GetContest(ctx context.Context, in *GetContestRequest, opts ...http.CallOption) (*Contest, error) {
	var out Contest
	pattern := "/contests/{id}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceGetContest))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) GetContestProblem(ctx context.Context, in *GetContestProblemRequest, opts ...http.CallOption) (*ContestProblem, error) {
	var out ContestProblem
	pattern := "/contests/{id}/problems/{number}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceGetContestProblem))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) GetContestProblemLanguage(ctx context.Context, in *GetContestProblemLanguageRequest, opts ...http.CallOption) (*ContestProblemLanguage, error) {
	var out ContestProblemLanguage
	pattern := "/contests/{id}/problems/{number}/languages/{language}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceGetContestProblemLanguage))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) ListContestAllSubmissions(ctx context.Context, in *ListContestAllSubmissionsRequest, opts ...http.CallOption) (*ListContestAllSubmissionsResponse, error) {
	var out ListContestAllSubmissionsResponse
	pattern := "/contests/{contest_id}/all_submissions"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceListContestAllSubmissions))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) ListContestProblemLanguages(ctx context.Context, in *ListContestProblemLanguagesRequest, opts ...http.CallOption) (*ListContestProblemLanguagesResponse, error) {
	var out ListContestProblemLanguagesResponse
	pattern := "/contests/{id}/problems/{number}/languages"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceListContestProblemLanguages))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) ListContestProblems(ctx context.Context, in *ListContestProblemsRequest, opts ...http.CallOption) (*ListContestProblemsResponse, error) {
	var out ListContestProblemsResponse
	pattern := "/contests/{id}/problems"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceListContestProblems))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) ListContestSubmissions(ctx context.Context, in *ListContestSubmissionsRequest, opts ...http.CallOption) (*ListContestSubmissionsResponse, error) {
	var out ListContestSubmissionsResponse
	pattern := "/contests/{contest_id}/submissions"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceListContestSubmissions))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) ListContestUsers(ctx context.Context, in *ListContestUsersRequest, opts ...http.CallOption) (*ListContestUsersResponse, error) {
	var out ListContestUsersResponse
	pattern := "/contests/{contest_id}/users"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceListContestUsers))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) ListContests(ctx context.Context, in *ListContestsRequest, opts ...http.CallOption) (*ListContestsResponse, error) {
	var out ListContestsResponse
	pattern := "/contests"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationContestServiceListContests))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) UpdateContest(ctx context.Context, in *UpdateContestRequest, opts ...http.CallOption) (*Contest, error) {
	var out Contest
	pattern := "/contests/{id}"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationContestServiceUpdateContest))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ContestServiceHTTPClientImpl) UpdateContestUser(ctx context.Context, in *UpdateContestUserRequest, opts ...http.CallOption) (*ContestUser, error) {
	var out ContestUser
	pattern := "/contests/{contest_id}/users"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationContestServiceUpdateContestUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}
