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
	ProblemName   string
	Verdict       int
	UserID        int
	Score         int
	Language      int
	User          ContestUser
	CreatedAt     time.Time
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
	ListContestStandings(context.Context, int) []*ContestSubmission
	ContestProblemRepo
	ContestUserRepo
}

// ContestUsecase is a Contest usecase.
type ContestUsecase struct {
	repo           ContestRepo
	problemRepo    ProblemRepo
	submissionRepo SubmissionRepo
	log            *log.Helper
}

// NewContestUsecase new a Contest usecase.
func NewContestUsecase(repo ContestRepo, problemRepo ProblemRepo, submissionRepo SubmissionRepo, logger log.Logger) *ContestUsecase {
	return &ContestUsecase{
		repo:           repo,
		problemRepo:    problemRepo,
		submissionRepo: submissionRepo,
		log:            log.NewHelper(logger),
	}
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
func (uc *ContestUsecase) ListContestStandings(ctx context.Context, id int) []*ContestSubmission {
	return uc.repo.ListContestStandings(ctx, id)
}

// ListContestSubmissions .
func (uc *ContestUsecase) ListContestSubmissions(ctx context.Context, req *v1.ListContestSubmissionsRequest) ([]*ContestSubmission, int64) {
	res := make([]*ContestSubmission, 0)
	submissions, count := uc.submissionRepo.ListSubmissions(ctx, &v1.ListSubmissionsRequest{
		ContestId: req.Id,
		Page:      req.Page,
		PerPage:   req.PerPage,
	})
	for _, v := range submissions {
		res = append(res, &ContestSubmission{
			ID:            v.ID,
			Verdict:       v.Verdict,
			ProblemNumber: v.ProblemNumber,
			ProblemName:   v.ProblemName,
			CreatedAt:     v.CreatedAt,
			Language:      v.Language,
			Score:         v.Score,
			User: ContestUser{
				ID:       v.User.ID,
				Nickname: v.User.Nickname,
			},
		})
	}
	return res, count
}
