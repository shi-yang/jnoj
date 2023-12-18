package biz

import (
	"sort"
	"time"
)

type StandingSortOption func(*standingSortOptions)

type standingSortOptions struct {
	VirtualIncluded bool
	OnlyOfficial    bool
}

func WithVirtualIncluded(virtualIncluded bool) StandingSortOption {
	return func(o *standingSortOptions) {
		o.VirtualIncluded = virtualIncluded
	}
}

func WithOnlyOfficial(onlyOfficial bool) StandingSortOption {
	return func(o *standingSortOptions) {
		o.OnlyOfficial = onlyOfficial
	}
}

type ContestStandingSorter interface {
	Sort(contest *Contest, users []*ContestUser, problems []*ContestProblem, submissions []*ContestSubmission) []*StandingUser
}

type StandingUser struct {
	// 排名
	Rank int
	// 用户
	Who string
	// 用户ID
	UserId int
	// 用户头像
	UserAvatar string
	// 解答
	Solved int
	// 是否参与排名。只有正式选手才参与排名
	IsRank bool
	// 虚拟参赛
	VirtualStart *time.Time
	VirtualEnd   *time.Time
	// 分数 OI、IOI模式
	// ICPC, score = 罚时
	// OI, score = 最后一次提交
	// IOI, score = 最大分数
	Score int
	// 最大分数 OI 模式
	MaxScore int
	// 题目
	Problem map[int]*StandingProblem
}

type StandingProblem struct {
	Attempted    int
	IsFirstBlood bool
	Status       int
	Score        int // 题目得分
	SolvedAt     int // 在比赛开始多少分钟解决的
	MaxScore     int
	IsInComp     bool // 是否处于比赛期间的提交
}

// NewContestStandingICPC ICPC 排名
func NewContestStandingICPC(opts ...StandingSortOption) ContestStandingSorter {
	options := &standingSortOptions{}
	for _, opt := range opts {
		opt(options)
	}
	return &ContestStandingICPC{options: options}
}

type ContestStandingICPC struct {
	options *standingSortOptions
}

// Sort ICPC 排名
func (c *ContestStandingICPC) Sort(contest *Contest, users []*ContestUser, problems []*ContestProblem, submissions []*ContestSubmission) []*StandingUser {
	var userMap = make(map[int]*StandingUser)
	// 初始化用户排名数据
	for _, user := range users {
		// 不包含虚拟比赛用户
		if !c.options.VirtualIncluded && user.VirtualStart != nil {
			continue
		}
		u := &StandingUser{
			UserId:       user.UserID,
			Who:          user.Name,
			UserAvatar:   user.UserAvatar,
			IsRank:       user.Role == ContestRoleOfficialPlayer,
			VirtualStart: user.VirtualStart,
			VirtualEnd:   user.VirtualEnd,
		}
		userMap[user.UserID] = u
	}
	// 记录一血
	firstBlood := make(map[int]int)
	for _, submission := range submissions {
		problemNumber := submission.ProblemNumber
		uid := submission.UserID
		if _, ok := userMap[uid]; !ok {
			continue
		}
		if userMap[uid].Problem == nil {
			userMap[uid].Problem = make(map[int]*StandingProblem)
		}
		userProblemStatus, ok := userMap[uid].Problem[problemNumber]
		if !ok {
			userProblemStatus = &StandingProblem{}
		}
		// 已经通过，则直接跳过
		if userProblemStatus.Status == SubmissionVerdictAccepted {
			continue
		}
		// 存在封榜
		if contest.FrozenTime != nil && submission.CreatedAt.After(*contest.FrozenTime) {
			userProblemStatus.Status = SubmissionVerdictPending
			userMap[uid].Problem[problemNumber] = userProblemStatus
			continue
		}
		// 判断是否比赛中的提交：正常比赛
		isInComp := false
		if submission.CreatedAt.Before(contest.EndTime) {
			isInComp = true
		}
		// 判断是否比赛中的提交：虚拟比赛
		if userMap[uid].VirtualStart != nil && submission.CreatedAt.Sub(*userMap[uid].VirtualStart) < contest.EndTime.Sub(contest.StartTime) {
			isInComp = true
			// 提前退出虚拟比赛，提交时间在退出虚拟比赛后则不算是比赛中的提交
			if userMap[uid].VirtualEnd != nil && submission.CreatedAt.After(*userMap[uid].VirtualEnd) {
				isInComp = false
			}
		}
		if isInComp && submission.Verdict == SubmissionVerdictAccepted {
			userProblemStatus.IsInComp = isInComp
		}
		// 只显示正式比赛的提交
		if c.options.OnlyOfficial && !isInComp {
			continue
		}
		userProblemStatus.Status = submission.Verdict
		// 编译错误不算提交
		if submission.Verdict != SubmissionVerdictCompileError {
			userProblemStatus.Attempted++
		}
		// 通过
		if submission.Verdict == SubmissionVerdictAccepted {
			if _, ok := firstBlood[problemNumber]; !ok {
				firstBlood[problemNumber] = uid
				userProblemStatus.IsFirstBlood = true
			}
			userProblemStatus.SolvedAt = int(submission.CreatedAt.Sub(contest.StartTime).Minutes())
			// ICPC 尝试次数会有20分罚时，加上本题通过时间，即为分数
			userProblemStatus.Score = 20*(userProblemStatus.Attempted-1) + userProblemStatus.SolvedAt
			// 虚拟比赛计分换算
			if userMap[uid].VirtualStart != nil && isInComp {
				userProblemStatus.SolvedAt = int(submission.CreatedAt.Sub(*userMap[uid].VirtualStart).Minutes())
				userProblemStatus.Score = 20*(userProblemStatus.Attempted-1) + int(submission.CreatedAt.Sub(*userMap[uid].VirtualStart).Minutes())
			}
			userMap[uid].Score += userProblemStatus.Score
			userMap[uid].Solved += 1
		}
		userMap[uid].Problem[problemNumber] = userProblemStatus
	}
	var res = make([]*StandingUser, 0, len(userMap))
	for _, user := range userMap {
		res = append(res, user)
	}
	sort.Slice(res, func(i, j int) bool {
		if res[i].Solved != res[j].Solved {
			return res[i].Solved > res[j].Solved
		}
		return res[i].Score < res[j].Score
	})

	rank := 1
	for i := 0; i < len(res); i++ {
		if res[i].IsRank {
			res[i].Rank = rank
			rank++
		}
	}
	return res
}

