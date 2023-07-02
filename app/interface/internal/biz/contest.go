package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"strings"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"google.golang.org/protobuf/types/known/durationpb"
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
	Privacy          int        // 隐私设置，私有、公开
	Membership       int        // 参赛资格
	InvitationCode   string     // 邀请码
	UserID           int        // 创建人ID
	UserNickname     string     // 创建人名称
	ParticipantCount int        // 参与人数
	Role             int        // 登录用户角色
	VirtualStart     *time.Time // 登录用户虚拟比赛开始时间
	VirtualEnd       *time.Time // 登录用户虚拟比赛结束时间
	Feature          string     // 特性：rated
	GroupId          int
	CreatedAt        time.Time
	UpdatedAt        time.Time
	OwnerName        string // 创建者名称
}

const (
	ContestRoleGuest            = iota // 游客
	ContestRoleOfficialPlayer          // 选手，只有正式选手参与排名
	ContestRoleUnofficialPlayer        // 非正式选手，不参与排名
	ContestRoleVirtualPlayer           // 虚拟参赛选手
	ContestRoleWriter                  // 出题人
	ContestRoleAdmin                   // 管理
)

const (
	ContestFeatureRated = "rated" // 计分
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
	ContestTypeICPC = iota // ICPC 赛制 International Collegiate Programming Contest
	ContestTypeIOI         // IOI 赛制 International Olympiad in Informatics
	ContestTypeOI          // OI 赛制 Olympiad in Informatics
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

const (
	ContestInitialRating = 1500 // 比赛第一次参赛默认积分
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
	// 虚拟竞赛比赛状态
	if c.VirtualStart != nil && c.VirtualEnd == nil {
		if now.Sub(*c.VirtualStart) < c.EndTime.Sub(c.StartTime) {
			return ContestRunningStatusInProgress
		}
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
	userID, role := auth.GetUserID(ctx)
	// update
	// 满足以下条件，可以有比赛的任何权限
	// 1. 比赛创建人
	// 2. 角色是管理员，角色可能继承自小组管理
	// 3. 后台分配的特定角色用户
	if c.UserID == userID || c.Role == ContestRoleAdmin || CheckAccess(role, ResourceContest) {
		return true
	}
	// view
	if t == ContestPermissionView {
		runningStatus := c.GetRunningStatus()
		// 公开赛比赛结束
		if c.Privacy == ContestPrivacyPublic && runningStatus == ContestRunningStatusFinished {
			return true
		}
		// 比赛开始后
		if (c.Role != ContestRoleGuest) && runningStatus != ContestRunningStatusNotStarted {
			return true
		}
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
	ListContestAllSubmissions(ctx context.Context, contest *Contest) []*ContestSubmission
	ContestProblemRepo
	ContestUserRepo
}

// ContestUsecase is a Contest usecase.
type ContestUsecase struct {
	repo           ContestRepo
	problemRepo    ProblemRepo
	submissionRepo SubmissionRepo
	userRepo       UserRepo
	groupRepo      GroupRepo
	log            *log.Helper
}

// NewContestUsecase new a Contest usecase.
func NewContestUsecase(
	repo ContestRepo,
	problemRepo ProblemRepo,
	submissionRepo SubmissionRepo,
	userRepo UserRepo,
	groupRepo GroupRepo,
	logger log.Logger) *ContestUsecase {
	return &ContestUsecase{
		repo:           repo,
		problemRepo:    problemRepo,
		submissionRepo: submissionRepo,
		userRepo:       userRepo,
		groupRepo:      groupRepo,
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
	if c.GroupId != 0 {
		c.Membership = ContestMembershipGroupUser
	}
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

// GetContestStanding 获取比赛榜单
func (uc *ContestUsecase) GetContestStanding(ctx context.Context, contest *Contest, page int, pageSize int, isOfficial bool, isVirtualPlayersIncluded bool) ([]*StandingUser, int) {
	users, _ := uc.repo.ListContestUsers(ctx, &v1.ListContestUsersRequest{ContestId: int32(contest.ID)})
	submissions := uc.ListContestAllSubmissions(ctx, contest)
	problems, _ := uc.repo.ListContestProblems(ctx, contest.ID)
	var sorter ContestStandingSorter
	if contest.Type == ContestTypeICPC {
		sorter = NewContestStandingICPC(WithOnlyOfficial(isOfficial), WithVirtualIncluded(isVirtualPlayersIncluded))
	} else if contest.Type == ContestTypeIOI {
		sorter = NewContestStandingIOI(WithOnlyOfficial(isOfficial), WithVirtualIncluded(isVirtualPlayersIncluded))
	} else if contest.Type == ContestTypeOI {
		sorter = NewContestStandingOI(WithOnlyOfficial(isOfficial), WithVirtualIncluded(isVirtualPlayersIncluded))
	}
	standings := sorter.Sort(contest, users, problems, submissions)
	count := len(standings)
	if pageSize <= 0 {
		return standings, count
	}
	startIndex := (page - 1) * pageSize
	endIndex := startIndex + pageSize
	if endIndex > count {
		endIndex = count
	}
	return standings[startIndex:endIndex], count
}

// ListContestAllSubmissions 获取全部提交记录
func (uc *ContestUsecase) ListContestAllSubmissions(ctx context.Context, contest *Contest) []*ContestSubmission {
	isContestManager := contest.HasPermission(ctx, ContestPermissionUpdate)
	submissions := uc.repo.ListContestAllSubmissions(ctx, contest)
	uid, _ := auth.GetUserID(ctx)

	var virtualTime time.Time
	now := time.Now()
	if contest.VirtualStart != nil {
		virtualTime = contest.StartTime.Add(now.Sub(*contest.VirtualStart))
	}
	runningStatus := contest.GetRunningStatus()
	var res []*ContestSubmission
	for _, s := range submissions {
		// 正在进行虚拟竞赛，不返回后面的提交
		if runningStatus == ContestRunningStatusInProgress && contest.Role == ContestRoleVirtualPlayer {
			if s.CreatedAt.After(virtualTime) && s.UserID != uid {
				continue
			}
		}
		// OI 未结束前不返回结果
		if contest.Type == ContestTypeOI && !isContestManager && runningStatus != ContestRunningStatusFinished {
			s.Verdict = SubmissionVerdictPending
			s.Time = 0
			s.Memory = 0
			s.Score = 0
		}
		res = append(res, s)
	}
	return res
}

// ListContestSubmissions .
func (uc *ContestUsecase) ListContestSubmissions(ctx context.Context, req *v1.ListContestSubmissionsRequest, contest *Contest) ([]*ContestSubmission, int64) {
	res := make([]*ContestSubmission, 0)
	problems, _ := uc.repo.ListContestProblems(ctx, int(req.ContestId))
	problemMap := make(map[int]int)
	var reqProblemID *int32
	for _, v := range problems {
		problemMap[v.ProblemID] = v.Number
		// 将传过来的 Number 换成 Problem.ID 去查询
		if req.Problem != nil && reqProblemID == nil && v.Number == int(*req.Problem) {
			id := int32(v.ProblemID)
			reqProblemID = &id
		}
	}
	submissions, count := uc.submissionRepo.ListSubmissions(ctx, &v1.ListSubmissionsRequest{
		EntityId:   req.ContestId,
		EntityType: SubmissionEntityTypeContest,
		Page:       req.Page,
		PerPage:    req.PerPage,
		Verdict:    req.Verdict,
		ProblemId:  reqProblemID,
		UserId:     req.UserId,
	})
	isContestManager := contest.HasPermission(ctx, ContestPermissionUpdate)
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
				ID:   v.UserID,
				Name: v.Nickname,
			},
		}
		// OI 提交之后无反馈
		if contest.Type == ContestTypeOI && !isContestManager && runningStatus != ContestRunningStatusFinished {
			cs.Verdict = SubmissionVerdictPending
			cs.Time = 0
			cs.Memory = 0
			cs.Score = 0
		}
		res = append(res, cs)
	}
	return res, count
}

// CalculateContestRating .
func (c *ContestUsecase) CalculateContestRating(ctx context.Context, contest *Contest) error {
	users, _ := c.GetContestStanding(ctx, contest, -1, -1, true, false)
	players := make([]*ContestRatedPlayer, 0)
	contestUsers := make([]*ContestUser, 0)
	for _, user := range users {
		if !user.IsRank {
			continue
		}
		oldRating := c.repo.GetContestUserRating(ctx, user.UserId)
		if oldRating < 0 {
			oldRating = ContestInitialRating
		}
		player := &ContestRatedPlayer{
			UserID:    user.UserId,
			Rank:      user.Rank,
			OldRating: oldRating,
		}
		players = append(players, player)
	}
	ContestRated(players)
	now := time.Now()
	for _, player := range players {
		contestUsers = append(contestUsers, &ContestUser{
			UserID:    player.UserID,
			ContestID: contest.ID,
			NewRating: player.NewRating,
			OldRating: player.OldRating,
			RatedAt:   &now,
		})
	}
	return c.repo.SaveContestRating(ctx, contestUsers)
}

// QueryContestSpecialEffects .
func (c *ContestUsecase) QueryContestSpecialEffects(ctx context.Context, contest *Contest) (*v1.QueryContestSpecialEffectsResponse, error) {
	userId, _ := auth.GetUserID(ctx)
	uc := c.repo.GetContestUser(ctx, contest.ID, userId)
	if uc == nil || contest.Type == ContestTypeOI {
		return nil, nil
	}
	res := &v1.QueryContestSpecialEffectsResponse{
		ContestName:     contest.Name,
		UserName:        uc.Name,
		ContestDuration: durationpb.New(contest.EndTime.Sub(contest.StartTime)),
	}
	if res.UserName == "" {
		res.UserName = uc.UserNickname
	}
	if strings.Contains(uc.SpecialEffects, ContestUserSpecialEffects) {
		return nil, nil
	}
	// 判断是否满足AK条件
	submissions, _ := c.submissionRepo.ListSubmissions(ctx, &v1.ListSubmissionsRequest{
		UserId:     int32(userId),
		EntityId:   int32(contest.ID),
		EntityType: SubmissionEntityTypeContest,
		Verdict:    []int32{SubmissionVerdictAccepted},
	})
	var akTime time.Time
	submissionProblem := make(map[int]struct{})
	for _, s := range submissions {
		submissionProblem[s.ProblemID] = struct{}{}
		// 记录AK时间
		if s.CreatedAt.Before(contest.EndTime) && akTime.Before(s.CreatedAt) {
			akTime = s.CreatedAt
		}
	}
	problems, _ := c.repo.ListContestProblems(ctx, contest.ID)
	if len(problems) == len(submissionProblem) {
		uc.SpecialEffects = ContestUserSpecialEffects
		res.AkTime = durationpb.New(akTime.Sub(contest.StartTime))
		c.repo.UpdateContestUser(ctx, uc)
	}
	return res, nil
}
