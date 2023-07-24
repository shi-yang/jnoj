package service

import (
	"context"
	"io"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"

	"github.com/go-kratos/kratos/v2/transport/http"
	"google.golang.org/protobuf/types/known/timestamppb"
)

// PostService is a post service.
type PostService struct {
	uc *biz.PostUsecase
}

// NewPostService new a post service.
func NewPostService(uc *biz.PostUsecase) *PostService {
	return &PostService{uc: uc}
}

// ListPosts 比赛列表
func (s *PostService) ListPosts(ctx context.Context, req *v1.ListPostsRequest) (*v1.ListPostsResponse, error) {
	res, count := s.uc.ListPosts(ctx, req)
	resp := new(v1.ListPostsResponse)
	resp.Total = count
	resp.Data = make([]*v1.Post, 0)
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.Post{
			Id:        int32(v.ID),
			Title:     v.Title,
			Content:   v.Content,
			CreatedAt: timestamppb.New(v.CreatedAt),
		})
	}
	return resp, nil
}

// GetPost .
func (s *PostService) GetPost(ctx context.Context, req *v1.GetPostRequest) (*v1.Post, error) {
	res, err := s.uc.GetPost(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	g := &v1.Post{
		Id:        int32(res.ID),
		Title:     res.Title,
		Content:   res.Content,
		UserId:    int32(res.UserID),
		CreatedAt: timestamppb.New(res.CreatedAt),
	}
	return g, nil
}

// CreatePost .
func (s *PostService) CreatePost(ctx context.Context, req *v1.CreatePostRequest) (*v1.Post, error) {
	uid, _ := auth.GetUserID(ctx)
	post := &biz.Post{
		Title:      req.Title,
		Content:    req.Content,
		UserID:     uid,
		EntityId:   int(req.EntityId),
		EntityType: int(req.EntityType),
	}
	res, err := s.uc.CreatePost(ctx, post)
	if err != nil {
		return nil, err
	}
	return &v1.Post{
		Id: int32(res.ID),
	}, nil
}

// UpdatePost .
func (s *PostService) UpdatePost(ctx context.Context, req *v1.UpdatePostRequest) (*v1.Post, error) {
	post, err := s.uc.GetPost(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	post.Title = req.Title
	post.Content = req.Content
	res, err := s.uc.UpdatePost(ctx, post)
	if err != nil {
		return nil, err
	}
	return &v1.Post{
		Id: int32(res.ID),
	}, nil
}

// UploadPostImage 上传图片
func (s *PostService) UploadPostImage(ctx http.Context) error {
	http.SetOperation(ctx, "uploadPostImage")
	file, fileheader, err := ctx.Request().FormFile("file")
	if err != nil {
		return err
	}
	fileContent, err := io.ReadAll(file)
	if err != nil {
		return err
	}
	defer file.Close()
	imageUrl, err := s.uc.CreatePostImage(ctx, fileheader.Filename, fileContent)
	if err != nil {
		return err
	}
	return ctx.Result(200, imageUrl)
}
