package service

import (
	"context"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"

	"google.golang.org/protobuf/types/known/emptypb"
	"google.golang.org/protobuf/types/known/timestamppb"
)

// GroupService is a contest service.
type GroupService struct {
	uc *biz.GroupUsecase
}

// NewGroupService new a contest service.
func NewGroupService(uc *biz.GroupUsecase) *GroupService {
	return &GroupService{uc: uc}
}

// ListGroups 比赛列表
func (s *GroupService) ListGroups(ctx context.Context, req *v1.ListGroupsRequest) (*v1.ListGroupsResponse, error) {
	res, count := s.uc.ListGroups(ctx, req)
	resp := new(v1.ListGroupsResponse)
	resp.Total = count
	resp.Data = make([]*v1.Group, 0)
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.Group{
			Id:           int32(v.ID),
			Name:         v.Name,
			Privacy:      int32(v.Privacy),
			Membership:   int32(v.Membership),
			Description:  v.Description,
			MemberCount:  int32(v.MemberCount),
			UserId:       int32(v.UserID),
			UserNickname: v.UserNickname,
		})
	}
	return resp, nil
}

// GetGroup .
func (s *GroupService) GetGroup(ctx context.Context, req *v1.GetGroupRequest) (*v1.Group, error) {
	res, err := s.uc.GetGroup(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	g := &v1.Group{
		Id:             int32(res.ID),
		Name:           res.Name,
		Description:    res.Description,
		Privacy:        int32(res.Privacy),
		Membership:     int32(res.Membership),
		MemberCount:    int32(res.MemberCount),
		InvitationCode: res.InvitationCode,
		Role:           v1.GroupUserRole(res.Role),
		CreatedAt:      timestamppb.New(res.CreatedAt),
	}
	return g, nil
}

// CreateGroup .
func (s *GroupService) CreateGroup(ctx context.Context, req *v1.CreateGroupRequest) (*v1.Group, error) {
	uid, _ := auth.GetUserID(ctx)
	group := &biz.Group{
		Name:        req.Name,
		Description: req.Description,
		UserID:      uid,
		MemberCount: 1,
	}
	res, err := s.uc.CreateGroup(ctx, group)
	if err != nil {
		return nil, err
	}
	return &v1.Group{
		Id: int32(res.ID),
	}, nil
}

// UpdateGroup .
func (s *GroupService) UpdateGroup(ctx context.Context, req *v1.UpdateGroupRequest) (*v1.Group, error) {
	group, err := s.uc.GetGroup(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	role := s.uc.GetGroupRole(ctx, group)
	if role != biz.GroupUserRoleAdmin && role != biz.GroupUserRoleManager {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	group.Name = req.Name
	group.Membership = int(req.Membership)
	group.Privacy = int(req.Privacy)
	group.InvitationCode = req.InvitationCode
	group.Description = req.Description
	res, err := s.uc.UpdateGroup(ctx, group)
	if err != nil {
		return nil, err
	}
	return &v1.Group{
		Id: int32(res.ID),
	}, nil
}

// ListGroupUsers .
func (s *GroupService) ListGroupUsers(ctx context.Context, req *v1.ListGroupUsersRequest) (*v1.ListGroupUsersResponse, error) {
	data, count := s.uc.ListGroupUsers(ctx, req)
	resp := new(v1.ListGroupUsersResponse)
	resp.Total = count
	for _, v := range data {
		u := &v1.GroupUser{
			Id:        int32(v.ID),
			GroupId:   int32(v.GroupID),
			UserId:    int32(v.UserID),
			Nickname:  v.Nickname,
			CreatedAt: timestamppb.New(v.CreatedAt),
			Role:      v1.GroupUserRole(v.Role),
		}
		resp.Data = append(resp.Data, u)
	}
	return resp, nil
}

// GetGroupUser .
func (s *GroupService) GetGroupUser(ctx context.Context, req *v1.GetGroupUserRequest) (*v1.GroupUser, error) {
	u, err := s.uc.GetGroupUser(ctx, int(req.Gid), int(req.Uid))
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	resp := &v1.GroupUser{
		Id:        int32(u.ID),
		Nickname:  u.Nickname,
		GroupId:   int32(u.GroupID),
		UserId:    int32(u.UserID),
		Role:      v1.GroupUserRole(u.Role),
		CreatedAt: timestamppb.New(u.CreatedAt),
	}
	return resp, nil
}

func (s *GroupService) CreateGroupUser(ctx context.Context, req *v1.CreateGroupUserRequest) (*v1.GroupUser, error) {
	res, err := s.uc.CreateGroupUser(ctx, req)
	if err != nil {
		return nil, err
	}
	return &v1.GroupUser{
		Id: int32(res.ID),
	}, nil
}

func (s *GroupService) UpdateGroupUser(ctx context.Context, req *v1.UpdateGroupUserRequest) (*v1.GroupUser, error) {
	group, err := s.uc.GetGroup(ctx, int(req.Gid))
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	role := s.uc.GetGroupRole(ctx, group)
	if role != biz.GroupUserRoleAdmin && role != biz.GroupUserRoleManager {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	update := &biz.GroupUser{
		UserID:   int(req.Uid),
		GroupID:  int(req.Gid),
		Nickname: req.Nickname,
	}
	switch req.Role {
	case v1.GroupUserRole_MANAGER:
		update.Role = biz.GroupUserRoleManager
	case v1.GroupUserRole_MEMBER:
		update.Role = biz.GroupUserRoleMember
	default:
		return nil, v1.ErrorBadRequest("")
	}
	res, err := s.uc.UpdateGroupUser(ctx, update)
	return &v1.GroupUser{
		Id: int32(res.ID),
	}, err
}

// DeleteGroupUser .
func (s *GroupService) DeleteGroupUser(ctx context.Context, req *v1.DeleteGroupUserRequest) (*emptypb.Empty, error) {
	group, err := s.uc.GetGroup(ctx, int(req.Gid))
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	uid, _ := auth.GetUserID(ctx)
	_, err = s.uc.GetGroupUser(ctx, group.ID, uid)
	isLoginUserExistGroup := (err == nil && req.Uid == int32(uid))
	role := s.uc.GetGroupRole(ctx, group)
	if role != biz.GroupUserRoleAdmin && role != biz.GroupUserRoleManager && !isLoginUserExistGroup {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.uc.DeleteGroupUser(ctx, int(req.Gid), int(req.Uid))
	return &emptypb.Empty{}, err
}
