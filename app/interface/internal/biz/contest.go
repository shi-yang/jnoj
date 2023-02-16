package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Contest is a Contest model.
type Contest struct {
	ID               int
	Name             string
	StartTime        time.Time
	EndTime          time.Time
	FrozenTime       *time.Time // 封榜时间
	Type             int        // 比赛类型
	Description      string
	Status           int // 隐藏，公开，私有
	UserID           int
	ParticipantCount int  // 参与人数
	IsRegistered     bool // 是否参赛
	GroupId          int
	CreatedAt        time.Time
	UpdatedAt        time.Time
}

const (
	ContestRoleGuest = iota
	ContestRolePlayer
	ContestRoleAdmin
)

type ContestSubmission struct {
	ID            int
	ProblemNumber int
	ProblemName   string
	Verdict       int
	UserID        int
	Score         int
	Time          int
	Memory        int
	Language      int
	User          ContestUser
	CreatedAt     time.Time
}

const (
	ContestTypeICPC = iota + 1 // ICPC 赛制 International Collegiate Programming Contest
	ContestTypeIOI             // IOI 赛制 International Olympiad in Informatics
	ContestTypeOI              // OI 赛制 Olympiad in Informatics
)

const (
	ContestStatusHidden  = iota // 隐藏
	ContestStatusPublic         // 公开
	ContestStatusPrivate        // 私有
)

const (
	ContestRunningStatusNotStarted      = iota // 尚未开始
	ContestRunningStatusInProgress             // 进行中
	ContestRunningStatusFrozenStandings        // 封榜
	ContestRunningStatusFinished               // 已结束
)

// GetRunningStatus 获取比赛的状态，是否开始、进行中、封榜、已结束
func (c *Contest) GetRunningStatus() int {
	now := time.Now()
	if now.Before(c.StartTime) {
		return ContestRunningStatusNotStarted
	} else if c.FrozenTime != nil && now.After(*c.FrozenTime) {
		return ContestRunningStatusFrozenStandings
	} else if now.Before(c.EndTime) {
		return ContestRunningStatusInProgress
	}
	return ContestRunningStatusFinished
}

// 比赛权限
type ContestPermissionType int32

const (
	ContestPermissionView   ContestPermissionType = 0 // 查看权限
	ContestPermissionUpdate ContestPermissionType = 1 // 修改权限
)

// HasPermission 是否有权限
// 修改权限，仅比赛创建人可以看
// 查看权限，规则要求：
// 1、公开情况下，比赛结束
// 2、管理员
// 3、比赛不是不可见状态
// 4、参赛用户
func (c *Contest) HasPermission(ctx context.Context, t ContestPermissionType) bool {
	userID, _ := auth.GetUserID(ctx)
	if t == ContestPermissionUpdate {
		return c.UserID == userID
	}
	// 比赛创建人
	if c.UserID == userID {
		return true
	}
	// 隐藏情况下除了比赛创建人都不准看
	if c.Status == ContestStatusHidden {
		return false
	}
	runningStatus := c.GetRunningStatus()
	// 公开赛比赛结束
	if c.Status == ContestStatusPublic && runningStatus == ContestRunningStatusFinished {
		return true
	}
	role := c.GetRole(ctx)
	// 是选手且比赛开始后
	if role == ContestRolePlayer && runningStatus != ContestRunningStatusNotStarted {
		return true
	}
	return false
}

// GetRole 获取当前用户的角色
func (c *Contest) GetRole(ctx context.Context) int {
	userID, ok := auth.GetUserID(ctx)
	if ok && c.UserID == userID {
		return ContestRoleAdmin
	} else if c.IsRegistered {
		return ContestRolePlayer
	}
	return ContestRoleGuest
}

// ContestRepo is a Contest repo.
type ContestRepo interface {
	ListContests(context.Context, *v1.ListContestsRequest) ([]*Contest, int64)
	GetContest(context.Context, int) (*Contest, error)
	CreateContest(context.Context, *Contest) (*Contest, error)
	UpdateContest(context.Context, *Contest) (*Contest, error)
	DeleteContest(context.Context, int) error
	AddContestParticipantCount(context.Context, int, int) error
	ListContestAllSubmissions(ctx context.Context, contesId int, userId int) []*ContestSubmission
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
	contest, err := uc.repo.GetContest(ctx, id)
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	return contest, nil
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
func (uc *ContestUsecase) ListContestAllSubmissions(ctx context.Context, id int, uid int) []*ContestSubmission {
	contest, err := uc.repo.GetContest(ctx, id)
	if err != nil {
		return nil
	}
	if !contest.HasPermission(ctx, ContestPermissionView) {
		return nil
	}
	res := uc.repo.ListContestAllSubmissions(ctx, id, uid)

	for i := 0; i < len(res); i++ {
		if contest.Type == ContestTypeICPC {
			res[i].Score = int(res[i].CreatedAt.Sub(contest.StartTime).Minutes())
		}
		if contest.Type == ContestTypeOI && contest.GetRunningStatus() != ContestRunningStatusFinished {
			res[i].Verdict = SubmissionVerdictPending
			res[i].Time = 0
			res[i].Memory = 0
			res[i].Score = 0
		}
	}
	return res
}

// ListContestSubmissions .
func (uc *ContestUsecase) ListContestSubmissions(ctx context.Context, req *v1.ListContestSubmissionsRequest, contest *Contest) ([]*ContestSubmission, int64) {
	res := make([]*ContestSubmission, 0)
	submissions, count := uc.submissionRepo.ListSubmissions(ctx, &v1.ListSubmissionsRequest{
		EntityId:   req.Id,
		EntityType: SubmissionEntityTypeContest,
		Page:       req.Page,
		PerPage:    req.PerPage,
	})
	problems, _ := uc.repo.ListContestProblems(ctx, int(req.Id))
	problemMap := make(map[int]int)
	for _, v := range problems {
		problemMap[v.ProblemID] = v.Number
	}
	runningStatus := contest.GetRunningStatus()
	for _, v := range submissions {
		cs := &ContestSubmission{
			ID:            v.ID,
			Verdict:       v.Verdict,
			ProblemNumber: problemMap[v.ProblemID],
			ProblemName:   v.ProblemName,
			CreatedAt:     v.CreatedAt,
			Language:      v.Language,
			Score:         v.Score,
			Time:          v.Time,
			Memory:        v.Memory,
			User: ContestUser{
				ID:       v.User.ID,
				Nickname: v.User.Nickname,
			},
		}
		// OI 提交之后无反馈
		if runningStatus == ContestRunningStatusInProgress && contest.Type == ContestTypeOI {
			cs.Verdict = SubmissionVerdictPending
			cs.Time = 0
			cs.Memory = 0
			cs.Score = 0
		}
		res = append(res, cs)
	}
	return res, count
}
