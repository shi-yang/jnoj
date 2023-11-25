package data

import (
	"bytes"
	"context"
	"fmt"
	"sort"
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
	IsTestPoint   bool // 是否测试点
	CreatedAt     time.Time
	UpdatedAt     time.Time
}

// 测试点的储存路径
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
			IsTestPoint:   v.IsTestPoint,
			InputSize:     v.InputSize,
			InputPreview:  v.InputPreview,
			OutputSize:    v.OutputSize,
			OutputPreview: v.OutputPreview,
			Order:         v.Order,
		})
	}
	return res, count
}

func (r *problemRepo) ListProblemTestContent(ctx context.Context, id int, testIds []int, isExample bool) ([]*biz.Test, error) {
	var tests []ProblemTest
	db := r.data.db.WithContext(ctx).
		Model(&ProblemTest{}).
		Where("problem_id = ?", id)
	if isExample {
		db.Where("is_example = ?", isExample)
	}
	if len(testIds) > 0 {
		db.Where("id in (?)", testIds)
	}
	db.Find(&tests)

	res := make([]*biz.Test, 0)
	for _, v := range tests {
		store := objectstorage.NewSeaweed()
		in, _ := store.GetObject(r.data.conf.ObjectStorage.PrivateBucket, fmt.Sprintf(problemTestInputPath, id, v.ID))
		out, _ := store.GetObject(r.data.conf.ObjectStorage.PrivateBucket, fmt.Sprintf(problemTestOutputPath, id, v.ID))
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
		IsTestPoint:   res.IsTestPoint,
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
		IsTestPoint:   b.IsTestPoint,
		InputSize:     b.InputSize,
		InputPreview:  b.InputPreview,
		OutputSize:    b.OutputSize,
		OutputPreview: b.OutputPreview,
		Order:         b.Order,
	}
	tx := r.data.db.WithContext(ctx).Begin()
	err := tx.Create(o).
		Error
	if err != nil {
		tx.Rollback()
		return nil, err
	}
	// 保存文件
	if len(b.InputFileContent) > 0 {
		store := objectstorage.NewSeaweed()
		storeName := fmt.Sprintf(problemTestInputPath, b.ProblemID, o.ID)
		err := store.PutObject(r.data.conf.ObjectStorage.PrivateBucket, storeName, bytes.NewReader(b.InputFileContent))
		if err != nil {
			tx.Rollback()
			return nil, err
		}
	}
	tx.Commit()
	return &biz.ProblemTest{
		ID: o.ID,
	}, err
}

// UpdateProblemTest .
func (r *problemRepo) UpdateProblemTest(ctx context.Context, p *biz.ProblemTest) (*biz.ProblemTest, error) {
	err := r.data.db.WithContext(ctx).
		Select("IsExample", "IsTestPoint", "Remark").
		Omit(clause.Associations).
		Model(&ProblemTest{ID: p.ID}).
		Updates(map[string]interface{}{
			"is_example":    p.IsExample,
			"is_test_point": p.IsTestPoint,
			"remark":        p.Remark,
		}).Error
	return nil, err
}

// DeleteProblemTest .
func (r *problemRepo) DeleteProblemTest(ctx context.Context, pid int, testIds []int32) error {
	var res []ProblemTest
	r.data.db.Model(&ProblemTest{}).
		Find(&res, "problem_id = ? and id in (?)", pid, testIds)

	for _, v := range res {
		err := r.data.db.WithContext(ctx).
			Omit(clause.Associations).
			Delete(ProblemTest{}, "id = ?", v.ID).
			Error
		// 删除数据库的记录
		if err != nil {
			return err
		}
		// 删除文件
		store := objectstorage.NewSeaweed()
		store.DeleteObject(r.data.conf.ObjectStorage.PrivateBucket, fmt.Sprintf(problemTestInputPath, pid, v.ID))
		store.DeleteObject(r.data.conf.ObjectStorage.PrivateBucket, fmt.Sprintf(problemTestOutputPath, pid, v.ID))
	}
	return nil
}

func (r *problemRepo) IsProblemTestSampleFirst(ctx context.Context, pid int) bool {
	var sampleOrder []int
	r.data.db.WithContext(ctx).
		Select("`order`").
		Model(&ProblemTest{}).
		Where("problem_id = ?", pid).
		Where("is_example = ?", true).
		Find(&sampleOrder)
	sort.Slice(sampleOrder, func(i, j int) bool {
		return sampleOrder[i] < sampleOrder[j]
	})
	for index, v := range sampleOrder {
		if index+1 != v {
			return false
		}
	}
	return true
}

func (r *problemRepo) SortProblemTests(ctx context.Context, req *v1.SortProblemTestsRequest) {
	if req.SetSampleFirst != nil && *req.SetSampleFirst {
		tests, _ := r.ListProblemTests(ctx, &v1.ListProblemTestsRequest{
			Id:      req.Id,
			Page:    -1,
			PerPage: -1,
		})
		sort.Slice(tests, func(i, j int) bool {
			if tests[i].IsExample {
				return true
			}
			if tests[j].IsExample {
				return false
			}
			return tests[i].Order < tests[j].Order
		})
		for index, test := range tests {
			r.data.db.WithContext(ctx).
				Model(&ProblemTest{ID: test.ID}).
				Update("order", index+1)
		}
	} else if req.SortByName != nil && *req.SortByName {
		tests, _ := r.ListProblemTests(ctx, &v1.ListProblemTestsRequest{
			Id:      req.Id,
			Page:    -1,
			PerPage: -1,
		})
		sort.Slice(tests, func(i, j int) bool {
			si, sj := tests[i].Name, tests[j].Name
			ni, nj := len(si), len(sj)
			for k := 0; k < ni && k < nj; k++ {
				ci, cj := si[k], sj[k]
				if ci != cj {
					return ci < cj
				}
			}
			return ni < nj
		})
		for index, test := range tests {
			r.data.db.WithContext(ctx).
				Model(&ProblemTest{ID: test.ID}).
				Update("order", index+1)
		}
	} else if len(req.Ids) > 0 {
		var orderMin int
		r.data.db.WithContext(ctx).
			Select("min(`order`)").
			Model(&ProblemTest{}).
			Where("id in (?)", req.Ids).
			Scan(&orderMin)
		for index, id := range req.Ids {
			r.data.db.WithContext(ctx).
				Model(&ProblemTest{ID: int(id)}).
				Update("order", index+orderMin)
		}
	}
}
