package service

import (
	"context"
	"io"

	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"
	"jnoj/internal/middleware/auth"

	"github.com/go-kratos/kratos/v2/transport/http"
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
		Realname: u.Realname,
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
		Realname: req.Realname,
		Email:    req.GetEmail(),
		Phone:    req.GetPhone(),
		Password: req.Password,
	})
	if err != nil {
		return nil, err
	}
	return &v1.User{Id: int32(u.ID)}, err
}

// BatchCreateUser 批量创建用户
func (s UserService) BatchCreateUser(ctx context.Context, req *v1.BatchCreateUserRequest) (*v1.BatchCreateUserResponse, error) {
	return s.uc.BatchCreateUser(ctx, req)
}

// UpdateUser 修改用户信息
func (s *UserService) UpdateUser(ctx context.Context, req *v1.UpdateUserRequest) (*v1.User, error) {
	user := &biz.User{
		ID:       int(req.Id),
		Username: req.Username,
		Password: req.Password,
		Nickname: req.Nickname,
		Realname: req.Realname,
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
			Realname:  v.Realname,
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

// ListUserBadges 用户勋章成就列表
func (s UserService) ListUserBadges(ctx context.Context, req *v1.ListUserBadgesRequest) (*v1.ListUserBadgesResponse, error) {
	res, count := s.uc.ListUserBadges(ctx, req)
	resp := new(v1.ListUserBadgesResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.UserBadge{
			Id:       int32(v.ID),
			Type:     v1.UserBadgeType(v.Type),
			Name:     v.Name,
			Image:    string(v.ImageURL),
			ImageGif: v.ImageGifURL,
		})
	}
	return resp, nil
}

// GetUserBadge 用户勋章详情
func (s UserService) GetUserBadge(ctx context.Context, req *v1.GetUserBadgeRequest) (*v1.UserBadge, error) {
	res, err := s.uc.GetUserBadge(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	return &v1.UserBadge{
		Id:       int32(res.ID),
		Name:     res.Name,
		Type:     v1.UserBadgeType(res.Type),
		Image:    res.ImageURL,
		ImageGif: res.ImageGifURL,
	}, nil
}

// CreateUserBadge 新增用户勋章
func (s UserService) CreateUserBadge(ctx context.Context, req *v1.CreateUserBadgeRequest) (*v1.UserBadge, error) {
	badge := &biz.UserBadge{
		Name:     req.Name,
		Type:     int(req.Type),
		Image:    req.Image,
		ImageGif: req.ImageGif,
	}
	s.uc.CreateUserBadge(ctx, badge)
	return &v1.UserBadge{}, nil
}

// CreateUserBadgeWithFile 处理用户勋章图片上传
func (s UserService) CreateUserBadgeWithFile(ctx http.Context) error {
	var in v1.CreateUserBadgeRequest
	imageFile, _, err := ctx.Request().FormFile("image")
	if err != nil {
		return err
	}
	fileContent, err := io.ReadAll(imageFile)
	if err != nil {
		return err
	}
	defer imageFile.Close()
	imageGifFile, _, err := ctx.Request().FormFile("imageGif")
	if err != nil {
		return err
	}
	imageGifFileContent, err := io.ReadAll(imageGifFile)
	if err != nil {
		return err
	}
	defer imageGifFile.Close()
	in.Image = fileContent
	in.ImageGif = imageGifFileContent
	if err := ctx.BindVars(&in); err != nil {
		return err
	}
	if err := ctx.BindForm(&in); err != nil {
		return err
	}
	http.SetOperation(ctx, v1.OperationUserServiceCreateUserBadge)
	h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
		return s.CreateUserBadge(ctx, req.(*v1.CreateUserBadgeRequest))
	})
	out, err := h(ctx, &in)
	if err != nil {
		return err
	}
	reply := out.(*v1.UserBadge)
	return ctx.Result(200, reply)
}

// DeleteUserBadge 删除用户勋章
func (s UserService) DeleteUserBadge(ctx context.Context, req *v1.DeleteUserBadgeRequest) (*emptypb.Empty, error) {
	err := s.uc.DeleteUserBadge(ctx, int(req.Id))
	return &emptypb.Empty{}, err
}

// UpdateUserBadge 修改用户勋章
func (s UserService) UpdateUserBadge(ctx context.Context, req *v1.UpdateUserBadgeRequest) (*emptypb.Empty, error) {
	_, err := s.uc.UpdateUserBadge(ctx, &biz.UserBadge{
		ID:       int(req.Id),
		Name:     req.Name,
		Image:    req.Image,
		ImageGif: req.ImageGif,
		Type:     int(req.Type),
	})
	return &emptypb.Empty{}, err
}

// UpdateUserBadgeWithFile 处理用户勋章图片上传
func (s UserService) UpdateUserBadgeWithFile(ctx http.Context) error {
	var in v1.UpdateUserBadgeRequest
	imageFile, _, err := ctx.Request().FormFile("image")
	if err != nil {
		return err
	}
	fileContent, err := io.ReadAll(imageFile)
	if err != nil {
		return err
	}
	defer imageFile.Close()
	imageGifFile, _, err := ctx.Request().FormFile("imageGif")
	if err != nil {
		return err
	}
	imageGifFileContent, err := io.ReadAll(imageGifFile)
	if err != nil {
		return err
	}
	defer imageGifFile.Close()
	in.Image = fileContent
	in.ImageGif = imageGifFileContent
	if err := ctx.BindVars(&in); err != nil {
		return err
	}
	if err := ctx.BindForm(&in); err != nil {
		return err
	}
	http.SetOperation(ctx, v1.OperationUserServiceUpdateUserBadge)
	h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
		return s.UpdateUserBadge(ctx, req.(*v1.UpdateUserBadgeRequest))
	})
	out, err := h(ctx, &in)
	if err != nil {
		return err
	}
	reply := out.(*v1.UserBadge)
	return ctx.Result(200, reply)
}
