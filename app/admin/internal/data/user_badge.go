package data

import (
	"bytes"
	"context"
	"fmt"
	"net/url"
	"time"

	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"
	"jnoj/pkg/pagination"

	objectstorage "jnoj/pkg/object_storage"

	"gorm.io/gorm/clause"
)

type UserBadge struct {
	ID        int
	Name      string
	Type      int
	Image     string
	ImageGif  string
	CreatedAt time.Time
}

// 用户勋章储存路径 %d 勋章ID， %s 名称
const userBadgeFilePath = "/user/badge/%d/%s"

// ListUserBadges .
func (r *userRepo) ListUserBadges(ctx context.Context, req *v1.ListUserBadgesRequest) ([]*biz.UserBadge, int64) {
	res := []UserBadge{}
	count := int64(0)
	pager := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&UserBadge{})
	if req.Name != "" {
		db.Where("name like ?", "%"+req.Name+"%")
	}
	if req.Type != nil {
		db.Where("type = ?", *req.Type)
	}
	db.
		Count(&count).
		Order("id desc").
		Offset(pager.GetOffset()).
		Limit(pager.GetPageSize()).
		Find(&res)

	rv := make([]*biz.UserBadge, 0)
	for _, v := range res {
		res := &biz.UserBadge{
			ID:   v.ID,
			Name: v.Name,
			Type: v.Type,
		}
		res.ImageURL, _ = url.JoinPath(
			r.data.conf.ObjectStorage.PublicBucket.Endpoint,
			r.data.conf.ObjectStorage.PublicBucket.Bucket,
			fmt.Sprintf(userBadgeFilePath, res.ID, v.Name+".png"),
		)
		res.ImageGifURL, _ = url.JoinPath(
			r.data.conf.ObjectStorage.PublicBucket.Endpoint,
			r.data.conf.ObjectStorage.PublicBucket.Bucket,
			fmt.Sprintf(userBadgeFilePath, res.ID, v.Name+".gif"),
		)
		rv = append(rv, res)
	}
	return rv, count
}

// GetUserBadge .
func (r *userRepo) GetUserBadge(ctx context.Context, id int) (*biz.UserBadge, error) {
	var u UserBadge
	err := r.data.db.Model(UserBadge{}).
		First(&u, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	res := &biz.UserBadge{
		ID:          u.ID,
		Name:        u.Name,
		Type:        u.Type,
		ImageURL:    u.Image,
		ImageGifURL: u.ImageGif,
	}
	res.ImageURL, _ = url.JoinPath(
		r.data.conf.ObjectStorage.PublicBucket.Endpoint,
		r.data.conf.ObjectStorage.PublicBucket.Bucket,
		fmt.Sprintf(userBadgeFilePath, res.ID, u.Name+".png"),
	)
	res.ImageGifURL, _ = url.JoinPath(
		r.data.conf.ObjectStorage.PublicBucket.Endpoint,
		r.data.conf.ObjectStorage.PublicBucket.Bucket,
		fmt.Sprintf(userBadgeFilePath, res.ID, u.Name+".gif"),
	)
	return res, err
}

// CreateUserBadge .
func (r *userRepo) CreateUserBadge(ctx context.Context, b *biz.UserBadge) (*biz.UserBadge, error) {
	res := UserBadge{
		Name: b.Name,
		Type: b.Type,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	if err != nil {
		return nil, err
	}
	store := objectstorage.NewSeaweed()
	pngStoreName := fmt.Sprintf(userBadgeFilePath, res.ID, b.Name+".png")
	err = store.PutObject(r.data.conf.ObjectStorage.PublicBucket, pngStoreName, bytes.NewReader(b.Image))
	if err != nil {
		return nil, err
	}
	gifStoreName := fmt.Sprintf(userBadgeFilePath, res.ID, b.Name+".gif")
	err = store.PutObject(r.data.conf.ObjectStorage.PublicBucket, gifStoreName, bytes.NewReader(b.ImageGif))
	if err != nil {
		return nil, err
	}
	res.Image, _ = url.JoinPath(
		r.data.conf.ObjectStorage.PublicBucket.Endpoint,
		r.data.conf.ObjectStorage.PublicBucket.Bucket,
		pngStoreName,
	)
	res.ImageGif, _ = url.JoinPath(
		r.data.conf.ObjectStorage.PublicBucket.Endpoint,
		r.data.conf.ObjectStorage.PublicBucket.Bucket,
		gifStoreName,
	)
	r.data.db.WithContext(ctx).
		Updates(&res)
	return &biz.UserBadge{
		ID:          res.ID,
		Name:        res.Name,
		Type:        res.Type,
		ImageURL:    res.Image,
		ImageGifURL: res.ImageGif,
	}, err
}

// UpdateUserBadge .
func (r *userRepo) UpdateUserBadge(ctx context.Context, b *biz.UserBadge) (*biz.UserBadge, error) {
	res := UserBadge{
		ID:   b.ID,
		Name: b.Name,
		Type: b.Type,
	}
	store := objectstorage.NewSeaweed()
	pngStoreName := fmt.Sprintf(userBadgeFilePath, res.ID, b.Name+".png")
	err := store.PutObject(r.data.conf.ObjectStorage.PublicBucket, pngStoreName, bytes.NewReader(b.Image))
	if err != nil {
		return nil, err
	}
	gifStoreName := fmt.Sprintf(userBadgeFilePath, res.ID, b.Name+".gif")
	err = store.PutObject(r.data.conf.ObjectStorage.PublicBucket, gifStoreName, bytes.NewReader(b.ImageGif))
	if err != nil {
		return nil, err
	}
	res.Image, _ = url.JoinPath(
		r.data.conf.ObjectStorage.PublicBucket.Endpoint,
		r.data.conf.ObjectStorage.PublicBucket.Bucket,
		pngStoreName,
	)
	res.ImageGif, _ = url.JoinPath(
		r.data.conf.ObjectStorage.PublicBucket.Endpoint,
		r.data.conf.ObjectStorage.PublicBucket.Bucket,
		gifStoreName,
	)
	err = r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteUserBadge .
func (r *userRepo) DeleteUserBadge(ctx context.Context, id int) error {
	var u UserBadge
	err := r.data.db.Model(UserBadge{}).
		First(&u, "id = ?", id).Error
	if err != nil {
		return err
	}
	store := objectstorage.NewSeaweed()
	pngStoreName := fmt.Sprintf(userBadgeFilePath, u.ID, u.Name+".png")
	gifStoreName := fmt.Sprintf(userBadgeFilePath, u.ID, u.Name+".gif")
	store.DeleteObject(r.data.conf.ObjectStorage.PublicBucket, pngStoreName)
	store.DeleteObject(r.data.conf.ObjectStorage.PublicBucket, gifStoreName)
	err = r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(UserBadge{ID: id}).
		Error
	return err
}
