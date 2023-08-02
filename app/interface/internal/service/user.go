package service

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"

	"google.golang.org/protobuf/types/known/emptypb"
	"google.golang.org/protobuf/types/known/structpb"
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
	if user.Status == biz.UserStatusDisable {
		return nil, v1.ErrorUserDisable("")
	}
	resp := &v1.GetUserInfoResponse{
		Id:       int32(user.ID),
		Nickname: user.Nickname,
		Avatar:   user.Avatar,
	}
	// 权限
	resources := biz.ListRoleResources(role)
	resp.Permissions = make(map[string]*structpb.ListValue)
	for _, r := range resources {
		permissions := &structpb.ListValue{}
		permissions.Values = append(permissions.Values, &structpb.Value{
			Kind: &structpb.Value_StringValue{StringValue: "write"},
		}, &structpb.Value{
			Kind: &structpb.Value_StringValue{StringValue: "read"},
		})
		resp.Permissions[r] = permissions
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
		Role:     v1.User_UserRole(res.Role),
		Avatar:   res.Avatar,
	}, nil
}

// UpdateUser 修改用户信息
func (s *UserService) UpdateUser(ctx context.Context, req *v1.UpdateUserRequest) (*v1.User, error) {
	uid, _ := auth.GetUserID(ctx)
	user, _ := s.uc.GetUser(ctx, uid)
	user.Nickname = req.Nickname
	s.uc.UpdateUser(ctx, user)
	up := &biz.UserProfile{
		UserID:   user.ID,
		Location: req.Location,
		Bio:      req.Bio,
		Gender:   int(req.Gender),
		School:   req.School,
		Company:  req.Company,
		Job:      req.Job,
	}
	if req.Birthday != nil {
		t := req.Birthday.AsTime()
		up.Birthday = &t
	}
	s.uc.UpdateUserProfile(ctx, up)
	return &v1.User{
		Id:       int32(user.ID),
		Nickname: user.Nickname,
	}, nil
}

// UpdateUserAvatar 修改用户头像
func (s *UserService) UpdateUserAvatar(ctx context.Context, req *v1.UpdateUserAvatarRequest) (*v1.UpdateUserAvatarResponse, error) {
	userId, _ := auth.GetUserID(ctx)
	user, _ := s.uc.GetUser(ctx, userId)
	u, err := s.uc.UpdateUserAvatar(ctx, user, req)
	if err != nil {
		return nil, err
	}
	return &v1.UpdateUserAvatarResponse{Url: u.Avatar}, err
}

// GetUserProfile 获取用户信息
func (s *UserService) GetUserProfile(ctx context.Context, req *v1.GetUserProfileRequest) (*v1.UserProfile, error) {
	userId, _ := auth.GetUserID(ctx)
	profile, err := s.uc.GetUserProfile(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	resp := &v1.UserProfile{
		Location: profile.Location,
		Bio:      profile.Bio,
		Gender:   int32(profile.Gender),
		School:   profile.School,
		Company:  profile.Company,
		Job:      profile.Job,
	}
	if profile.Birthday != nil && userId == int(req.Id) {
		resp.Birthday = timestamppb.New(*profile.Birthday)
	}
	return resp, nil
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

// GetUserProfileProblemSolved 用户主页做题进度统计
func (s UserService) GetUserProfileProblemSolved(ctx context.Context, req *v1.GetUserProfileProblemSolvedRequest) (*v1.GetUserProfileProblemSolvedResponse, error) {
	return s.uc.GetUserProfileProblemSolved(ctx, req)
}

// GetUserProfileCount 用户主页-统计
func (s UserService) GetUserProfileCount(ctx context.Context, req *v1.GetUserProfileCountRequest) (*v1.GetUserProfileCountResponse, error) {
	return s.uc.GetUserProfileCount(ctx, int(req.Id))
}

// ListUserProfileUserBadges 用户主页勋章成就
func (s UserService) ListUserProfileUserBadges(ctx context.Context, req *v1.ListUserProfileUserBadgesRequest) (*v1.ListUserProfileUserBadgesResponse, error) {
	return s.uc.ListUserProfileUserBadges(ctx, int(req.Id))
}
