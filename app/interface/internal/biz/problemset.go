package biz

import (
	"context"
	"fmt"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Problemset is a Problemset model.
type Problemset struct {
	ID           int
	Name         string
	UserID       int
	Order        int
	Description  string
	ProblemCount int
	CreatedAt    time.Time
}

// HasPermission 是否有权限修改
func (p *Problemset) HasPermission(ctx context.Context) bool {
	uid, _ := auth.GetUserID(ctx)
	fmt.Println(uid, p.UserID)
	return uid == p.UserID
}

// ProblemsetProblem Problemset's Problem model.
type ProblemsetProblem struct {
	ID            int
	Name          string
	Order         int // 题目次序 1,2,3,4
	ProblemID     int
	ProblemsetID  int
	SubmitCount   int
	AcceptedCount int
	Source        string
	CreatedAt     time.Time
}

// ProblemsetRepo is a Problemset repo.
type ProblemsetRepo interface {
	ListProblemsets(context.Context, *v1.ListProblemsetsRequest) ([]*Problemset, int64)
	GetProblemset(context.Context, int) (*Problemset, error)
	CreateProblemset(context.Context, *Problemset) (*Problemset, error)
	UpdateProblemset(context.Context, *Problemset) (*Problemset, error)
	DeleteProblemset(context.Context, int) error
	ListProblemsetProblems(context.Context, *v1.ListProblemsetProblemsRequest) ([]*ProblemsetProblem, int64)
	GetProblemsetProblem(ctx context.Context, sid int, order int) (*ProblemsetProblem, error)
	AddProblemToProblemset(ctx context.Context, sid int, pid int) error
	DeleteProblemFromProblemset(ctx context.Context, sid int, order int) error
	SortProblemsetProblems(ctx context.Context, req *v1.SortProblemsetProblemsRequest) error
}

// ProblemsetUsecase is a Problemset usecase.
type ProblemsetUsecase struct {
	repo        ProblemsetRepo
	problemRepo ProblemRepo
	log         *log.Helper
}

// NewProblemsetUsecase new a Problemset usecase.
func NewProblemsetUsecase(repo ProblemsetRepo, problemRepo ProblemRepo, logger log.Logger) *ProblemsetUsecase {
	return &ProblemsetUsecase{
		repo:        repo,
		problemRepo: problemRepo,
		log:         log.NewHelper(logger),
	}
}

// ListProblemsets list Problemset
func (uc *ProblemsetUsecase) ListProblemsets(ctx context.Context, req *v1.ListProblemsetsRequest) ([]*Problemset, int64) {
	return uc.repo.ListProblemsets(ctx, req)
}

// GetProblemset get a Problemset
func (uc *ProblemsetUsecase) GetProblemset(ctx context.Context, id int) (*Problemset, error) {
	return uc.repo.GetProblemset(ctx, id)
}

// CreateProblemset creates a Problemset, and returns the new Problemset.
func (uc *ProblemsetUsecase) CreateProblemset(ctx context.Context, g *Problemset) (*Problemset, error) {
	return uc.repo.CreateProblemset(ctx, g)
}

// UpdateProblemset update a Problemset
func (uc *ProblemsetUsecase) UpdateProblemset(ctx context.Context, p *Problemset) (*Problemset, error) {
	return uc.repo.UpdateProblemset(ctx, p)
}

// DeleteProblemset delete a Problemset
func (uc *ProblemsetUsecase) DeleteProblemset(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemset(ctx, id)
}

func (uc *ProblemsetUsecase) ListProblemsetProblems(ctx context.Context, req *v1.ListProblemsetProblemsRequest) ([]*ProblemsetProblem, int64) {
	return uc.repo.ListProblemsetProblems(ctx, req)
}

func (uc *ProblemsetUsecase) GetProblemsetProblem(ctx context.Context, sid int, order int) (*ProblemsetProblem, error) {
	return uc.repo.GetProblemsetProblem(ctx, sid, order)
}

func (uc *ProblemsetUsecase) AddProblemToProblemset(ctx context.Context, sid int, pid int) error {
	return uc.repo.AddProblemToProblemset(ctx, sid, pid)
}

func (uc *ProblemsetUsecase) DeleteProblemFromProblemset(ctx context.Context, sid int, pid int) error {
	return uc.repo.DeleteProblemFromProblemset(ctx, sid, pid)
}

func (uc *ProblemsetUsecase) SortProblemsetProblems(ctx context.Context, req *v1.SortProblemsetProblemsRequest) error {
	return uc.repo.SortProblemsetProblems(ctx, req)
}
