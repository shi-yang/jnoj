// Code generated by protoc-gen-go-http. DO NOT EDIT.
// versions:
// - protoc-gen-go-http v2.5.2
// - protoc             v3.19.4
// source: v1/problem.proto

package v1

import (
	context "context"
	http "github.com/go-kratos/kratos/v2/transport/http"
	binding "github.com/go-kratos/kratos/v2/transport/http/binding"
)

// This is a compile-time assertion to ensure that this generated file
// is compatible with the kratos package it is being compiled against.
var _ = new(context.Context)
var _ = binding.EncodeURL

const _ = http.SupportPackageIsVersion1

const OperationProblemServiceCreateProblem = "/jnoj.interface.v1.ProblemService/CreateProblem"
const OperationProblemServiceCreateProblemChecker = "/jnoj.interface.v1.ProblemService/CreateProblemChecker"
const OperationProblemServiceCreateProblemSolution = "/jnoj.interface.v1.ProblemService/CreateProblemSolution"
const OperationProblemServiceCreateProblemStatement = "/jnoj.interface.v1.ProblemService/CreateProblemStatement"
const OperationProblemServiceCreateProblemTest = "/jnoj.interface.v1.ProblemService/CreateProblemTest"
const OperationProblemServiceDeleteProblemSolution = "/jnoj.interface.v1.ProblemService/DeleteProblemSolution"
const OperationProblemServiceDeleteProblemStatement = "/jnoj.interface.v1.ProblemService/DeleteProblemStatement"
const OperationProblemServiceDeleteProblemTest = "/jnoj.interface.v1.ProblemService/DeleteProblemTest"
const OperationProblemServiceGetProblem = "/jnoj.interface.v1.ProblemService/GetProblem"
const OperationProblemServiceGetProblemChecker = "/jnoj.interface.v1.ProblemService/GetProblemChecker"
const OperationProblemServiceGetProblemSolution = "/jnoj.interface.v1.ProblemService/GetProblemSolution"
const OperationProblemServiceGetProblemStatement = "/jnoj.interface.v1.ProblemService/GetProblemStatement"
const OperationProblemServiceGetProblemTest = "/jnoj.interface.v1.ProblemService/GetProblemTest"
const OperationProblemServiceListProblemCheckers = "/jnoj.interface.v1.ProblemService/ListProblemCheckers"
const OperationProblemServiceListProblemSolutions = "/jnoj.interface.v1.ProblemService/ListProblemSolutions"
const OperationProblemServiceListProblemStatements = "/jnoj.interface.v1.ProblemService/ListProblemStatements"
const OperationProblemServiceListProblemTests = "/jnoj.interface.v1.ProblemService/ListProblemTests"
const OperationProblemServiceListProblems = "/jnoj.interface.v1.ProblemService/ListProblems"
const OperationProblemServiceUpdateProblem = "/jnoj.interface.v1.ProblemService/UpdateProblem"
const OperationProblemServiceUpdateProblemChecker = "/jnoj.interface.v1.ProblemService/UpdateProblemChecker"
const OperationProblemServiceUpdateProblemSolution = "/jnoj.interface.v1.ProblemService/UpdateProblemSolution"
const OperationProblemServiceUpdateProblemStatement = "/jnoj.interface.v1.ProblemService/UpdateProblemStatement"
const OperationProblemServiceUpdateProblemTest = "/jnoj.interface.v1.ProblemService/UpdateProblemTest"

