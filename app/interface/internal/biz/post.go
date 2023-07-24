package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Post is a Post model.
type Post struct {
	ID           int
	Title        string
	Content      string
	EntityId     int
	EntityType   int
	UserID       int
	UserNickname string
	CreatedAt    time.Time
}

const (
	PostEntityTypeProblemSolution = iota
	PostEntityTypeContestEditorial
)

// PostRepo is a Post repo.
type PostRepo interface {
	ListPosts(context.Context, *v1.ListPostsRequest) ([]*Post, int64)
	GetPost(context.Context, int) (*Post, error)
	CreatePost(context.Context, *Post) (*Post, error)
	UpdatePost(context.Context, *Post) (*Post, error)
	DeletePost(context.Context, int) error
	CreatePostImage(ctx context.Context, filename string, content []byte) (string, error)
}

// PostUsecase is a Post usecase.
type PostUsecase struct {
	repo        PostRepo
	userRepo    UserRepo
	contestRepo ContestRepo
	log         *log.Helper
}

// NewPostUsecase new a Post usecase.
func NewPostUsecase(repo PostRepo, userRepo UserRepo, contestRepo ContestRepo, logger log.Logger) *PostUsecase {
	return &PostUsecase{repo: repo, userRepo: userRepo, contestRepo: contestRepo, log: log.NewHelper(logger)}
}

// ListPosts list Post
func (uc *PostUsecase) ListPosts(ctx context.Context, req *v1.ListPostsRequest) ([]*Post, int64) {
	// 判断权限
	if req.EntityType == PostEntityTypeContestEditorial {
		contest, err := uc.contestRepo.GetContest(ctx, int(req.EntityId))
		if err != nil {
			return nil, 0
		}
		if contest.GetRunningStatus() != ContestRunningStatusFinished && !contest.HasPermission(ctx, ContestPermissionUpdate) {
			return nil, 0
		}
	}
	return uc.repo.ListPosts(ctx, req)
}

// GetPost get a Post
func (uc *PostUsecase) GetPost(ctx context.Context, id int) (*Post, error) {
	p, err := uc.repo.GetPost(ctx, id)
	if err != nil {
		return nil, err
	}
	// 判断权限
	if p.EntityType == PostEntityTypeContestEditorial {
		contest, err := uc.contestRepo.GetContest(ctx, p.EntityId)
		if err != nil {
			return nil, err
		}
		if contest.GetRunningStatus() != ContestRunningStatusFinished && !contest.HasPermission(ctx, ContestPermissionUpdate) {
			return nil, v1.ErrorForbidden("")
		}
	}
	return p, nil
}

// CreatePost creates a Post, and returns the new Post.
func (uc *PostUsecase) CreatePost(ctx context.Context, p *Post) (*Post, error) {
	// 判断权限
	if p.EntityType == PostEntityTypeContestEditorial {
		contest, err := uc.contestRepo.GetContest(ctx, p.EntityId)
		if err != nil {
			return nil, err
		}
		if !contest.HasPermission(ctx, ContestPermissionUpdate) {
			return nil, v1.ErrorForbidden("")
		}
	}
	res, err := uc.repo.CreatePost(ctx, p)
	if err != nil {
		return nil, err
	}
	return res, nil
}

// UpdatePost update a Post
func (uc *PostUsecase) UpdatePost(ctx context.Context, p *Post) (*Post, error) {
	// 判断权限
	if p.EntityType == PostEntityTypeContestEditorial {
		contest, err := uc.contestRepo.GetContest(ctx, p.EntityId)
		if err != nil {
			return nil, err
		}
		if !contest.HasPermission(ctx, ContestPermissionUpdate) {
			return nil, v1.ErrorForbidden("")
		}
	}
	return uc.repo.UpdatePost(ctx, p)
}

// DeletePost delete a Post
func (uc *PostUsecase) DeletePost(ctx context.Context, id int) error {
	return uc.repo.DeletePost(ctx, id)
}

func (uc *PostUsecase) CreatePostImage(ctx context.Context, filename string, image []byte) (string, error) {
	return uc.repo.CreatePostImage(ctx, filename, image)
}
