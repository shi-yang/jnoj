package biz

import (
	"context"
	"errors"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"
)

// ContestProblem is a ContestProblem model.
type ContestProblem struct {
	ID            int
	Name          string
	Number        int // 题目次序、A、B、C、D
	Type          int
	ContestID     int
	ProblemID     int
	SubmitCount   int
	AcceptedCount int
	TimeLimit     int64
	Memory        int64
	Status        int // 解答情况
	Statements    []*ProblemStatement
	SampleTest    []*Test
	CreatedAt     time.Time
}

// ContestProblemRepo is a ContestProblem repo.
type ContestProblemRepo interface {
	ListContestProblems(context.Context, int) ([]*ContestProblem, int64)
	GetContestProblemByProblemID(context.Context, int, int) (*ContestProblem, error)
	GetContestProblemByNumber(context.Context, int, int) (*ContestProblem, error)
	CreateContestProblem(context.Context, *ContestProblem) (*ContestProblem, error)
	UpdateContestProblem(context.Context, *ContestProblem) (*ContestProblem, error)
	DeleteContestProblem(context.Context, int, int) error
	CountContestProblem(context.Context, int) int
}

// ListContestProblems list ContestProblem
func (uc *ContestUsecase) ListContestProblems(ctx context.Context, contest *Contest) ([]*ContestProblem, int64) {
	problems, count := uc.repo.ListContestProblems(ctx, contest.ID)
	// 登录用户查询解答情况
	uid, ok := auth.GetUserID(ctx)
	if ok {
		ids := make([]int, 0)
		for _, v := range problems {
			ids = append(ids, v.ProblemID)
		}
		statusMap := uc.problemRepo.GetProblemsStatus(ctx, SubmissionEntityTypeContest, &contest.ID, uid, ids)
		isOIMode := contest.Type == ContestTypeOI && contest.GetRunningStatus() != ContestRunningStatusFinished
		for k, v := range problems {
			if isOIMode && statusMap[v.ProblemID] != ProblemStatusNotStart {
				problems[k].Status = ProblemStatusAttempted
			} else {
				problems[k].Status = statusMap[v.ProblemID]
			}
			// OI比赛进行中不返回成功数量
			if isOIMode {
				problems[k].AcceptedCount = 0
			}
		}
	}
	// 如果没有比赛管理权限，不展示 problem_id
	canUpdate := contest.HasPermission(ctx, ContestPermissionUpdate)
	for k := range problems {
		if !canUpdate {
			problems[k].ProblemID = 0
		}
	}
	return problems, count
}

// GetContestProblem get a ContestProblem
func (uc *ContestUsecase) GetContestProblem(ctx context.Context, cid int, number int) (*ContestProblem, error) {
	return uc.repo.GetContestProblemByNumber(ctx, cid, number)
}

// CreateContestProblem creates a ContestProblem, and returns the new ContestProblem.
func (uc *ContestUsecase) CreateContestProblem(ctx context.Context, c *ContestProblem) (*ContestProblem, error) {
	// 检查题目是否存在
	problem, err := uc.problemRepo.GetProblem(ctx, c.ProblemID)
	if err != nil {
		return nil, errors.New("problem not found")
	}
	if !problem.HasPermission(ctx, ProblemPermissionView) {
		return nil, v1.ErrorPermissionDenied("problem permission denied")
	}
	verification, err := uc.problemRepo.GetProblemVerification(ctx, c.ProblemID)
	if err != nil || verification.VerificationStatus != VerificationStatusSuccess {
		return nil, v1.ErrorProblemNotVerification("problem not verification")
	}
	// 检查题目是否已经在比赛里
	_, err = uc.repo.GetContestProblemByProblemID(ctx, c.ContestID, c.ProblemID)
	if err == nil {
		return nil, errors.New("problem already exists")
	}
	// 统计现有题目数量，以增加题目序号
	count := uc.repo.CountContestProblem(ctx, c.ContestID)
	uc.log.Info(count)
	if count > 25 {
		return nil, errors.New("reached the maximum number(25) of problems in a single contest")
	}
	c.Number = count
	return uc.repo.CreateContestProblem(ctx, c)
}

// UpdateContestProblem update a ContestProblem
func (uc *ContestUsecase) UpdateContestProblem(ctx context.Context, p *ContestProblem) (*ContestProblem, error) {
	return uc.repo.UpdateContestProblem(ctx, p)
}

// DeleteContestProblem delete a ContestProblem
func (uc *ContestUsecase) DeleteContestProblem(ctx context.Context, cid int, problemNumber int) error {
	return uc.repo.DeleteContestProblem(ctx, cid, problemNumber)
}