type ProblemServiceHTTPServer interface {
	CreateProblem(context.Context, *CreateProblemRequest) (*CreateProblemResponse, error)
	CreateProblemChecker(context.Context, *CreateProblemCheckerRequest) (*ProblemChecker, error)
	CreateProblemSolution(context.Context, *CreateProblemSolutionRequest) (*ProblemSolution, error)
	CreateProblemStatement(context.Context, *CreateProblemStatementRequest) (*ProblemStatement, error)
	CreateProblemTest(context.Context, *CreateProblemTestRequest) (*ProblemTest, error)
	DeleteProblemSolution(context.Context, *DeleteProblemSolutionRequest) (*ProblemSolution, error)
	DeleteProblemStatement(context.Context, *DeleteProblemStatementRequest) (*ProblemStatement, error)
	DeleteProblemTest(context.Context, *DeleteProblemTestRequest) (*ProblemTest, error)
	GetProblem(context.Context, *GetProblemRequest) (*Problem, error)
	GetProblemChecker(context.Context, *GetProblemCheckerRequest) (*ProblemChecker, error)
	GetProblemSolution(context.Context, *GetProblemSolutionRequest) (*ProblemSolution, error)
	GetProblemStatement(context.Context, *GetProblemStatementRequest) (*ProblemStatement, error)
	GetProblemTest(context.Context, *GetProblemTestRequest) (*ProblemTest, error)
	ListProblemCheckers(context.Context, *ListProblemCheckersRequest) (*ListProblemCheckersResponse, error)
	ListProblemSolutions(context.Context, *ListProblemSolutionsRequest) (*ListProblemSolutionsResponse, error)
	ListProblemStatements(context.Context, *ListProblemStatementsRequest) (*ListProblemStatementsResponse, error)
	ListProblemTests(context.Context, *ListProblemTestsRequest) (*ListProblemTestsResponse, error)
	ListProblems(context.Context, *ListProblemsRequest) (*ListProblemsResponse, error)
	UpdateProblem(context.Context, *UpdateProblemRequest) (*Problem, error)
	UpdateProblemChecker(context.Context, *UpdateProblemCheckerRequest) (*ProblemChecker, error)
	UpdateProblemSolution(context.Context, *UpdateProblemSolutionRequest) (*ProblemSolution, error)
	UpdateProblemStatement(context.Context, *UpdateProblemStatementRequest) (*ProblemStatement, error)
	UpdateProblemTest(context.Context, *UpdateProblemTestRequest) (*ProblemTest, error)
}

