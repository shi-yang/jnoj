// Code generated by protoc-gen-go-http. DO NOT EDIT.
// versions:
// - protoc-gen-go-http v2.5.4
// - protoc             v3.21.12
// source: v1/user.proto

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
const OperationUserServiceCreateUser = "/jnoj.admin.v1.UserService/CreateUser"
const OperationUserServiceCreateUserExpiration = "/jnoj.admin.v1.UserService/CreateUserExpiration"
const OperationUserServiceDeleteUserExpiration = "/jnoj.admin.v1.UserService/DeleteUserExpiration"
const OperationUserServiceGetUser = "/jnoj.admin.v1.UserService/GetUser"
const OperationUserServiceListUserExpirations = "/jnoj.admin.v1.UserService/ListUserExpirations"
const OperationUserServiceListUsers = "/jnoj.admin.v1.UserService/ListUsers"
const OperationUserServiceUpdateUser = "/jnoj.admin.v1.UserService/UpdateUser"

type UserServiceHTTPServer interface {
	CreateUser(context.Context, *CreateUserRequest) (*User, error)
	CreateUserExpiration(context.Context, *CreateUserExpirationRequest) (*emptypb.Empty, error)
	DeleteUserExpiration(context.Context, *DeleteUserExpirationRequest) (*emptypb.Empty, error)
	GetUser(context.Context, *GetUserRequest) (*User, error)
	ListUserExpirations(context.Context, *ListUserExpirationsRequest) (*ListUserExpirationsResponse, error)
	ListUsers(context.Context, *ListUsersRequest) (*ListUsersResponse, error)
	UpdateUser(context.Context, *UpdateUserRequest) (*User, error)
}

func RegisterUserServiceHTTPServer(s *http.Server, srv UserServiceHTTPServer) {
	s.Use("/jnoj.admin.v1.UserService/GetUserInfo", auth.User())
	r := s.Route("/")
	r.GET("/users/{id}", _UserService_GetUser0_HTTP_Handler(srv))
	r.POST("/users", _UserService_CreateUser0_HTTP_Handler(srv))
	r.PUT("/users/{id}", _UserService_UpdateUser0_HTTP_Handler(srv))
	r.GET("/users", _UserService_ListUsers0_HTTP_Handler(srv))
	r.POST("/users/{user_id}/user_expirations", _UserService_CreateUserExpiration0_HTTP_Handler(srv))
	r.DELETE("/user_expirations/{id}", _UserService_DeleteUserExpiration0_HTTP_Handler(srv))
	r.GET("/users/{user_id}/user_expirations", _UserService_ListUserExpirations0_HTTP_Handler(srv))
}

func _UserService_GetUser0_HTTP_Handler(srv UserServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetUserRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationUserServiceGetUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetUser(ctx, req.(*GetUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*User)
		return ctx.Result(200, reply)
	}
}

func _UserService_CreateUser0_HTTP_Handler(srv UserServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateUserRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationUserServiceCreateUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateUser(ctx, req.(*CreateUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*User)
		return ctx.Result(200, reply)
	}
}

func _UserService_UpdateUser0_HTTP_Handler(srv UserServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateUserRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationUserServiceUpdateUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateUser(ctx, req.(*UpdateUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*User)
		return ctx.Result(200, reply)
	}
}

func _UserService_ListUsers0_HTTP_Handler(srv UserServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListUsersRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationUserServiceListUsers)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListUsers(ctx, req.(*ListUsersRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListUsersResponse)
		return ctx.Result(200, reply)
	}
}

func _UserService_CreateUserExpiration0_HTTP_Handler(srv UserServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateUserExpirationRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationUserServiceCreateUserExpiration)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateUserExpiration(ctx, req.(*CreateUserExpirationRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*emptypb.Empty)
		return ctx.Result(200, reply)
	}
}

func _UserService_DeleteUserExpiration0_HTTP_Handler(srv UserServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in DeleteUserExpirationRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationUserServiceDeleteUserExpiration)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.DeleteUserExpiration(ctx, req.(*DeleteUserExpirationRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*emptypb.Empty)
		return ctx.Result(200, reply)
	}
}

func _UserService_ListUserExpirations0_HTTP_Handler(srv UserServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListUserExpirationsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationUserServiceListUserExpirations)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListUserExpirations(ctx, req.(*ListUserExpirationsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListUserExpirationsResponse)
		return ctx.Result(200, reply)
	}
}

type UserServiceHTTPClient interface {
	CreateUser(ctx context.Context, req *CreateUserRequest, opts ...http.CallOption) (rsp *User, err error)
	CreateUserExpiration(ctx context.Context, req *CreateUserExpirationRequest, opts ...http.CallOption) (rsp *emptypb.Empty, err error)
	DeleteUserExpiration(ctx context.Context, req *DeleteUserExpirationRequest, opts ...http.CallOption) (rsp *emptypb.Empty, err error)
	GetUser(ctx context.Context, req *GetUserRequest, opts ...http.CallOption) (rsp *User, err error)
	ListUserExpirations(ctx context.Context, req *ListUserExpirationsRequest, opts ...http.CallOption) (rsp *ListUserExpirationsResponse, err error)
	ListUsers(ctx context.Context, req *ListUsersRequest, opts ...http.CallOption) (rsp *ListUsersResponse, err error)
	UpdateUser(ctx context.Context, req *UpdateUserRequest, opts ...http.CallOption) (rsp *User, err error)
}

type UserServiceHTTPClientImpl struct {
	cc *http.Client
}

func NewUserServiceHTTPClient(client *http.Client) UserServiceHTTPClient {
	return &UserServiceHTTPClientImpl{client}
}

func (c *UserServiceHTTPClientImpl) CreateUser(ctx context.Context, in *CreateUserRequest, opts ...http.CallOption) (*User, error) {
	var out User
	pattern := "/users"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationUserServiceCreateUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *UserServiceHTTPClientImpl) CreateUserExpiration(ctx context.Context, in *CreateUserExpirationRequest, opts ...http.CallOption) (*emptypb.Empty, error) {
	var out emptypb.Empty
	pattern := "/users/{user_id}/user_expirations"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationUserServiceCreateUserExpiration))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *UserServiceHTTPClientImpl) DeleteUserExpiration(ctx context.Context, in *DeleteUserExpirationRequest, opts ...http.CallOption) (*emptypb.Empty, error) {
	var out emptypb.Empty
	pattern := "/user_expirations/{id}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationUserServiceDeleteUserExpiration))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "DELETE", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *UserServiceHTTPClientImpl) GetUser(ctx context.Context, in *GetUserRequest, opts ...http.CallOption) (*User, error) {
	var out User
	pattern := "/users/{id}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationUserServiceGetUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *UserServiceHTTPClientImpl) ListUserExpirations(ctx context.Context, in *ListUserExpirationsRequest, opts ...http.CallOption) (*ListUserExpirationsResponse, error) {
	var out ListUserExpirationsResponse
	pattern := "/users/{user_id}/user_expirations"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationUserServiceListUserExpirations))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *UserServiceHTTPClientImpl) ListUsers(ctx context.Context, in *ListUsersRequest, opts ...http.CallOption) (*ListUsersResponse, error) {
	var out ListUsersResponse
	pattern := "/users"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationUserServiceListUsers))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *UserServiceHTTPClientImpl) UpdateUser(ctx context.Context, in *UpdateUserRequest, opts ...http.CallOption) (*User, error) {
	var out User
	pattern := "/users/{id}"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationUserServiceUpdateUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}
