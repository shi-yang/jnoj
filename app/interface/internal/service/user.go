package service

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"

	"google.golang.org/protobuf/types/known/emptypb"
	"google.golang.org/protobuf/types/known/structpb"
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
	id, token, err := s.uc.Register(ctx, &biz.User{
		Username: req.Username,
		Nickname: req.Username,
		Email:    req.GetEmail(),
		Phone:    req.GetPhone(),
		Password: req.Password,
	}, req.Captcha)
	if err != nil {
		return nil, err
	}
	return &v1.RegisterResponse{
		Id:    int32(id),
		Token: token,
	}, err
}

// GetCaptcha 获取验证码
func (s *UserService) GetCaptcha(ctx context.Context, req *v1.GetCaptchaRequest) (*emptypb.Empty, error) {
	if err := s.uc.GetCaptcha(ctx, req.GetEmail(), req.GetPhone()); err != nil {
		return nil, err
	}
	return &emptypb.Empty{}, nil
}

// GetUserInfo 获取登录用户信息
func (s *UserService) GetUserInfo(ctx context.Context, req *emptypb.Empty) (*v1.GetUserInfoResponse, error) {
	userID, role := auth.GetUserID(ctx)
	if userID == 0 {
		return nil, v1.ErrorUnauthorized("not login")
	}
	user, err := s.uc.GetUser(ctx, userID)
	if err != nil {
		return nil, err
	}
	resp := &v1.GetUserInfoResponse{
		Id:       int32(user.ID),
		Nickname: user.Nickname,
	}
	if biz.CheckAccess(role, biz.ResourceAdmin) {
		resp.Permissions = make(map[string]*structpb.ListValue)
		permissions := &structpb.ListValue{}
		permissions.Values = append(permissions.Values, &structpb.Value{
			Kind: &structpb.Value_StringValue{StringValue: "write"},
		}, &structpb.Value{
			Kind: &structpb.Value_StringValue{StringValue: "read"},
		})
		resp.Permissions["*"] = permissions
	}
	return resp, nil
}

// GetUser 获取用户主页信息
func (s *UserService) GetUser(ctx context.Context, req *v1.GetUserRequest) (*v1.User, error) {
	res, err := s.uc.GetUser(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	return &v1.User{
		Id:       int32(res.ID),
		Nickname: res.Nickname,
		Username: res.Username,
	}, nil
}

// UpdateUser 修改用户信息
func (s *UserService) UpdateUser(ctx context.Context, req *v1.UpdateUserRequest) (*v1.User, error) {
	user, err := s.uc.GetUser(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	uid, _ := auth.GetUserID(ctx)
	if user.ID != uid {
		return nil, v1.ErrorForbidden("")
	}
	user.Nickname = req.Nickname
	s.uc.UpdateUser(ctx, user)
	return &v1.User{
		Id:       int32(user.ID),
		Nickname: user.Nickname,
	}, nil
}

// UpdateUserPassword 修改用户密码
func (s *UserService) UpdateUserPassword(ctx context.Context, req *v1.UpdateUserPasswordRequest) (*emptypb.Empty, error) {
	user, err := s.uc.GetUser(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	uid, _ := auth.GetUserID(ctx)
	if user.ID != uid {
		return nil, v1.ErrorForbidden("")
	}
	_, err = s.uc.UpdateUserPassowrd(ctx, user, req.OldPassword, req.NewPassword)
	return &emptypb.Empty{}, err
}

// GetUserProfileCalendar 用户主页提交统计
func (s UserService) GetUserProfileCalendar(ctx context.Context, req *v1.GetUserProfileCalendarRequest) (*v1.GetUserProfileCalendarResponse, error) {
	return s.uc.GetUserProfileCalendar(ctx, req)
}

// GetUserProfileProblemSolved 用户主页提交统计
func (s UserService) GetUserProfileProblemSolved(ctx context.Context, req *v1.GetUserProfileProblemSolvedRequest) (*v1.GetUserProfileProblemSolvedResponse, error) {
	return s.uc.GetUserProfileProblemSolved(ctx, req)
}