func RegisterProblemServiceHTTPServer(s *http.Server, srv ProblemServiceHTTPServer) {
	r := s.Route("/")
	r.GET("/problems", _ProblemService_ListProblems0_HTTP_Handler(srv))
	r.GET("/problems/{id}", _ProblemService_GetProblem0_HTTP_Handler(srv))
	r.POST("/problems", _ProblemService_CreateProblem0_HTTP_Handler(srv))
	r.POST("/problems", _ProblemService_UpdateProblem0_HTTP_Handler(srv))
	r.GET("/problems/{id}/statements", _ProblemService_ListProblemStatements0_HTTP_Handler(srv))
	r.GET("/problems/{id}/statements", _ProblemService_GetProblemStatement0_HTTP_Handler(srv))
	r.POST("/problems/{id}/statements", _ProblemService_CreateProblemStatement0_HTTP_Handler(srv))
	r.PUT("/problems/{id}/statements", _ProblemService_UpdateProblemStatement0_HTTP_Handler(srv))
	r.DELETE("/problems/{id}/statements", _ProblemService_DeleteProblemStatement0_HTTP_Handler(srv))
	r.GET("/problems/{id}/checkers", _ProblemService_ListProblemCheckers0_HTTP_Handler(srv))
	r.GET("/problems/{id}/checkers", _ProblemService_GetProblemChecker0_HTTP_Handler(srv))
	r.POST("/problems/{id}/checkers", _ProblemService_CreateProblemChecker0_HTTP_Handler(srv))
	r.PUT("/problems/{id}/checkers", _ProblemService_UpdateProblemChecker0_HTTP_Handler(srv))
	r.GET("/problems/{id}/tests", _ProblemService_ListProblemTests0_HTTP_Handler(srv))
	r.GET("/problems/{id}/tests", _ProblemService_GetProblemTest0_HTTP_Handler(srv))
	r.POST("/problems/{id}/tests", _ProblemService_CreateProblemTest0_HTTP_Handler(srv))
	r.PUT("/problems/{id}/tests", _ProblemService_UpdateProblemTest0_HTTP_Handler(srv))
	r.DELETE("/problems/{id}/tests", _ProblemService_DeleteProblemTest0_HTTP_Handler(srv))
	r.GET("/problems/{id}/solution", _ProblemService_ListProblemSolutions0_HTTP_Handler(srv))
	r.GET("/problems/{id}/solution", _ProblemService_GetProblemSolution0_HTTP_Handler(srv))
	r.POST("/problems/{id}/solution", _ProblemService_CreateProblemSolution0_HTTP_Handler(srv))
	r.PUT("/problems/{id}/solution", _ProblemService_UpdateProblemSolution0_HTTP_Handler(srv))
	r.DELETE("/problems/{id}/solution", _ProblemService_DeleteProblemSolution0_HTTP_Handler(srv))
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

func _ProblemService_GetProblem0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetProblemRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceGetProblem)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetProblem(ctx, req.(*GetProblemRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*Problem)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_CreateProblem0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateProblemRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceCreateProblem)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateProblem(ctx, req.(*CreateProblemRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*CreateProblemResponse)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_UpdateProblem0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateProblemRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceUpdateProblem)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateProblem(ctx, req.(*UpdateProblemRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*Problem)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_ListProblemStatements0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListProblemStatementsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceListProblemStatements)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListProblemStatements(ctx, req.(*ListProblemStatementsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListProblemStatementsResponse)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_GetProblemStatement0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetProblemStatementRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceGetProblemStatement)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetProblemStatement(ctx, req.(*GetProblemStatementRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemStatement)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_CreateProblemStatement0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateProblemStatementRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceCreateProblemStatement)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateProblemStatement(ctx, req.(*CreateProblemStatementRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemStatement)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_UpdateProblemStatement0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateProblemStatementRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceUpdateProblemStatement)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateProblemStatement(ctx, req.(*UpdateProblemStatementRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemStatement)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_DeleteProblemStatement0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in DeleteProblemStatementRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceDeleteProblemStatement)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.DeleteProblemStatement(ctx, req.(*DeleteProblemStatementRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemStatement)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_ListProblemCheckers0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListProblemCheckersRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceListProblemCheckers)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListProblemCheckers(ctx, req.(*ListProblemCheckersRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListProblemCheckersResponse)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_GetProblemChecker0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetProblemCheckerRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceGetProblemChecker)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetProblemChecker(ctx, req.(*GetProblemCheckerRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemChecker)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_CreateProblemChecker0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateProblemCheckerRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceCreateProblemChecker)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateProblemChecker(ctx, req.(*CreateProblemCheckerRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemChecker)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_UpdateProblemChecker0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateProblemCheckerRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceUpdateProblemChecker)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateProblemChecker(ctx, req.(*UpdateProblemCheckerRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemChecker)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_ListProblemTests0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListProblemTestsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceListProblemTests)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListProblemTests(ctx, req.(*ListProblemTestsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListProblemTestsResponse)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_GetProblemTest0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetProblemTestRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceGetProblemTest)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetProblemTest(ctx, req.(*GetProblemTestRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemTest)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_CreateProblemTest0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateProblemTestRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceCreateProblemTest)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateProblemTest(ctx, req.(*CreateProblemTestRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemTest)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_UpdateProblemTest0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateProblemTestRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceUpdateProblemTest)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateProblemTest(ctx, req.(*UpdateProblemTestRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemTest)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_DeleteProblemTest0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in DeleteProblemTestRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceDeleteProblemTest)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.DeleteProblemTest(ctx, req.(*DeleteProblemTestRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemTest)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_ListProblemSolutions0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListProblemSolutionsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceListProblemSolutions)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListProblemSolutions(ctx, req.(*ListProblemSolutionsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListProblemSolutionsResponse)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_GetProblemSolution0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetProblemSolutionRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceGetProblemSolution)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetProblemSolution(ctx, req.(*GetProblemSolutionRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemSolution)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_CreateProblemSolution0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateProblemSolutionRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceCreateProblemSolution)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateProblemSolution(ctx, req.(*CreateProblemSolutionRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemSolution)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_UpdateProblemSolution0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateProblemSolutionRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceUpdateProblemSolution)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateProblemSolution(ctx, req.(*UpdateProblemSolutionRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemSolution)
		return ctx.Result(200, reply)
	}
}

func _ProblemService_DeleteProblemSolution0_HTTP_Handler(srv ProblemServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in DeleteProblemSolutionRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationProblemServiceDeleteProblemSolution)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.DeleteProblemSolution(ctx, req.(*DeleteProblemSolutionRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ProblemSolution)
		return ctx.Result(200, reply)
	}
}

