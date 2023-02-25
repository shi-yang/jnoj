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
	Privacy          int    // 隐私设置，私有、公开
	Membership       int    // 参赛资格
	InvitationCode   string // 邀请码
	UserID           int
	ParticipantCount int // 参与人数
	Role             int // 登录用户角色
	GroupId          int
	CreatedAt        time.Time
	UpdatedAt        time.Time
	OwnerName        string
}

const (
	ContestRoleGuest            = iota // 游客
	ContestRoleOfficialPlayer          // 选手，只有正式选手参与排名
	ContestRoleUnofficialPlayer        // 非正式选手，不参与排名
	ContestRoleWriter                  // 出题人
	ContestRoleAdmin                   // 管理
)

const (
	ContestMembershipAllowAnyone    = iota // 允许任何人
	ContestMembershipInvitationCode        // 凭邀请码
	ContestMembershipGroupUser             // 仅小组成员，比赛属于小组时才能设置
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
	ContestPrivacyPrivate = iota // 私有
	ContestPrivacyPublic         // 公开
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
// 修改权限，仅比赛创建人
// 查看权限，规则要求：
// 1、公开情况下，比赛结束
// 2、管理员
// 3、比赛不是私有
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
	runningStatus := c.GetRunningStatus()
	// 公开赛比赛结束
	if c.Privacy == ContestPrivacyPublic && runningStatus == ContestRunningStatusFinished {
		return true
	}
	// 比赛开始后
	if c.Role == ContestRoleOfficialPlayer && runningStatus != ContestRunningStatusNotStarted {
		return true
	}
	return false
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
	// 邀请码仅管理员可见
	if contest.Role != ContestRoleAdmin {
		contest.InvitationCode = ""
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
				ID:   v.User.ID,
				Name: v.User.Nickname,
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