type ContestStandingIOI struct {
	options *standingSortOptions
}

// NewContestStandingIOI IOI 排名
func NewContestStandingIOI(opts ...StandingSortOption) ContestStandingSorter {
	options := &standingSortOptions{}
	for _, opt := range opts {
		opt(options)
	}
	return &ContestStandingIOI{options: options}
}

// Sort IOI 排名
func (c *ContestStandingIOI) Sort(contest *Contest, users []*ContestUser, problems []*ContestProblem, submissions []*ContestSubmission) []*StandingUser {
	var userMap = make(map[int]*StandingUser)
	// 初始化用户排名数据
	for _, user := range users {
		// 不包含虚拟比赛用户
		if !c.options.VirtualIncluded && user.VirtualStart != nil {
			continue
		}
		u := &StandingUser{
			UserId:       user.UserID,
			Who:          user.Name,
			UserAvatar:   user.UserAvatar,
			IsRank:       user.Role == ContestRoleOfficialPlayer,
			VirtualStart: user.VirtualStart,
		}
		userMap[user.UserID] = u
	}
	// 记录一血
	firstBlood := make(map[int]int)
	for _, submission := range submissions {
		problemNumber := submission.ProblemNumber
		uid := submission.UserID
		if _, ok := userMap[uid]; !ok {
			continue
		}
		if userMap[uid].Problem == nil {
			userMap[uid].Problem = make(map[int]*StandingProblem)
		}
		userProblemStatus, ok := userMap[uid].Problem[problemNumber]
		if !ok {
			userProblemStatus = &StandingProblem{}
		}
		// 已经通过，则直接跳过
		if userProblemStatus.Status == SubmissionVerdictAccepted {
			continue
		}
		// 存在封榜
		if contest.FrozenTime != nil && submission.CreatedAt.After(*contest.FrozenTime) {
			userProblemStatus.Status = SubmissionVerdictPending
			userMap[uid].Problem[problemNumber] = userProblemStatus
			continue
		}
		// 判断是否比赛中的提交：正常比赛
		isInComp := false
		if submission.CreatedAt.Before(contest.EndTime) {
			isInComp = true
		}
		// 判断是否比赛中的提交：虚拟比赛
		if userMap[uid].VirtualStart != nil && submission.CreatedAt.Sub(*userMap[uid].VirtualStart) < contest.EndTime.Sub(contest.StartTime) {
			isInComp = true
			// 提前退出虚拟比赛，提交时间在退出虚拟比赛后则不算是比赛中的提交
			if userMap[uid].VirtualEnd != nil && submission.CreatedAt.After(*userMap[uid].VirtualEnd) {
				isInComp = false
			}
		}
		if isInComp && submission.Verdict == SubmissionVerdictAccepted {
			userProblemStatus.IsInComp = isInComp
		}
		// 只显示正式比赛的提交
		if c.options.OnlyOfficial && !isInComp {
			continue
		}
		userProblemStatus.Status = submission.Verdict
		// 编译错误不算提交
		if submission.Verdict != SubmissionVerdictCompileError {
			userProblemStatus.Attempted++
		}
		// 通过
		if submission.Verdict == SubmissionVerdictAccepted {
			if _, ok := firstBlood[problemNumber]; !ok {
				firstBlood[problemNumber] = uid
				userProblemStatus.IsFirstBlood = true
			}
			userProblemStatus.SolvedAt = int(submission.CreatedAt.Sub(contest.StartTime).Minutes())
			userMap[uid].Solved += 1
		}
		// IOI 取最大得分
		if userProblemStatus.Score < submission.Score {
			userProblemStatus.Score = submission.Score
		}
		userMap[uid].Problem[problemNumber] = userProblemStatus
	}
	var res = make([]*StandingUser, 0, len(userMap))
	for _, user := range userMap {
		for _, p := range user.Problem {
			user.Score += p.Score
		}
		res = append(res, user)
	}
	sort.Slice(res, func(i, j int) bool {
		if res[i].Score != res[j].Score {
			return res[i].Score > res[j].Score
		}
		return res[i].Solved > res[j].Solved
	})
	rank := 0
	prevScore := 0
	for i := 0; i < len(res); i++ {
		if res[i].IsRank {
			if prevScore != res[i].Score {
				rank++
			}
			res[i].Rank = rank
			prevScore = res[i].Score
		}
	}
	return res
}