type ProblemServiceHTTPClient interface {
	CreateProblem(ctx context.Context, req *CreateProblemRequest, opts ...http.CallOption) (rsp *CreateProblemResponse, err error)
	CreateProblemChecker(ctx context.Context, req *CreateProblemCheckerRequest, opts ...http.CallOption) (rsp *ProblemChecker, err error)
	CreateProblemSolution(ctx context.Context, req *CreateProblemSolutionRequest, opts ...http.CallOption) (rsp *ProblemSolution, err error)
	CreateProblemStatement(ctx context.Context, req *CreateProblemStatementRequest, opts ...http.CallOption) (rsp *ProblemStatement, err error)
	CreateProblemTest(ctx context.Context, req *CreateProblemTestRequest, opts ...http.CallOption) (rsp *ProblemTest, err error)
	DeleteProblemSolution(ctx context.Context, req *DeleteProblemSolutionRequest, opts ...http.CallOption) (rsp *ProblemSolution, err error)
	DeleteProblemStatement(ctx context.Context, req *DeleteProblemStatementRequest, opts ...http.CallOption) (rsp *ProblemStatement, err error)
	DeleteProblemTest(ctx context.Context, req *DeleteProblemTestRequest, opts ...http.CallOption) (rsp *ProblemTest, err error)
	GetProblem(ctx context.Context, req *GetProblemRequest, opts ...http.CallOption) (rsp *Problem, err error)
	GetProblemChecker(ctx context.Context, req *GetProblemCheckerRequest, opts ...http.CallOption) (rsp *ProblemChecker, err error)
	GetProblemSolution(ctx context.Context, req *GetProblemSolutionRequest, opts ...http.CallOption) (rsp *ProblemSolution, err error)
	GetProblemStatement(ctx context.Context, req *GetProblemStatementRequest, opts ...http.CallOption) (rsp *ProblemStatement, err error)
	GetProblemTest(ctx context.Context, req *GetProblemTestRequest, opts ...http.CallOption) (rsp *ProblemTest, err error)
	ListProblemCheckers(ctx context.Context, req *ListProblemCheckersRequest, opts ...http.CallOption) (rsp *ListProblemCheckersResponse, err error)
	ListProblemSolutions(ctx context.Context, req *ListProblemSolutionsRequest, opts ...http.CallOption) (rsp *ListProblemSolutionsResponse, err error)
	ListProblemStatements(ctx context.Context, req *ListProblemStatementsRequest, opts ...http.CallOption) (rsp *ListProblemStatementsResponse, err error)
	ListProblemTests(ctx context.Context, req *ListProblemTestsRequest, opts ...http.CallOption) (rsp *ListProblemTestsResponse, err error)
	ListProblems(ctx context.Context, req *ListProblemsRequest, opts ...http.CallOption) (rsp *ListProblemsResponse, err error)
	UpdateProblem(ctx context.Context, req *UpdateProblemRequest, opts ...http.CallOption) (rsp *Problem, err error)
	UpdateProblemChecker(ctx context.Context, req *UpdateProblemCheckerRequest, opts ...http.CallOption) (rsp *ProblemChecker, err error)
	UpdateProblemSolution(ctx context.Context, req *UpdateProblemSolutionRequest, opts ...http.CallOption) (rsp *ProblemSolution, err error)
	UpdateProblemStatement(ctx context.Context, req *UpdateProblemStatementRequest, opts ...http.CallOption) (rsp *ProblemStatement, err error)
	UpdateProblemTest(ctx context.Context, req *UpdateProblemTestRequest, opts ...http.CallOption) (rsp *ProblemTest, err error)
}

type ProblemServiceHTTPClientImpl struct {
	cc *http.Client
}

func NewProblemServiceHTTPClient(client *http.Client) ProblemServiceHTTPClient {
	return &ProblemServiceHTTPClientImpl{client}
}

