// Code generated by protoc-gen-go-http. DO NOT EDIT.
// versions:
// - protoc-gen-go-http v2.5.4
// - protoc             v3.21.12
// source: v1/group.proto

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
const OperationGroupServiceCreateGroup = "/jnoj.interface.v1.GroupService/CreateGroup"
const OperationGroupServiceCreateGroupUser = "/jnoj.interface.v1.GroupService/CreateGroupUser"
const OperationGroupServiceDeleteGroupUser = "/jnoj.interface.v1.GroupService/DeleteGroupUser"
const OperationGroupServiceGetGroup = "/jnoj.interface.v1.GroupService/GetGroup"
const OperationGroupServiceGetGroupUser = "/jnoj.interface.v1.GroupService/GetGroupUser"
const OperationGroupServiceListGroupUsers = "/jnoj.interface.v1.GroupService/ListGroupUsers"
const OperationGroupServiceListGroups = "/jnoj.interface.v1.GroupService/ListGroups"
const OperationGroupServiceUpdateGroup = "/jnoj.interface.v1.GroupService/UpdateGroup"
const OperationGroupServiceUpdateGroupUser = "/jnoj.interface.v1.GroupService/UpdateGroupUser"

type GroupServiceHTTPServer interface {
	CreateGroup(context.Context, *CreateGroupRequest) (*Group, error)
	CreateGroupUser(context.Context, *CreateGroupUserRequest) (*GroupUser, error)
	DeleteGroupUser(context.Context, *DeleteGroupUserRequest) (*emptypb.Empty, error)
	GetGroup(context.Context, *GetGroupRequest) (*Group, error)
	GetGroupUser(context.Context, *GetGroupUserRequest) (*GroupUser, error)
	ListGroupUsers(context.Context, *ListGroupUsersRequest) (*ListGroupUsersResponse, error)
	ListGroups(context.Context, *ListGroupsRequest) (*ListGroupsResponse, error)
	UpdateGroup(context.Context, *UpdateGroupRequest) (*Group, error)
	UpdateGroupUser(context.Context, *UpdateGroupUserRequest) (*GroupUser, error)
}

func RegisterGroupServiceHTTPServer(s *http.Server, srv GroupServiceHTTPServer) {
	s.Use("/jnoj.interface.v1.GroupService/CreateGroup", auth.User())
	s.Use("/jnoj.interface.v1.GroupService/ListGroups", auth.Guest())
	s.Use("/jnoj.interface.v1.GroupService/GetGroup", auth.Guest())
	r := s.Route("/")
	r.GET("/groups", _GroupService_ListGroups0_HTTP_Handler(srv))
	r.GET("/groups/{id}", _GroupService_GetGroup0_HTTP_Handler(srv))
	r.POST("/groups", _GroupService_CreateGroup0_HTTP_Handler(srv))
	r.PUT("/groups/{id}", _GroupService_UpdateGroup0_HTTP_Handler(srv))
	r.GET("/groups/{id}/users", _GroupService_ListGroupUsers0_HTTP_Handler(srv))
	r.GET("/groups/{gid}/users/{uid}", _GroupService_GetGroupUser0_HTTP_Handler(srv))
	r.POST("/groups/{gid}/users", _GroupService_CreateGroupUser0_HTTP_Handler(srv))
	r.PUT("/groups/{gid}/users/{uid}", _GroupService_UpdateGroupUser0_HTTP_Handler(srv))
	r.DELETE("/groups/{gid}/users/{uid}", _GroupService_DeleteGroupUser0_HTTP_Handler(srv))
}

func _GroupService_ListGroups0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListGroupsRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceListGroups)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListGroups(ctx, req.(*ListGroupsRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListGroupsResponse)
		return ctx.Result(200, reply)
	}
}

func _GroupService_GetGroup0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetGroupRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceGetGroup)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetGroup(ctx, req.(*GetGroupRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*Group)
		return ctx.Result(200, reply)
	}
}

func _GroupService_CreateGroup0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateGroupRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceCreateGroup)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateGroup(ctx, req.(*CreateGroupRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*Group)
		return ctx.Result(200, reply)
	}
}

func _GroupService_UpdateGroup0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateGroupRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceUpdateGroup)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateGroup(ctx, req.(*UpdateGroupRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*Group)
		return ctx.Result(200, reply)
	}
}

func _GroupService_ListGroupUsers0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in ListGroupUsersRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceListGroupUsers)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.ListGroupUsers(ctx, req.(*ListGroupUsersRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*ListGroupUsersResponse)
		return ctx.Result(200, reply)
	}
}

func _GroupService_GetGroupUser0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in GetGroupUserRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceGetGroupUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.GetGroupUser(ctx, req.(*GetGroupUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*GroupUser)
		return ctx.Result(200, reply)
	}
}

func _GroupService_CreateGroupUser0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in CreateGroupUserRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceCreateGroupUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.CreateGroupUser(ctx, req.(*CreateGroupUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*GroupUser)
		return ctx.Result(200, reply)
	}
}

func _GroupService_UpdateGroupUser0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in UpdateGroupUserRequest
		if err := ctx.Bind(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceUpdateGroupUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.UpdateGroupUser(ctx, req.(*UpdateGroupUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*GroupUser)
		return ctx.Result(200, reply)
	}
}

