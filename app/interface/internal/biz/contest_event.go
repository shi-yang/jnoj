package biz

import (
	"context"
	"time"
)

// ContestEvent 比赛事件记录
type ContestEvent struct {
	Id        int
	ContestId int
	UserId    int
	ProblemId int
	Type      int
	CreatedAt time.Time

	ContestUser *ContestUser
}

type ContestEventRepo interface {
	GetContestEvent(ctx context.Context, id int) (*ContestEvent, error)
	ListContestEvents(ctx context.Context, contestId int, userId int) ([]*ContestEvent, int64)
	CreateContestEvent(context.Context, *ContestEvent, time.Time) error
}

const (
	ContestEventTypeFirstSolve = iota
	ContestEventTypeAK
	ContestEventTypeRankFirst
)

// ListContestEvents list ContestEvent
func (uc *ContestUsecase) ListContestEvents(ctx context.Context, contestId int, userId int) ([]*ContestEvent, int64) {
	contest, err := uc.repo.GetContest(ctx, contestId)
	if err != nil {
		return nil, 0
	}
	if contest.Type == ContestTypeOI && contest.GetRunningStatus() == ContestRunningStatusInProgress && !contest.HasPermission(ctx, ContestPermissionUpdate) {
		return nil, 0
	}
	return uc.repo.ListContestEvents(ctx, contestId, userId)
}

func (uc *ContestUsecase) GetContestEvent(ctx context.Context, id int) (*ContestEvent, error) {
	return uc.repo.GetContestEvent(ctx, id)
}
