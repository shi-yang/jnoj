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
			Id:          int32(v.ID),
			Name:        v.Name,
			Description: v.Description,
			MemberCount: int32(v.MemberCount),
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
	return &v1.Group{
		Id:          int32(res.ID),
		Name:        res.Name,
		Description: res.Description,
		MemberCount: int32(res.MemberCount),
		CreatedAt:   timestamppb.New(res.CreatedAt),
	}, nil
}

// CreateGroup .
func (s *GroupService) CreateGroup(ctx context.Context, req *v1.CreateGroupRequest) (*v1.Group, error) {
	uid, _ := auth.GetUserID(ctx)
	group := &biz.Group{
		Name:        req.Name,
		Description: req.Description,
		UserID:      uid,
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
	group := &biz.Group{
		ID:          int(req.Id),
		Name:        req.Name,
		Description: req.Description,
	}
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
		resp.Data = append(resp.Data, &v1.GroupUser{
			Id:        int32(v.ID),
			GroupId:   int32(v.GroupID),
			UserId:    int32(v.UserID),
			CreatedAt: timestamppb.New(v.CreatedAt),
		})
	}
	return resp, nil
}

func (s *GroupService) CreateGroupUser(ctx context.Context, req *v1.CreateGroupUserRequest) (*v1.GroupUser, error) {
	res, err := s.uc.CreateGroupUser(ctx, &biz.GroupUser{
		GroupID: int(req.Gid),
		UserID:  int(req.Uid),
	})
	if err != nil {
		return nil, err
	}
	return &v1.GroupUser{
		Id: int32(res.ID),
	}, nil
}

// DeleteGroupUser .
func (s *GroupService) DeleteGroupUser(ctx context.Context, req *v1.DeleteGroupUserRequest) (*emptypb.Empty, error) {
	err := s.uc.DeleteGroupUser(ctx, int(req.Gid), int(req.Uid))
	return &emptypb.Empty{}, err
}
