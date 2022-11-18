package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Contest is a Contest model.
type Contest struct {
	ID               int
	Name             string
	StartTime        time.Time
	EndTime          time.Time
	FrozenTime       *time.Time
	Type             int
	Description      string
	Status           int
	UserID           int
	ParticipantCount int
	CreatedAt        time.Time
	UpdatedAt        time.Time
}

type ContestSubmission struct {
	ID            int
	ProblemNumber int
	Status        int
	UserID        int
	Score         int
}

const (
	ContestTypeICPC = iota + 1 // ICPC 赛制 International Collegiate Programming Contest
	ContestTypeIOI             // IOI 赛制 International Olympiad in Informatics
	ContestTypeOI              // OI 赛制 Olympiad in Informatics
)

// ContestRepo is a Contest repo.
type ContestRepo interface {
	ListContests(context.Context, *v1.ListContestsRequest) ([]*Contest, int64)
	GetContest(context.Context, int) (*Contest, error)
	CreateContest(context.Context, *Contest) (*Contest, error)
	UpdateContest(context.Context, *Contest) (*Contest, error)
	DeleteContest(context.Context, int) error
	AddContestParticipantCount(context.Context, int, int) error
	ListContestSubmissions(context.Context, int) []*ContestSubmission
	ContestProblemRepo
	ContestUserRepo
}

// ContestUsecase is a Contest usecase.
type ContestUsecase struct {
	repo ContestRepo
	log  *log.Helper
}

// NewContestUsecase new a Contest usecase.
func NewContestUsecase(repo ContestRepo, logger log.Logger) *ContestUsecase {
	return &ContestUsecase{repo: repo, log: log.NewHelper(logger)}
}

// ListContests list Contest
func (uc *ContestUsecase) ListContests(ctx context.Context, req *v1.ListContestsRequest) ([]*Contest, int64) {
	return uc.repo.ListContests(ctx, req)
}

// GetContest get a Contest
func (uc *ContestUsecase) GetContest(ctx context.Context, id int) (*Contest, error) {
	return uc.repo.GetContest(ctx, id)
}

// CreateContest creates a Contest, and returns the new Contest.
func (uc *ContestUsecase) CreateContest(ctx context.Context, c *Contest) (*Contest, error) {
	c.Type = ContestTypeICPC
	return uc.repo.CreateContest(ctx, c)
}

// UpdateContest update a Contest
func (uc *ContestUsecase) UpdateContest(ctx context.Context, c *Contest) (*Contest, error) {
	return uc.repo.UpdateContest(ctx, c)
}

// DeleteContest delete a Contest
func (uc *ContestUsecase) DeleteContest(ctx context.Context, id int) error {
	return uc.repo.DeleteContest(ctx, id)
}

// ListContestSubmissions .
func (uc *ContestUsecase) ListContestSubmissions(ctx context.Context, id int) []*ContestSubmission {
	return uc.repo.ListContestSubmissions(ctx, id)
}