func _GroupService_DeleteGroupUser0_HTTP_Handler(srv GroupServiceHTTPServer) func(ctx http.Context) error {
	return func(ctx http.Context) error {
		var in DeleteGroupUserRequest
		if err := ctx.BindQuery(&in); err != nil {
			return err
		}
		if err := ctx.BindVars(&in); err != nil {
			return err
		}
		http.SetOperation(ctx, OperationGroupServiceDeleteGroupUser)
		h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
			return srv.DeleteGroupUser(ctx, req.(*DeleteGroupUserRequest))
		})
		out, err := h(ctx, &in)
		if err != nil {
			return err
		}
		reply := out.(*emptypb.Empty)
		return ctx.Result(200, reply)
	}
}

type GroupServiceHTTPClient interface {
	CreateGroup(ctx context.Context, req *CreateGroupRequest, opts ...http.CallOption) (rsp *Group, err error)
	CreateGroupUser(ctx context.Context, req *CreateGroupUserRequest, opts ...http.CallOption) (rsp *GroupUser, err error)
	DeleteGroupUser(ctx context.Context, req *DeleteGroupUserRequest, opts ...http.CallOption) (rsp *emptypb.Empty, err error)
	GetGroup(ctx context.Context, req *GetGroupRequest, opts ...http.CallOption) (rsp *Group, err error)
	GetGroupUser(ctx context.Context, req *GetGroupUserRequest, opts ...http.CallOption) (rsp *GroupUser, err error)
	ListGroupUsers(ctx context.Context, req *ListGroupUsersRequest, opts ...http.CallOption) (rsp *ListGroupUsersResponse, err error)
	ListGroups(ctx context.Context, req *ListGroupsRequest, opts ...http.CallOption) (rsp *ListGroupsResponse, err error)
	UpdateGroup(ctx context.Context, req *UpdateGroupRequest, opts ...http.CallOption) (rsp *Group, err error)
	UpdateGroupUser(ctx context.Context, req *UpdateGroupUserRequest, opts ...http.CallOption) (rsp *GroupUser, err error)
}

type GroupServiceHTTPClientImpl struct {
	cc *http.Client
}

func NewGroupServiceHTTPClient(client *http.Client) GroupServiceHTTPClient {
	return &GroupServiceHTTPClientImpl{client}
}

func (c *GroupServiceHTTPClientImpl) CreateGroup(ctx context.Context, in *CreateGroupRequest, opts ...http.CallOption) (*Group, error) {
	var out Group
	pattern := "/groups"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationGroupServiceCreateGroup))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *GroupServiceHTTPClientImpl) CreateGroupUser(ctx context.Context, in *CreateGroupUserRequest, opts ...http.CallOption) (*GroupUser, error) {
	var out GroupUser
	pattern := "/groups/{gid}/users"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationGroupServiceCreateGroupUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "POST", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *GroupServiceHTTPClientImpl) DeleteGroupUser(ctx context.Context, in *DeleteGroupUserRequest, opts ...http.CallOption) (*emptypb.Empty, error) {
	var out emptypb.Empty
	pattern := "/groups/{gid}/users/{uid}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationGroupServiceDeleteGroupUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "DELETE", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *GroupServiceHTTPClientImpl) GetGroup(ctx context.Context, in *GetGroupRequest, opts ...http.CallOption) (*Group, error) {
	var out Group
	pattern := "/groups/{id}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationGroupServiceGetGroup))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *GroupServiceHTTPClientImpl) GetGroupUser(ctx context.Context, in *GetGroupUserRequest, opts ...http.CallOption) (*GroupUser, error) {
	var out GroupUser
	pattern := "/groups/{gid}/users/{uid}"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationGroupServiceGetGroupUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *GroupServiceHTTPClientImpl) ListGroupUsers(ctx context.Context, in *ListGroupUsersRequest, opts ...http.CallOption) (*ListGroupUsersResponse, error) {
	var out ListGroupUsersResponse
	pattern := "/groups/{id}/users"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationGroupServiceListGroupUsers))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *GroupServiceHTTPClientImpl) ListGroups(ctx context.Context, in *ListGroupsRequest, opts ...http.CallOption) (*ListGroupsResponse, error) {
	var out ListGroupsResponse
	pattern := "/groups"
	path := binding.EncodeURL(pattern, in, true)
	opts = append(opts, http.Operation(OperationGroupServiceListGroups))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "GET", path, nil, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *GroupServiceHTTPClientImpl) UpdateGroup(ctx context.Context, in *UpdateGroupRequest, opts ...http.CallOption) (*Group, error) {
	var out Group
	pattern := "/groups/{id}"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationGroupServiceUpdateGroup))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}

func (c *GroupServiceHTTPClientImpl) UpdateGroupUser(ctx context.Context, in *UpdateGroupUserRequest, opts ...http.CallOption) (*GroupUser, error) {
	var out GroupUser
	pattern := "/groups/{gid}/users/{uid}"
	path := binding.EncodeURL(pattern, in, false)
	opts = append(opts, http.Operation(OperationGroupServiceUpdateGroupUser))
	opts = append(opts, http.PathTemplate(pattern))
	err := c.cc.Invoke(ctx, "PUT", path, in, &out, opts...)
	if err != nil {
		return nil, err
	}
	return &out, err
}
