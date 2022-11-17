package biz

import (
	"context"
	"errors"
	v1 "jnoj/api/interface/v1"
	"time"
)

// ContestProblem is a ContestProblem model.
type ContestProblem struct {
	ID            int
	Name          string
	Number        int // 题目次序、A、B、C、D
	ContestID     int
	ProblemID     int
	SubmitCount   int
	AcceptedCount int
	TimeLimit     int64
	Memory        int64
	Statements    []*ProblemStatement
	SampleTest    []*SampleTest
	CreatedAt     time.Time
}

// ContestProblemRepo is a ContestProblem repo.
type ContestProblemRepo interface {
	ListContestProblems(context.Context, *v1.ListContestProblemsRequest) ([]*ContestProblem, int64)
	GetContestProblemByProblemID(context.Context, int, int) (*ContestProblem, error)
	GetContestProblemByNumber(context.Context, int, int) (*ContestProblem, error)
	CreateContestProblem(context.Context, *ContestProblem) (*ContestProblem, error)
	UpdateContestProblem(context.Context, *ContestProblem) (*ContestProblem, error)
	DeleteContestProblem(context.Context, int) error
	CountContestProblem(context.Context, int) int
}

// ListContestProblems list ContestProblem
func (uc *ContestUsecase) ListContestProblems(ctx context.Context, req *v1.ListContestProblemsRequest) ([]*ContestProblem, int64) {
	return uc.repo.ListContestProblems(ctx, req)
}

// GetContestProblem get a ContestProblem
func (uc *ContestUsecase) GetContestProblem(ctx context.Context, cid int, number int) (*ContestProblem, error) {
	return uc.repo.GetContestProblemByNumber(ctx, cid, number)
}

// CreateContestProblem creates a ContestProblem, and returns the new ContestProblem.
func (uc *ContestUsecase) CreateContestProblem(ctx context.Context, c *ContestProblem) (*ContestProblem, error) {
	// 检查题目是否已经在比赛里
	_, err := uc.repo.GetContestProblemByProblemID(ctx, c.ContestID, c.ProblemID)
	if err == nil {
		return nil, errors.New("题目已经存在")
	}
	// 统计现有题目数量，以增加题目序号
	count := uc.repo.CountContestProblem(ctx, c.ContestID)
	if count > 25 {
		return nil, errors.New("已达单场比赛最大题目数量")
	}
	c.Number = count
	return uc.repo.CreateContestProblem(ctx, c)
}

// UpdateContestProblem update a ContestProblem
func (uc *ContestUsecase) UpdateContestProblem(ctx context.Context, p *ContestProblem) (*ContestProblem, error) {
	return uc.repo.UpdateContestProblem(ctx, p)
}

// DeleteContestProblem delete a ContestProblem
func (uc *ContestUsecase) DeleteContestProblem(ctx context.Context, id int) error {
	return uc.repo.DeleteContestProblem(ctx, id)
}
