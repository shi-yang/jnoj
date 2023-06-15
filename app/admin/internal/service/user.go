package service

import (
	"context"

	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"
	"jnoj/internal/middleware/auth"

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
