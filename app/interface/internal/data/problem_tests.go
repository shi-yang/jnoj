package data

import (
	"bytes"
	"context"
	"fmt"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	objectstorage "jnoj/pkg/object_storage"
	"jnoj/pkg/pagination"

	"gorm.io/gorm/clause"
)

type ProblemTest struct {
	ID            int
	ProblemID     int
	Order         int
	Name          string // 测试点名称
	InputSize     int64  // 输入文件大小
	InputPreview  string // 输入文件预览
	OutputSize    int64  // 输出文件大小
	OutputPreview string // 输出文件预览
	Remark        string
	UserID        int
	IsExample     bool
	CreatedAt     time.Time
	UpdatedAt     time.Time
}

const problemTestInputPath = "/problem_tests/%d/%d.in"
const problemTestOutputPath = "/problem_tests/%d/%d.out"

// ListProblemTests 获取题目的测试点列表
func (r *problemRepo) ListProblemTests(ctx context.Context, req *v1.ListProblemTestsRequest) ([]*biz.ProblemTest, int64) {
	var (
		tests []ProblemTest
		count int64
	)
	db := r.data.db.WithContext(ctx).
		Model(&ProblemTest{}).
		Where("problem_id = ?", req.Id)

	page := pagination.NewPagination(req.Page, req.PerPage)
	db.Count(&count).
		Order("`order`").
		Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&tests)
	res := make([]*biz.ProblemTest, 0)
	for _, v := range tests {
		res = append(res, &biz.ProblemTest{
			ID:            v.ID,
			Name:          v.Name,
			CreatedAt:     v.CreatedAt,
			Remark:        v.Remark,
			IsExample:     v.IsExample,
			InputSize:     v.InputSize,
			InputPreview:  v.InputPreview,
			OutputSize:    v.OutputSize,
			OutputPreview: v.OutputPreview,
			Order:         v.Order,
		})
	}
	return res, count
}

func (r *problemRepo) ListProblemTestContent(ctx context.Context, id int, isExample bool) ([]*biz.Test, error) {
	var tests []ProblemTest
	db := r.data.db.WithContext(ctx).
		Model(&ProblemTest{}).
		Where("problem_id = ?", id)
	if isExample {
		db.Where("is_example = ?", isExample)
	}
	db.Find(&tests)

	res := make([]*biz.Test, 0)
	for _, v := range tests {
		store := objectstorage.NewSeaweed()
		in, _ := store.GetObject(r.data.conf.ObjectStorage, fmt.Sprintf(problemTestInputPath, id, v.ID))
		out, _ := store.GetObject(r.data.conf.ObjectStorage, fmt.Sprintf(problemTestOutputPath, id, v.ID))
		res = append(res, &biz.Test{
			ID:     v.ID,
			Input:  string(in),
			Output: string(out),
		})
	}
	return res, nil
}

// GetProblemTest .
func (r *problemRepo) GetProblemTest(ctx context.Context, id int) (*biz.ProblemTest, error) {
	var res ProblemTest
	err := r.data.db.Model(&ProblemTest{}).
		First(&res, "id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemTest{
		ID:            res.ID,
		ProblemID:     res.ProblemID,
		IsExample:     res.IsExample,
		Name:          res.Name,
		InputSize:     res.InputSize,
		InputPreview:  res.InputPreview,
		OutputSize:    res.OutputSize,
		OutputPreview: res.OutputPreview,
	}, nil
}

// CreateProblemTest .
func (r *problemRepo) CreateProblemTest(ctx context.Context, b *biz.ProblemTest) (*biz.ProblemTest, error) {
	o := &ProblemTest{
		ProblemID:     b.ProblemID,
		Name:          b.Name,
		IsExample:     b.IsExample,
		InputSize:     b.InputSize,
		InputPreview:  b.InputPreview,
		OutputSize:    b.OutputSize,
		OutputPreview: b.OutputPreview,
		Order:         b.Order,
	}
	err := r.data.db.WithContext(ctx).
		Create(o).
		Error
	if err != nil {
		return nil, err
	}
	// 保存文件
	if len(b.InputFileContent) > 0 {
		store := objectstorage.NewSeaweed()
		storeName := fmt.Sprintf(problemTestInputPath, b.ProblemID, o.ID)
		store.PutObject(r.data.conf.ObjectStorage, storeName, bytes.NewReader(b.InputFileContent))
	}
	return &biz.ProblemTest{
		ID: o.ID,
	}, err
}

// UpdateProblemTest .
func (r *problemRepo) UpdateProblemTest(ctx context.Context, p *biz.ProblemTest) (*biz.ProblemTest, error) {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Model(&ProblemTest{ID: p.ID}).
		Updates(map[string]interface{}{
			"is_example": p.IsExample,
			"remark":     p.Remark,
		}).Error
	return nil, err
}

// DeleteProblemTest .
func (r *problemRepo) DeleteProblemTest(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(ProblemTest{ID: id}).
		Error
	return err
}

func (r *problemRepo) SortProblemTests(ctx context.Context, ids []int32) {
	for index, id := range ids {
		r.data.db.WithContext(ctx).
			Model(&ProblemTest{ID: int(id)}).
			Update("order", index)
	}
}
