package service

import (
	"context"

	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"
	"jnoj/internal/middleware/auth"

	"google.golang.org/protobuf/types/known/emptypb"
	"google.golang.org/protobuf/types/known/timestamppb"
)

// UserService is a user service.
type UserService struct {
	uc *biz.UserUsecase
}

// NewUserService new a user service.
func NewUserService(uc *biz.UserUsecase) *UserService {
	return &UserService{uc: uc}
}

// GetUser .
func (s UserService) GetUser(ctx context.Context, req *v1.GetUserRequest) (*v1.User, error) {
	u, err := s.uc.GetUser(ctx, &biz.User{ID: int(req.Id)})
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	return &v1.User{
		Id:       int32(u.ID),
		Nickname: u.Nickname,
		Username: u.Username,
		Role:     v1.UserRole(u.Role),
		Status:   v1.UserStatus(u.Status),
	}, nil
}

// CreateUser .
func (s UserService) CreateUser(ctx context.Context, req *v1.CreateUserRequest) (*v1.User, error) {
	if uid, _ := auth.GetUserID(ctx); uid != 1 {
		return nil, v1.ErrorForbidden("")
	}
	if req.Nickname == "" {
		req.Nickname = req.Username
	}
	u, err := s.uc.CreateUser(ctx, &biz.User{
		Username: req.Username,
		Nickname: req.Nickname,
		Email:    req.GetEmail(),
		Phone:    req.GetPhone(),
		Password: req.Password,
	})
	if err != nil {
		return nil, err
	}
	return &v1.User{Id: int32(u.ID)}, err
}

// UpdateUser 修改用户信息
func (s *UserService) UpdateUser(ctx context.Context, req *v1.UpdateUserRequest) (*v1.User, error) {
	user := &biz.User{
		ID:       int(req.Id),
		Username: req.Username,
		Password: req.Password,
		Nickname: req.Nickname,
		Role:     int(req.Role),
		Status:   int(req.Status),
	}
	s.uc.UpdateUser(ctx, user)
	return &v1.User{
		Id:       int32(user.ID),
		Username: user.Username,
	}, nil
}

// ListUsers .
func (s UserService) ListUsers(ctx context.Context, req *v1.ListUsersRequest) (*v1.ListUsersResponse, error) {
	res, count := s.uc.ListUsers(ctx, req)
	resp := new(v1.ListUsersResponse)
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.User{
			Id:        int32(v.ID),
			Username:  v.Username,
			Nickname:  v.Nickname,
			Role:      v1.UserRole(v.Role),
			Status:    v1.UserStatus(v.Status),
			CreatedAt: timestamppb.New(v.CreatedAt),
		})
	}
	resp.Total = count
	return resp, nil
}

// CreateUserExpiration 创建用户有效期
func (s UserService) CreateUserExpiration(ctx context.Context, req *v1.CreateUserExpirationRequest) (*emptypb.Empty, error) {
	for _, uid := range req.UserId {
		expiration := &biz.UserExpiration{
			UserID:    int(uid),
			StartTime: req.StartTime.AsTime(),
			EndTime:   req.EndTime.AsTime(),
			Type:      int(req.Type),
		}
		if req.Type == v1.UserExpirationType_ROLE {
			expiration.PeriodValue = int(v1.UserRole_value[req.PeriodValue])
			expiration.EndValue = int(v1.UserRole_value[req.EndValue])
		} else {
			expiration.PeriodValue = int(v1.UserStatus_value[req.PeriodValue])
			expiration.EndValue = int(v1.UserStatus_value[req.EndValue])
		}
		s.uc.CreateUserExpiration(ctx, expiration)
	}
	return nil, nil
}

// ListUserExpirations 用户有效期列表
func (s UserService) ListUserExpirations(ctx context.Context, req *v1.ListUserExpirationsRequest) (*v1.ListUserExpirationsResponse, error) {
	uids := make([]int, 0)
	for _, uid := range req.UserId {
		uids = append(uids, int(uid))
	}
	res := s.uc.ListUserExpirations(ctx, uids)
	resp := new(v1.ListUserExpirationsResponse)
	for _, v := range res {
		ue := &v1.UserExpiration{
			Id:        int32(v.ID),
			UserId:    int32(v.UserID),
			Type:      v1.UserExpirationType(v.Type),
			StartTime: timestamppb.New(v.StartTime),
			EndTime:   timestamppb.New(v.EndTime),
		}
		if v.Type == biz.UserExpirationTypeRole {
			ue.PeriodValue = v1.UserRole_name[int32(v.PeriodValue)]
			ue.EndValue = v1.UserRole_name[int32(v.EndValue)]
		} else {
			ue.PeriodValue = v1.UserStatus_name[int32(v.PeriodValue)]
			ue.EndValue = v1.UserStatus_name[int32(v.EndValue)]
		}
		resp.Data = append(resp.Data, ue)
	}
	return resp, nil
}

// DeleteUserExpiration 删除用户有效期
func (s UserService) DeleteUserExpiration(ctx context.Context, req *v1.DeleteUserExpirationRequest) (*emptypb.Empty, error) {
	err := s.uc.DeleteUserExpiration(ctx, int(req.Id))
	return &emptypb.Empty{}, err
}
