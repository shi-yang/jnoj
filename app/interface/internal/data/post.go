package data

import (
	"bytes"
	"context"
	"fmt"
	"net/url"
	"path"
	"strconv"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	objectstorage "jnoj/pkg/object_storage"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type postRepo struct {
	data *Data
	log  *log.Helper
}

type Post struct {
	ID         int
	Title      string
	Content    string
	EntityID   int
	EntityType int
	ViewCount  int
	UserID     int
	CreatedAt  time.Time
	UpdatedAt  time.Time
	DeletedAt  gorm.DeletedAt

	User *User `json:"user" gorm:"foreignKey:UserID"`
}

const postImageFilePath = "/posts/images/%s"

// NewPostRepo .
func NewPostRepo(data *Data, logger log.Logger) biz.PostRepo {
	return &postRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListPosts .
func (r *postRepo) ListPosts(ctx context.Context, req *v1.ListPostsRequest) ([]*biz.Post, int64) {
	res := []Post{}
	count := int64(0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&Post{}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		})
	db.Where("entity_id = ?", req.EntityId)
	db.Where("entity_type = ?", req.EntityType)
	if req.Title != "" {
		db.Where("title like ?", fmt.Sprintf("%%%s%%", req.Title))
	}
	db.Count(&count)
	db.Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&res)
	rv := make([]*biz.Post, 0)
	for _, v := range res {
		p := &biz.Post{
			ID:      v.ID,
			Title:   v.Title,
			Content: v.Content,
		}
		if v.User != nil {
			p.UserNickname = v.User.Nickname
		}
		rv = append(rv, p)
	}
	return rv, count
}

// GetPost .
func (r *postRepo) GetPost(ctx context.Context, id int) (*biz.Post, error) {
	var res Post
	err := r.data.db.Model(Post{}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname")
		}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.Post{
		ID:           res.ID,
		Title:        res.Title,
		Content:      res.Content,
		UserID:       res.UserID,
		UserNickname: res.User.Nickname,
		CreatedAt:    res.CreatedAt,
	}, err
}

// CreatePost .
func (r *postRepo) CreatePost(ctx context.Context, p *biz.Post) (*biz.Post, error) {
	res := Post{
		EntityID:   p.EntityId,
		EntityType: p.EntityType,
		Title:      p.Title,
		Content:    p.Content,
		UserID:     p.UserID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Post{
		ID: res.ID,
	}, err
}

// UpdatePost .
func (r *postRepo) UpdatePost(ctx context.Context, p *biz.Post) (*biz.Post, error) {
	res := Post{
		ID:      p.ID,
		Title:   p.Title,
		Content: p.Content,
	}
	err := r.data.db.WithContext(ctx).
		Select("Title", "Content").
		Omit(clause.Associations).
		Updates(&res).Error
	if err != nil {
		return nil, err
	}
	return &biz.Post{
		ID: res.ID,
	}, err
}

// DeletePost .
func (r *postRepo) DeletePost(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(Post{ID: id}).
		Error
	return err
}

func (r *postRepo) CreatePostImage(ctx context.Context, filename string, image []byte) (string, error) {
	store := objectstorage.NewSeaweed()
	fileUnixName := strconv.FormatInt(time.Now().UnixNano(), 10)
	storeName := fmt.Sprintf(postImageFilePath, fileUnixName+path.Ext(filename))
	err := store.PutObject(r.data.conf.ObjectStorage.PublicBucket, storeName, bytes.NewReader(image))
	if err != nil {
		return "", err
	}
	return url.JoinPath(
		r.data.conf.ObjectStorage.PublicBucket.Endpoint,
		r.data.conf.ObjectStorage.PublicBucket.Bucket,
		storeName,
	)
}
