package data

import (
	"context"
	"fmt"
	"strings"
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
	User              *User               `gorm:"ForeignKey:UserID"`
	ProblemStatements []*ProblemStatement `gorm:"ForeignKey:ProblemID"`
	ProblemTags       []*ProblemTag       `gorm:"many2many:problem_tag_problem"`
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
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname, username")
		}).
		Preload("ProblemStatements", func(db *gorm.DB) *gorm.DB {
			return db.Select("name, language, problem_id")
		}).
		Preload("ProblemTags")
	if len(req.Status) > 0 {
		db.Where("status in (?)", req.Status)
	}
	if len(req.Type) > 0 {
		db.Where("type in (?)", req.Type)
	}
	if req.UserId != 0 {
		db.Where("user_id = ?", req.UserId)
	} else {
		// 不查自己的，只能查公开的
		db.Where("status = ?", biz.ProblemStatusPublic)
	}
	if req.Id != 0 {
		db.Where("problem.id = ?", req.Id)
	}
	if req.Keyword != "" {
		db.Where("name like ? or id in (?)", fmt.Sprintf("%%%s%%", req.Keyword),
			r.data.db.WithContext(ctx).Select("problem_id").
				Model(&ProblemStatement{}).Where("name like ?", fmt.Sprintf("%%%s%%", req.Keyword))).
			Or("source like ?", fmt.Sprintf("%%%s%%", req.Keyword))
	}
	if req.OrderBy != nil {
		if strings.Contains(*req.OrderBy, "desc") {
			db.Order("id desc")
		} else {
			db.Order("id")
		}
	}
	db.Count(&count)
	db.Offset(pager.GetOffset()).
		Limit(pager.GetPageSize()).
		Find(&res)

	var ids []int
	rv := make([]*biz.Problem, 0)
	for _, v := range res {
		ids = append(ids, v.ID)
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
		if v.User != nil {
			p.Nickname = v.User.Nickname
		}
		for _, t := range v.ProblemTags {
			p.Tags = append(p.Tags, t.Name)
		}
		for _, s := range v.ProblemStatements {
			p.Statements = append(p.Statements, &biz.ProblemStatement{
				Name:     s.Name,
				Language: s.Language,
			})
		}
		rv = append(rv, p)
	}
	// 查询是否允许下载
	var allowdownloadIds []int
	r.data.db.WithContext(ctx).
		Select("problem_id").
		Model(&ProblemFile{}).
		Where("problem_id in (?)", ids).
		Where("file_type = ?", biz.ProblemFileFileTypePackage).
		Scan(&allowdownloadIds)
	r.log.Info(allowdownloadIds)
	for _, v := range allowdownloadIds {
		for k, p := range rv {
			if p.ID == v && p.Status == biz.ProblemStatusPublic {
				rv[k].AllowDownload = true
			}
		}
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

// GetProblemsStatus 查询题目的解答情况
func (r *problemRepo) GetProblemsStatus(ctx context.Context, entityType int, entityID int, userId int, problemId []int) map[int]int {
	res := make(map[int]int)
	var submissions []Submission
	r.data.db.WithContext(ctx).
		Model(&Submission{}).
		Select("verdict, problem_id").
		Where("user_id = ?", userId).
		Where("problem_id in (?)", problemId).
		Where("entity_type = ?", entityType).
		Where("entity_id = ?", entityID).
		Find(&submissions)
	for _, v := range submissions {
		if res[v.ProblemID] == biz.ProblemStatusSolved {
			continue
		}
		if v.Verdict == biz.SubmissionVerdictAccepted {
			res[v.ProblemID] = biz.ProblemStatusSolved
		} else {
			res[v.ProblemID] = biz.ProblemStatusAttempted
		}
	}
	return res
}
