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

	"gorm.io/gorm"
	"gorm.io/gorm/clause"

	objectstorage "jnoj/pkg/object_storage"
)

type ProblemFile struct {
	ID        int
	Name      string
	Content   string
	Type      string
	ProblemID int
	UserID    int
	FileType  string
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

// ListProblemFiles .
func (r *problemRepo) ListProblemFiles(ctx context.Context, req *v1.ListProblemFilesRequest) ([]*biz.ProblemFile, int64) {
	res := make([]ProblemFile, 0)
	count := int64(0)
	db := r.data.db.WithContext(ctx).
		Where("problem_id = ?", req.Id)
	if req.FileType != "" {
		db.Where("file_type = ?", req.FileType)
	}
	db.Find(&res).
		Count(&count)
	rv := make([]*biz.ProblemFile, 0)
	for _, v := range res {
		i := &biz.ProblemFile{
			ID:        v.ID,
			Name:      v.Name,
			Type:      v.Type,
			FileType:  v.FileType,
			Content:   v.Content,
			CreatedAt: v.CreatedAt,
			UpdatedAt: v.UpdatedAt,
		}
		if i.FileType == string(biz.ProblemFileFileTypeAttachment) {
			i.Content, _ = url.JoinPath("http://localhost:8333", r.data.conf.ObjectStorage.Bucket, i.Content)
		}
		rv = append(rv, i)
	}
	return rv, count
}

// GetProblemFile .
func (r *problemRepo) GetProblemFile(ctx context.Context, id int) (*biz.ProblemFile, error) {
	var res ProblemFile
	err := r.data.db.Model(ProblemFile{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemFile{
		ID:        res.ID,
		ProblemID: res.ProblemID,
		Name:      res.Name,
		Content:   res.Content,
		Type:      res.Type,
		UserID:    res.UserID,
		CreatedAt: res.CreatedAt,
		UpdatedAt: res.UpdatedAt,
	}, err
}

// CreateProblemFile .
func (r *problemRepo) CreateProblemFile(ctx context.Context, p *biz.ProblemFile) (*biz.ProblemFile, error) {
	res := ProblemFile{
		ProblemID: p.ProblemID,
		Name:      p.Name,
		Content:   p.Content,
		Type:      p.Type,
		UserID:    p.UserID,
		FileType:  p.FileType,
	}
	// 保存文件
	if p.FileType == string(biz.ProblemFileFileTypeAttachment) {
		store := objectstorage.NewSeaweed()
		fileUnixName := strconv.FormatInt(time.Now().UnixNano(), 10)
		storeName := fmt.Sprintf("/problem_files/%d/attachments/%s", p.ProblemID, fileUnixName+path.Ext(p.Name))
		store.PutObject(r.data.conf.ObjectStorage, storeName, bytes.NewReader(p.FileContent))
		res.Content = storeName
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ProblemFile{
		ID:      res.ID,
		Content: res.Content,
		Name:    res.Name,
	}, err
}

// UpdateProblemFile .
func (r *problemRepo) UpdateProblemFile(ctx context.Context, p *biz.ProblemFile) (*biz.ProblemFile, error) {
	res := ProblemFile{
		ID:      p.ID,
		Name:    p.Name,
		Content: p.Content,
		Type:    p.Type,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteProblemFile .
func (r *problemRepo) DeleteProblemFile(ctx context.Context, id int) error {
	var p ProblemFile
	err := r.data.db.Model(ProblemFile{}).
		First(&p, "id = ?", id).Error
	if err != nil {
		return err
	}
	// 删除文件
	if p.FileType == string(biz.ProblemFileFileTypeAttachment) {
		store := objectstorage.NewSeaweed()
		store.DeleteObject(r.data.conf.ObjectStorage, p.Content)
	}
	err = r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(&ProblemFile{ID: id}).
		Error
	return err
}

func (r *problemRepo) GetProblemChecker(ctx context.Context, id int) (*biz.ProblemFile, error) {
	var res ProblemFile
	err := r.data.db.WithContext(ctx).
		Where("id = (?)", r.data.db.Select("checker_id").Model(&Problem{}).Where("id = ?", id)).
		First(&res).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemFile{
		ID:      res.ID,
		Name:    res.Name,
		Content: res.Content,
	}, nil
}
