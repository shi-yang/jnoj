package data

import (
	"context"
	"errors"
	"math"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type ProblemsetRepo struct {
	data *Data
	log  *log.Helper
}

type Problemset struct {
	ID           int
	Name         string
	Description  string
	UserID       int
	ProblemCount int
	CreatedAt    time.Time
	UpdatedAt    time.Time
	DeletedAt    gorm.DeletedAt
}

type ProblemsetProblem struct {
	ID           int
	ProblemID    int
	ProblemsetID int
	Order        int
	Problem      *Problem `gorm:"ForeignKey:ProblemID"`
}

// NewProblemsetRepo .
func NewProblemsetRepo(data *Data, logger log.Logger) biz.ProblemsetRepo {
	return &ProblemsetRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListProblemsets .
func (r *ProblemsetRepo) ListProblemsets(ctx context.Context, req *v1.ListProblemsetsRequest) ([]*biz.Problemset, int64) {
	res := []Problemset{}
	count := int64(0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&res)
	db.Count(&count)
	db.Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&res)
	rv := make([]*biz.Problemset, 0)
	for _, v := range res {
		rv = append(rv, &biz.Problemset{
			ID:           v.ID,
			Name:         v.Name,
			Description:  v.Description,
			CreatedAt:    v.CreatedAt,
			ProblemCount: v.ProblemCount,
			UserID:       v.UserID,
		})
	}
	return rv, count
}

// GetProblemset .
func (r *ProblemsetRepo) GetProblemset(ctx context.Context, id int) (*biz.Problemset, error) {
	var res Problemset
	err := r.data.db.Model(Problemset{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.Problemset{
		ID:           res.ID,
		Name:         res.Name,
		Description:  res.Description,
		ProblemCount: res.ProblemCount,
		CreatedAt:    res.CreatedAt,
		UserID:       res.UserID,
	}, err
}

// CreateProblemset .
func (r *ProblemsetRepo) CreateProblemset(ctx context.Context, b *biz.Problemset) (*biz.Problemset, error) {
	res := Problemset{
		Name:        b.Name,
		UserID:      b.UserID,
		Description: b.Description,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Problemset{
		ID: res.ID,
	}, err
}

// UpdateProblemset .
func (r *ProblemsetRepo) UpdateProblemset(ctx context.Context, b *biz.Problemset) (*biz.Problemset, error) {
	err := r.data.db.WithContext(ctx).
		Model(&Problemset{ID: b.ID}).
		Updates(map[string]interface{}{
			"name":        b.Name,
			"description": b.Description,
		}).Error
	return &biz.Problemset{ID: b.ID}, err
}

// DeleteProblemset .
func (r *ProblemsetRepo) DeleteProblemset(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(Problemset{ID: id}).
		Error
	return err
}

func (r *ProblemsetRepo) ListProblemsetProblems(ctx context.Context, req *v1.ListProblemsetProblemsRequest) ([]*biz.ProblemsetProblem, int64) {
	res := []ProblemsetProblem{}
	count := int64(0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&ProblemsetProblem{}).
		Preload("Problem", func(db *gorm.DB) *gorm.DB {
			return db.Select("name, id, submit_count, accepted_count")
		}).
		Preload("Problem.ProblemStatements", func(db *gorm.DB) *gorm.DB {
			return db.Select("name, language, problem_id")
		}).
		Preload("Problem.ProblemTags")
	db.Where("problemset_id = ?", req.Id).
		Order("`order`").
		Count(&count)
	db.
		Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&res)
	rv := make([]*biz.ProblemsetProblem, 0)
	for _, v := range res {
		p := &biz.ProblemsetProblem{
			ID:            v.ID,
			Order:         v.Order,
			SubmitCount:   v.Problem.SubmitCount,
			AcceptedCount: v.Problem.AcceptedCount,
			Source:        v.Problem.Source,
			ProblemsetID:  v.ProblemsetID,
			ProblemID:     v.ProblemID,
		}
		for _, v := range v.Problem.ProblemTags {
			p.Tags = append(p.Tags, v.Name)
		}
		if len(v.Problem.ProblemStatements) > 0 {
			p.Name = v.Problem.ProblemStatements[0].Name
		}
		rv = append(rv, p)
	}
	return rv, count
}

// GetProblemsetProblem .
func (r *ProblemsetRepo) GetProblemsetProblem(ctx context.Context, sid int, order int) (*biz.ProblemsetProblem, error) {
	var p ProblemsetProblem
	err := r.data.db.Model(&ProblemsetProblem{}).
		First(&p, "problemset_id = ? and `order` = ?", sid, order).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemsetProblem{
		ID:           p.ID,
		ProblemID:    p.ProblemID,
		ProblemsetID: p.ProblemsetID,
		Order:        p.Order,
	}, nil
}

// AddProblemToProblemset .
func (r *ProblemsetRepo) AddProblemToProblemset(ctx context.Context, sid int, pid int) error {
	// 判断是否已经存在
	var count int64
	r.data.db.WithContext(ctx).
		Model(&ProblemsetProblem{}).
		Where("problemset_id = ? and problem_id = ?", sid, pid).
		Count(&count)
	if count > 0 {
		return errors.New("已经存在")
	}

	var maxOrder int
	db := r.data.db.WithContext(ctx).Begin()
	db.Select("max(`order`)").Model(&ProblemsetProblem{}).Where("problemset_id = ?", sid).Scan(&maxOrder)
	err := db.Create(&ProblemsetProblem{
		ProblemID:    pid,
		ProblemsetID: sid,
		Order:        maxOrder + 1,
	}).Error
	if err != nil {
		db.Rollback()
		return err
	}
	db.Model(&Problemset{ID: sid}).
		UpdateColumn("problem_count", gorm.Expr("problem_count + 1"))
	db.Commit()
	return nil
}

// DeleteProblemFromProblemset .
func (r *ProblemsetRepo) DeleteProblemFromProblemset(ctx context.Context, sid int, order int) error {
	tx := r.data.db.WithContext(ctx).Begin()
	err := tx.Delete(&ProblemsetProblem{}, "problemset_id = ? and `order` = ?", sid, order).Error
	if err != nil {
		tx.Rollback()
		return err
	}
	tx.Model(&Problemset{ID: sid}).
		UpdateColumn("problem_count", gorm.Expr("problem_count - 1"))
	// 调整移除后的顺序
	var ids []int
	tx.Select("id").Model(&ProblemsetProblem{}).Where("problemset_id = ? and `order` > ?", sid, order).Scan(&ids)
	for index, id := range ids {
		err := tx.Model(&ProblemsetProblem{ID: id}).
			Update("`order`", order+index).
			Error
		if err != nil {
			tx.Rollback()
			return err
		}
	}
	tx.Commit()
	return nil
}

// SortProblemsetProblems .
func (r *ProblemsetRepo) SortProblemsetProblems(ctx context.Context, req *v1.SortProblemsetProblemsRequest) error {
	min := math.MaxInt
	for _, v := range req.Ids {
		if min > int(v.Order) {
			min = int(v.Order)
		}
	}
	tx := r.data.db.WithContext(ctx).Begin()
	for index, item := range req.Ids {
		// 没有变化的不用调整
		if min+index == int(item.Order) {
			continue
		}
		err := tx.Model(&ProblemsetProblem{ID: int(item.Id)}).
			Update("`order`", min+index).
			Error
		if err != nil {
			tx.Rollback()
			return err
		}
	}
	tx.Commit()
	return nil
}
