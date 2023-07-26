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
	Language  int    // 语言
	Content   string // 文件内容或路径
	Type      string
	FileSize  int64 // 文件大小
	ProblemID int
	UserID    int
	FileType  string // 业务类型
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

// 定义文件储存路径。定义了的储存在公开的对象储存，没有定义的直接储存在数据库
// %d problemId
// %s filename
var problemFileStorePath = map[biz.ProblemFileFileType]string{
	biz.ProblemFileFileTypeAttachment: "/problem_files/%d/attachment/%s",
	biz.ProblemFileFileTypePackage:    "/problem_files/%d/package/%s",
	biz.ProblemFileFileTypeStatement:  "/problem_files/%d/statement/%s",
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
	if req.Type != "" {
		db.Where("type = ?", req.Type)
	}
	if req.Name != "" {
		db.Where("name = ?", req.Name)
	}
	db.Find(&res).
		Count(&count)
	rv := make([]*biz.ProblemFile, 0)
	for _, v := range res {
		i := &biz.ProblemFile{
			ID:        v.ID,
			Name:      v.Name,
			Language:  v.Language,
			Type:      v.Type,
			FileType:  v.FileType,
			FileSize:  v.FileSize,
			Content:   v.Content,
			CreatedAt: v.CreatedAt,
			UpdatedAt: v.UpdatedAt,
		}
		if _, ok := problemFileStorePath[biz.ProblemFileFileType(i.FileType)]; ok {
			i.Content, _ = url.JoinPath(
				r.data.conf.ObjectStorage.PublicBucket.Endpoint,
				r.data.conf.ObjectStorage.PublicBucket.Bucket,
				i.Content,
			)
		}
		rv = append(rv, i)
	}
	return rv, count
}

// GetProblemFile .
func (r *problemRepo) GetProblemFile(ctx context.Context, p *biz.ProblemFile) (*biz.ProblemFile, error) {
	var res ProblemFile
	err := r.data.db.Model(ProblemFile{}).
		First(&res, p).Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemFile{
		ID:        res.ID,
		ProblemID: res.ProblemID,
		FileType:  res.FileType,
		Name:      res.Name,
		Language:  res.Language,
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
		Language:  p.Language,
		Content:   p.Content,
		Type:      p.Type,
		UserID:    p.UserID,
		FileType:  p.FileType,
		FileSize:  p.FileSize,
	}
	// 保存文件
	if filepath, ok := problemFileStorePath[biz.ProblemFileFileType(p.FileType)]; ok {
		store := objectstorage.NewSeaweed()
		fileUnixName := strconv.FormatInt(time.Now().UnixNano(), 10)
		storeName := fmt.Sprintf(filepath, p.ProblemID, fileUnixName+path.Ext(p.Name))
		err := store.PutObject(r.data.conf.ObjectStorage.PublicBucket, storeName, bytes.NewReader(p.FileContent))
		if err != nil {
			return nil, err
		}
		res.Content = storeName
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	p.Content, _ = url.JoinPath(
		r.data.conf.ObjectStorage.PublicBucket.Endpoint,
		r.data.conf.ObjectStorage.PublicBucket.Bucket,
		res.Content,
	)
	return p, err
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
	if _, ok := problemFileStorePath[biz.ProblemFileFileType(p.FileType)]; ok {
		store := objectstorage.NewSeaweed()
		store.DeleteObject(r.data.conf.ObjectStorage.PublicBucket, p.Content)
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