type ContestStandingOI struct {
	options *standingSortOptions
}

// NewContestStandingOI OI 排名
func NewContestStandingOI(opts ...StandingSortOption) ContestStandingSorter {
	options := &standingSortOptions{}
	for _, opt := range opts {
		opt(options)
	}
	return &ContestStandingOI{options: options}
}

// Sort OI 排名
func (c *ContestStandingOI) Sort(contest *Contest, users []*ContestUser, problems []*ContestProblem, submissions []*ContestSubmission) []*StandingUser {
	var userMap = make(map[int]*StandingUser)
	// 初始化用户排名数据
	for _, user := range users {
		// 不包含虚拟比赛用户
		if !c.options.VirtualIncluded && user.VirtualStart != nil {
			continue
		}
		u := &StandingUser{
			UserId:       user.UserID,
			Who:          user.Name,
			UserAvatar:   user.UserAvatar,
			IsRank:       user.Role == ContestRoleOfficialPlayer,
			VirtualStart: user.VirtualStart,
		}
		userMap[user.UserID] = u
	}
	// 记录一血
	firstBlood := make(map[int]int)
	for _, submission := range submissions {
		problemNumber := submission.ProblemNumber
		uid := submission.UserID
		if _, ok := userMap[uid]; !ok {
			continue
		}
		if userMap[uid].Problem == nil {
			userMap[uid].Problem = make(map[int]*StandingProblem)
		}
		userProblemStatus, ok := userMap[uid].Problem[problemNumber]
		if !ok {
			userProblemStatus = &StandingProblem{}
		}
		// 已经通过，则直接跳过
		if userProblemStatus.Status == SubmissionVerdictAccepted {
			continue
		}
		// 存在封榜
		if contest.FrozenTime != nil && submission.CreatedAt.After(*contest.FrozenTime) {
			userProblemStatus.Status = SubmissionVerdictPending
			userMap[uid].Problem[problemNumber] = userProblemStatus
			continue
		}
		// 判断是否比赛中的提交：正常比赛
		isInComp := false
		if submission.CreatedAt.Before(contest.EndTime) {
			isInComp = true
		}
		// 判断是否比赛中的提交：虚拟比赛
		if userMap[uid].VirtualStart != nil && submission.CreatedAt.Sub(*userMap[uid].VirtualStart) < contest.EndTime.Sub(contest.StartTime) {
			isInComp = true
			// 提前退出虚拟比赛，提交时间在退出虚拟比赛后则不算是比赛中的提交
			if userMap[uid].VirtualEnd != nil && submission.CreatedAt.After(*userMap[uid].VirtualEnd) {
				isInComp = false
			}
		}
		if isInComp && submission.Verdict == SubmissionVerdictAccepted {
			userProblemStatus.IsInComp = isInComp
		}
		// 只显示正式比赛的提交
		if c.options.OnlyOfficial && !isInComp {
			continue
		}
		userProblemStatus.Status = submission.Verdict
		if submission.Verdict == SubmissionVerdictAccepted {
			if _, ok := firstBlood[problemNumber]; !ok {
				firstBlood[problemNumber] = uid
				userProblemStatus.IsFirstBlood = true
			}
			userProblemStatus.SolvedAt = int(submission.CreatedAt.Sub(contest.StartTime).Minutes())
			userMap[uid].Solved += 1
		}
		// OI 取最后一次得分
		userProblemStatus.Score = submission.Score
		if userProblemStatus.MaxScore < submission.Score {
			userProblemStatus.MaxScore = submission.Score
		}
		userMap[uid].Problem[problemNumber] = userProblemStatus
	}
	var res = make([]*StandingUser, 0, len(userMap))
	for _, user := range userMap {
		for _, p := range user.Problem {
			user.Score += p.Score
			user.MaxScore += p.MaxScore
		}
		res = append(res, user)
	}
	sort.Slice(res, func(i, j int) bool {
		if res[i].Score != res[j].Score {
			return res[i].Score > res[j].Score
		}
		return res[i].MaxScore > res[j].MaxScore
	})
	rank := 0
	prevScore := 0
	for i := 0; i < len(res); i++ {
		if res[i].IsRank {
			if prevScore != res[i].Score {
				rank++
			}
			res[i].Rank = rank
			prevScore = res[i].Score
		}
	}
	return res
}