func (c *ProblemServiceHTTPClientImpl) CreateProblem(ctx context.Context, in *CreateProblemRequest, opts ...http.CallOption) (*CreateProblemResponse, error) {
	var out CreateProblemResponse
	pattern := "/problems"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceCreateProblem))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) CreateProblemChecker(ctx context.Context, in *CreateProblemCheckerRequest, opts ...http.CallOption) (*ProblemChecker, error) {
	var out ProblemChecker
	pattern := "/problems/{id}/checkers"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceCreateProblemChecker))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) CreateProblemSolution(ctx context.Context, in *CreateProblemSolutionRequest, opts ...http.CallOption) (*ProblemSolution, error) {
	var out ProblemSolution
	pattern := "/problems/{id}/solution"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceCreateProblemSolution))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) CreateProblemStatement(ctx context.Context, in *CreateProblemStatementRequest, opts ...http.CallOption) (*ProblemStatement, error) {
	var out ProblemStatement
	pattern := "/problems/{id}/statements"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceCreateProblemStatement))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) CreateProblemTest(ctx context.Context, in *CreateProblemTestRequest, opts ...http.CallOption) (*ProblemTest, error) {
	var out ProblemTest
	pattern := "/problems/{id}/tests"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceCreateProblemTest))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) DeleteProblemSolution(ctx context.Context, in *DeleteProblemSolutionRequest, opts ...http.CallOption) (*ProblemSolution, error) {
	var out ProblemSolution
	pattern := "/problems/{id}/solution"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceDeleteProblemSolution))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "DELETE", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) DeleteProblemStatement(ctx context.Context, in *DeleteProblemStatementRequest, opts ...http.CallOption) (*ProblemStatement, error) {
	var out ProblemStatement
	pattern := "/problems/{id}/statements"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceDeleteProblemStatement))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "DELETE", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) DeleteProblemTest(ctx context.Context, in *DeleteProblemTestRequest, opts ...http.CallOption) (*ProblemTest, error) {
	var out ProblemTest
	pattern := "/problems/{id}/tests"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceDeleteProblemTest))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "DELETE", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) GetProblem(ctx context.Context, in *GetProblemRequest, opts ...http.CallOption) (*Problem, error) {
	var out Problem
	pattern := "/problems/{id}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceGetProblem))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) GetProblemChecker(ctx context.Context, in *GetProblemCheckerRequest, opts ...http.CallOption) (*ProblemChecker, error) {
	var out ProblemChecker
	pattern := "/problems/{id}/checkers"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceGetProblemChecker))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) GetProblemSolution(ctx context.Context, in *GetProblemSolutionRequest, opts ...http.CallOption) (*ProblemSolution, error) {
	var out ProblemSolution
	pattern := "/problems/{id}/solution"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceGetProblemSolution))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) GetProblemStatement(ctx context.Context, in *GetProblemStatementRequest, opts ...http.CallOption) (*ProblemStatement, error) {
	var out ProblemStatement
	pattern := "/problems/{id}/statements"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceGetProblemStatement))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) GetProblemTest(ctx context.Context, in *GetProblemTestRequest, opts ...http.CallOption) (*ProblemTest, error) {
	var out ProblemTest
	pattern := "/problems/{id}/tests"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceGetProblemTest))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) ListProblemCheckers(ctx context.Context, in *ListProblemCheckersRequest, opts ...http.CallOption) (*ListProblemCheckersResponse, error) {
	var out ListProblemCheckersResponse
	pattern := "/problems/{id}/checkers"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceListProblemCheckers))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) ListProblemSolutions(ctx context.Context, in *ListProblemSolutionsRequest, opts ...http.CallOption) (*ListProblemSolutionsResponse, error) {
	var out ListProblemSolutionsResponse
	pattern := "/problems/{id}/solution"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceListProblemSolutions))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) ListProblemStatements(ctx context.Context, in *ListProblemStatementsRequest, opts ...http.CallOption) (*ListProblemStatementsResponse, error) {
	var out ListProblemStatementsResponse
	pattern := "/problems/{id}/statements"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceListProblemStatements))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) ListProblemTests(ctx context.Context, in *ListProblemTestsRequest, opts ...http.CallOption) (*ListProblemTestsResponse, error) {
	var out ListProblemTestsResponse
	pattern := "/problems/{id}/tests"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationProblemServiceListProblemTests))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
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

func (c *ProblemServiceHTTPClientImpl) UpdateProblem(ctx context.Context, in *UpdateProblemRequest, opts ...http.CallOption) (*Problem, error) {
	var out Problem
	pattern := "/problems"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceUpdateProblem))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) UpdateProblemChecker(ctx context.Context, in *UpdateProblemCheckerRequest, opts ...http.CallOption) (*ProblemChecker, error) {
	var out ProblemChecker
	pattern := "/problems/{id}/checkers"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceUpdateProblemChecker))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) UpdateProblemSolution(ctx context.Context, in *UpdateProblemSolutionRequest, opts ...http.CallOption) (*ProblemSolution, error) {
	var out ProblemSolution
	pattern := "/problems/{id}/solution"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceUpdateProblemSolution))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) UpdateProblemStatement(ctx context.Context, in *UpdateProblemStatementRequest, opts ...http.CallOption) (*ProblemStatement, error) {
	var out ProblemStatement
	pattern := "/problems/{id}/statements"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceUpdateProblemStatement))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *ProblemServiceHTTPClientImpl) UpdateProblemTest(ctx context.Context, in *UpdateProblemTestRequest, opts ...http.CallOption) (*ProblemTest, error) {
	var out ProblemTest
	pattern := "/problems/{id}/tests"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationProblemServiceUpdateProblemTest))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}
