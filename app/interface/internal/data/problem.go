package data

import (
	"context"
	"fmt"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type problemRepo struct {
	data *Data
	log  *log.Helper
}

type Problem struct {
	ID                int
	Name              string
	TimeLimit         int64
	MemoryLimit       int64
	AcceptedCount     int
	SubmitCount       int
	UserID            int
	Type              int
	CheckerID         int
	Status            int
	Source            string
	CreatedAt         time.Time
	UpdatedAt         time.Time
	DeletedAt         gorm.DeletedAt
	ProblemStatements []*ProblemStatement `gorm:"ForeignKey:ProblemID"`
}

// NewProblemRepo .
func NewProblemRepo(data *Data, logger log.Logger) biz.ProblemRepo {
	return &problemRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListProblems .
func (r *problemRepo) ListProblems(ctx context.Context, req *v1.ListProblemsRequest) ([]*biz.Problem, int64) {
	res := []Problem{}
	count := int64(0)
	pager := pagination.NewPagination(req.Page, req.PerPage)

	db := r.data.db.WithContext(ctx).
		Model(&Problem{}).
		Preload("ProblemStatements", func(db *gorm.DB) *gorm.DB {
			return db.Select("name, language, problem_id")
		})
	db.Where("user_id = ? or status = ?", req.UserId, biz.ProblemStatusPublic)
	if req.Name != "" {
		db.Where("name like ? or id in (?)", fmt.Sprintf("%%%s%%", req.Name),
			r.data.db.WithContext(ctx).Select("problem_id").
				Model(&ProblemStatement{}).Where("name like ?", fmt.Sprintf("%%%s%%", req.Name)))
	}
	db.Count(&count)
	db.Offset(pager.GetOffset()).
		Limit(pager.GetPageSize()).
		Find(&res)
	rv := make([]*biz.Problem, 0)
	for _, v := range res {
		p := &biz.Problem{
			ID:            v.ID,
			Name:          v.Name,
			Type:          v.Type,
			SubmitCount:   v.SubmitCount,
			AcceptedCount: v.AcceptedCount,
			CreatedAt:     v.CreatedAt,
			UpdatedAt:     v.UpdatedAt,
			UserID:        v.UserID,
			Status:        v.Status,
			Source:        v.Source,
		}
		for _, s := range v.ProblemStatements {
			p.Statements = append(p.Statements, &biz.ProblemStatement{
				Name:     s.Name,
				Language: s.Language,
			})
		}
		rv = append(rv, p)
	}
	return rv, count
}

// GetProblem .
func (r *problemRepo) GetProblem(ctx context.Context, id int) (*biz.Problem, error) {
	var res Problem
	err := r.data.db.Model(&Problem{}).
		First(&res, "id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.Problem{
		ID:            res.ID,
		Name:          res.Name,
		TimeLimit:     res.TimeLimit,
		MemoryLimit:   res.MemoryLimit,
		AcceptedCount: res.AcceptedCount,
		SubmitCount:   res.SubmitCount,
		UserID:        res.UserID,
		Type:          res.Type,
		Status:        res.Status,
		CheckerID:     res.CheckerID,
		Source:        res.Source,
	}, err
}

// CreateProblem .
func (r *problemRepo) CreateProblem(ctx context.Context, p *biz.Problem) (*biz.Problem, error) {
	res := Problem{
		Name:        p.Name,
		UserID:      p.UserID,
		TimeLimit:   p.TimeLimit,
		MemoryLimit: p.MemoryLimit,
		Status:      p.Status,
		Type:        p.Type,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Problem{
		ID: res.ID,
	}, err
}

// UpdateProblem .
func (r *problemRepo) UpdateProblem(ctx context.Context, p *biz.Problem) (*biz.Problem, error) {
	update := Problem{
		ID:            p.ID,
		TimeLimit:     p.TimeLimit,
		MemoryLimit:   p.MemoryLimit,
		Status:        p.Status,
		SubmitCount:   p.SubmitCount,
		AcceptedCount: p.AcceptedCount,
		Source:        p.Source,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&update).Error
	return nil, err
}

// DeleteProblem .
func (r *problemRepo) DeleteProblem(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(Problem{ID: id}).
		Error
	return err
}

func (r *problemRepo) UpdateProblemChecker(ctx context.Context, id int, checkerID int) error {
	update := Problem{
		ID:        id,
		CheckerID: checkerID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&update).Error
	return err
}
