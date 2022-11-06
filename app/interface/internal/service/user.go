package service

import (
	"context"
	"fmt"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"

	"google.golang.org/protobuf/types/known/emptypb"
)

// UserService is a user service.
type UserService struct {
	uc *biz.UserUsecase
}

// NewUserService new a user service.
func NewUserService(uc *biz.UserUsecase) *UserService {
	return &UserService{uc: uc}
}

// Login 登录
func (s *UserService) Login(ctx context.Context, req *v1.LoginRequest) (*v1.LoginResponse, error) {
	token, err := s.uc.Login(ctx, req)
	if err != nil {
		return nil, err
	}
	return &v1.LoginResponse{
		Token: token,
	}, nil
}

// Register 注册
func (s *UserService) Register(ctx context.Context, req *v1.RegisterRequest) (*v1.RegisterResponse, error) {
	s.uc.Register(ctx, &biz.User{
		Username: req.Phone,
	})
	return nil, nil
}

// GetUserInfo 获取登录用户信息
func (s *UserService) GetUserInfo(ctx context.Context, req *emptypb.Empty) (*v1.GetUserInfoResponse, error) {
	userID, ok := auth.GetUserID(ctx)
	if !ok {
		return nil, fmt.Errorf("not login")
	}
	user, err := s.uc.GetUser(ctx, userID)
	if err != nil {
		return nil, err
	}
	return &v1.GetUserInfoResponse{
		Id:       int32(user.ID),
		Nickname: user.Username,
	}, nil
}

// GetUser 获取用户主页信息
func (s *UserService) GetUser(ctx context.Context, req *v1.GetUserRequest) (*v1.User, error) {
	return &v1.User{}, nil
}
