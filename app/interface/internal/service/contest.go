package service

import (
	"context"
	"strings"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"

	"google.golang.org/protobuf/types/known/emptypb"
	"google.golang.org/protobuf/types/known/timestamppb"
)

// ContestService is a contest service.
type ContestService struct {
	uc        *biz.ContestUsecase
	problemUc *biz.ProblemUsecase
}

// NewContestService new a contest service.
func NewContestService(uc *biz.ContestUsecase, problemUc *biz.ProblemUsecase) *ContestService {
	return &ContestService{uc: uc, problemUc: problemUc}
}

// ListContests 比赛列表
func (s *ContestService) ListContests(ctx context.Context, req *v1.ListContestsRequest) (*v1.ListContestsResponse, error) {
	res, count := s.uc.ListContests(ctx, req)
	resp := new(v1.ListContestsResponse)
	resp.Total = count
	for _, v := range res {
		runningStatus := v.GetRunningStatus()
		c := &v1.Contest{
			Id:               int32(v.ID),
			Name:             v.Name,
			ParticipantCount: int32(v.ParticipantCount),
			StartTime:        timestamppb.New(v.StartTime),
			EndTime:          timestamppb.New(v.EndTime),
			Type:             v1.ContestType(v.Type),
			RunningStatus:    v1.Contest_RunningStatus(runningStatus),
			Privacy:          v1.ContestPrivacy(v.Privacy),
			Membership:       v1.ContestMembership(v.Membership),
			UserId:           int32(v.UserID),
			Feature:          v.Feature,
		}
		c.Owner = &v1.Contest_Owner{
			Name:         v.OwnerName,
			UserNickname: v.UserNickname,
		}
		if v.GroupId != 0 {
			c.Owner.Id = int32(v.GroupId)
			c.Owner.Type = v1.Contest_Owner_GROUP
		} else {
			c.Owner.Id = int32(v.UserID)
			c.Owner.Type = v1.Contest_Owner_USER
		}
		resp.Data = append(resp.Data, c)
	}
	return resp, nil
}

// GetContest 比赛详情
func (s *ContestService) GetContest(ctx context.Context, req *v1.GetContestRequest) (*v1.Contest, error) {
	res, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	resp := &v1.Contest{
		Id:               int32(res.ID),
		Name:             res.Name,
		Type:             v1.ContestType(res.Type),
		Description:      res.Description,
		Privacy:          v1.ContestPrivacy(res.Privacy),
		Membership:       v1.ContestMembership(res.Membership),
		InvitationCode:   res.InvitationCode,
		ParticipantCount: int32(res.ParticipantCount),
		StartTime:        timestamppb.New(res.StartTime),
		EndTime:          timestamppb.New(res.EndTime),
		RunningStatus:    v1.Contest_RunningStatus(res.GetRunningStatus()),
		Role:             v1.ContestUserRole(res.Role),
		Feature:          res.Feature,
	}
	if res.VirtualStart != nil {
		resp.VirtualStart = timestamppb.New(*res.VirtualStart)
	}
	if res.VirtualEnd != nil {
		resp.VirtualEnd = timestamppb.New(*res.VirtualEnd)
	}
	resp.Owner = &v1.Contest_Owner{
		Name: res.OwnerName,
	}
	if res.GroupId != 0 {
		resp.Owner.Id = int32(res.GroupId)
		resp.Owner.Type = v1.Contest_Owner_GROUP
	} else {
		resp.Owner.Id = int32(res.UserID)
		resp.Owner.Type = v1.Contest_Owner_USER
	}
	return resp, nil
}

// UpdateContest 编辑比赛
func (s *ContestService) UpdateContest(ctx context.Context, req *v1.UpdateContestRequest) (*v1.Contest, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	_, role := auth.GetUserID(ctx)
	// 仅管理员有权限编辑 rated
	if req.Feature != "" && !biz.CheckAccess(role, biz.ResourceContest) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, err := s.uc.UpdateContest(ctx, &biz.Contest{
		ID:             int(req.Id),
		Name:           req.Name,
		Description:    req.Description,
		StartTime:      req.StartTime.AsTime(),
		EndTime:        req.EndTime.AsTime(),
		Type:           int(req.Type),
		Privacy:        int(req.Privacy),
		Membership:     int(req.Membership),
		InvitationCode: req.InvitationCode,
		Feature:        req.Feature,
	})
	if err != nil {
		return nil, err
	}
	resp := &v1.Contest{
		Id: int32(res.ID),
	}
	return resp, nil
}

// CreateContest 创建比赛
func (s *ContestService) CreateContest(ctx context.Context, req *v1.CreateContestRequest) (*v1.Contest, error) {
	userID, _ := auth.GetUserID(ctx)
	res, err := s.uc.CreateContest(ctx, &biz.Contest{
		Name:      req.Name,
		UserID:    userID,
		GroupId:   int(req.GroupId),
		StartTime: req.StartTime.AsTime(),
		EndTime:   req.EndTime.AsTime(),
		Privacy:   biz.ContestPrivacyPrivate,
	})
	if err != nil {
		return nil, err
	}
	return &v1.Contest{
		Id: int32(res.ID),
	}, nil
}

// GetContestStanding 获取比赛榜单
func (s *ContestService) GetContestStanding(ctx context.Context, req *v1.GetContestStandingRequest) (*v1.GetContestStandingResponse, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	res, count := s.uc.GetContestStanding(ctx, contest, int(req.Page), int(req.PerPage), req.IsOfficial, req.IsVirtualIncluded)
	resp := new(v1.GetContestStandingResponse)
	for _, v := range res {
		u := &v1.ContestStandingUser{
			Rank:      int32(v.Rank),
			Who:       v.Who,
			UserId:    int32(v.UserId),
			Solved:    int32(v.Solved),
			IsRank:    v.IsRank,
			IsVirtual: v.VirtualStart != nil,
			Score:     int32(v.Score),
			MaxScore:  int32(v.MaxScore),
		}
		u.Problem = make(map[int32]*v1.ContestStandingUser_Problem)
		for k, p := range v.Problem {
			key := int32(k)
			u.Problem[key] = &v1.ContestStandingUser_Problem{}
			u.Problem[key].Attempted = int32(p.Attempted)
			u.Problem[key].IsFirstBlood = p.IsFirstBlood
			u.Problem[key].IsInComp = p.IsInComp
			u.Problem[key].MaxScore = int32(p.MaxScore)
			u.Problem[key].Score = int32(p.Score)
			u.Problem[key].SolvedAt = int32(p.SolvedAt)
			switch p.Status {
			case biz.SubmissionVerdictPending:
				u.Problem[key].Status = v1.ContestStandingUser_PENDING
			case biz.SubmissionVerdictAccepted:
				u.Problem[key].Status = v1.ContestStandingUser_CORRECT
			default:
				u.Problem[key].Status = v1.ContestStandingUser_INCORRECT
			}
		}
		resp.Data = append(resp.Data, u)
	}
	resp.Total = int32(count)
	return resp, nil
}

// ListContestProblems 获取比赛题目列表
func (s *ContestService) ListContestProblems(ctx context.Context, req *v1.ListContestProblemsRequest) (*v1.ListContestProblemsResponse, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionView) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, count := s.uc.ListContestProblems(ctx, contest)
	resp := new(v1.ListContestProblemsResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ContestProblem{
			Id:            int32(v.ID),
			Number:        int32(v.Number),
			Name:          v.Name,
			SubmitCount:   int32(v.SubmitCount),
			AcceptedCount: int32(v.AcceptedCount),
			ProblemId:     int32(v.ProblemID),
			Status:        v1.ContestProblem_Status(v.Status),
		})
	}
	return resp, nil
}

// GetContestProblem 获取比赛题目
func (s *ContestService) GetContestProblem(ctx context.Context, req *v1.GetContestProblemRequest) (*v1.ContestProblem, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionView) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, err := s.uc.GetContestProblem(ctx, int(req.Id), int(req.Number))
	if err != nil {
		return nil, err
	}
	resp := &v1.ContestProblem{
		Id:            int32(res.ID),
		Name:          res.Name,
		Number:        int32(res.Number),
		SubmitCount:   int32(res.SubmitCount),
		AcceptedCount: int32(res.AcceptedCount),
		TimeLimit:     res.TimeLimit,
		MemoryLimit:   res.Memory,
		Type:          v1.ContestProblem_ProblemType(res.Type),
	}
	for _, v := range res.Statements {
		resp.Statements = append(resp.Statements, &v1.ContestProblem_Statement{
			Name:     v.Name,
			Input:    v.Input,
			Output:   v.Output,
			Legend:   v.Legend,
			Language: v.Language,
			Note:     v.Note,
		})
	}
	for _, v := range res.SampleTest {
		resp.SampleTests = append(resp.SampleTests, &v1.ContestProblem_SampleTest{
			Input:  v.Input,
			Output: v.Output,
		})
	}
	return resp, nil
}

// CreateContestProblem 创建比赛题目
func (s *ContestService) CreateContestProblem(ctx context.Context, req *v1.CreateContestProblemRequest) (*v1.ContestProblem, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	problem, err := s.uc.CreateContestProblem(ctx, &biz.ContestProblem{
		ProblemID: int(req.ProblemId),
		ContestID: int(req.Id),
	})
	if err != nil {
		return nil, err
	}
	return &v1.ContestProblem{
		Id: int32(problem.ID),
	}, nil
}

// DeleteContestProblem 删除比赛题目
func (s *ContestService) DeleteContestProblem(ctx context.Context, req *v1.DeleteContestProblemRequest) (*emptypb.Empty, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.uc.DeleteContestProblem(ctx, int(req.Id), int(req.Number))
	if err != nil {
		return nil, err
	}
	return &emptypb.Empty{}, nil
}

// ListContestProblemLanguages 语言列表
func (s *ContestService) ListContestProblemLanguages(ctx context.Context, req *v1.ListContestProblemLanguagesRequest) (*v1.ListContestProblemLanguagesResponse, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionView) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	cp, err := s.uc.GetContestProblem(ctx, int(req.Id), int(req.Number))
	if err != nil {
		return nil, err
	}
	res, count := s.problemUc.ListProblemLanguages(ctx, &biz.Problem{
		ID:   cp.ProblemID,
		Type: cp.Type,
	})
	resp := new(v1.ListContestProblemLanguagesResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ContestProblemLanguage{
			Id:           v.Id,
			LanguageCode: v.LanguageCode,
			LanguageName: v.LanguageName,
		})
	}
	return resp, nil
}

// GetContestProblemLanguage 语言
func (s *ContestService) GetContestProblemLanguage(ctx context.Context, req *v1.GetContestProblemLanguageRequest) (*v1.ContestProblemLanguage, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionView) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	cp, err := s.uc.GetContestProblem(ctx, int(req.Id), int(req.Number))
	if err != nil {
		return nil, err
	}
	res, err := s.problemUc.GetProblemLanguage(ctx, int(cp.ProblemID), int(req.Id))
	if err != nil {
		return nil, err
	}
	resp := &v1.ContestProblemLanguage{
		Id:           res.Id,
		LanguageCode: res.LanguageCode,
		LanguageName: res.LanguageName,
		UserContent:  res.UserContent,
	}
	return resp, nil
}

// ListContestUsers 获取比赛用户
func (s *ContestService) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) (*v1.ListContestUsersResponse, error) {
	users, count := s.uc.ListContestUsers(ctx, req)
	resp := new(v1.ListContestUsersResponse)
	for _, v := range users {
		u := &v1.ContestUser{
			Id:           int32(v.ID),
			UserId:       int32(v.UserID),
			UserNickname: v.UserNickname,
			Name:         v.Name,
			Role:         v1.ContestUserRole(v.Role),
			OldRating:    int32(v.OldRating),
			NewRating:    int32(v.NewRating),
		}
		if v.VirtualStart != nil {
			u.VirtualStart = timestamppb.New(*v.VirtualStart)
		}
		resp.Data = append(resp.Data, u)
	}
	resp.Total = count
	return resp, nil
}

// CreateContestUsers 新增比赛用户
func (s *ContestService) CreateContestUser(ctx context.Context, req *v1.CreateContestUserRequest) (*v1.ContestUser, error) {
	u := &biz.ContestUser{
		Name:      req.Name,
		ContestID: int(req.ContestId),
	}
	u.UserID, _ = auth.GetUserID(ctx)
	res, err := s.uc.CreateContestUser(ctx, u, req.InvitationCode)
	if err != nil {
		return nil, err
	}
	return &v1.ContestUser{
		Id: int32(res.ID),
	}, nil
}

// GetContestUser 获取比赛用户
func (s *ContestService) GetContestUser(ctx context.Context, req *v1.GetContestUserRequest) (*v1.ContestUser, error) {
	_, err := s.uc.GetContest(ctx, int(req.ContestId))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	contestUser := s.uc.GetContestUser(ctx, int(req.ContestId), int(req.UserId))
	if contestUser == nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	cu := &v1.ContestUser{
		Id:             int32(contestUser.ID),
		Name:           contestUser.Name,
		UserId:         int32(contestUser.UserID),
		UserNickname:   contestUser.UserNickname,
		SpecialEffects: contestUser.SpecialEffects,
		Role:           v1.ContestUserRole(contestUser.Role),
		OldRating:      int32(contestUser.OldRating),
		NewRating:      int32(contestUser.NewRating),
	}
	return cu, nil
}

// BatchCreateContestUsers 批量添加用户
func (s *ContestService) BatchCreateContestUsers(ctx context.Context, req *v1.BatchCreateContestUsersRequest) (*v1.BatchCreateContestUsersResponse, error) {
	return s.uc.BatchCreateContestUsers(ctx, req)
}

// ExitVirtualContest 退出虚拟比赛
func (s *ContestService) ExitVirtualContest(ctx context.Context, req *v1.ExitVirtualContestRequest) (*emptypb.Empty, error) {
	contest, err := s.uc.GetContest(ctx, int(req.ContestId))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if contest.VirtualStart == nil || contest.Role == biz.ContestRoleGuest {
		return nil, v1.ErrorBadRequest("did not participate in virtual contest")
	}
	if contest.VirtualEnd != nil || contest.GetRunningStatus() == biz.ContestRunningStatusFinished {
		return nil, v1.ErrorBadRequest("the virtual contest has ended")
	}
	now := time.Now()
	uid, _ := auth.GetUserID(ctx)
	contestUser := s.uc.GetContestUser(ctx, int(req.ContestId), uid)
	if contestUser == nil {
		return nil, v1.ErrorBadRequest("did not participate in virtual contest")
	}
	contestUser.VirtualEnd = &now
	s.uc.UpdateContestUser(ctx, contestUser)
	return &emptypb.Empty{}, nil
}

// UpdateContestUser 修改比赛用户信息
func (s *ContestService) UpdateContestUser(ctx context.Context, req *v1.UpdateContestUserRequest) (*v1.ContestUser, error) {
	contest, err := s.uc.GetContest(ctx, int(req.ContestId))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	uid, _ := auth.GetUserID(ctx)
	if uid != int(req.UserId) && !contest.HasPermission(ctx, biz.ContestPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	if (req.Role == v1.ContestUserRole_ROLE_ADMIN || req.Role == v1.ContestUserRole_ROLE_WRITER) && !contest.HasPermission(ctx, biz.ContestPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	update := &biz.ContestUser{
		ContestID: int(req.ContestId),
		UserID:    int(req.UserId),
		Name:      req.Name,
		Role:      int(req.Role),
	}
	res, err := s.uc.UpdateContestUser(ctx, update)
	if err != nil {
		return nil, err
	}
	return &v1.ContestUser{
		Id: int32(res.ID),
	}, nil
}

// ListContestAllSubmissions 用户比赛全部提交列表
func (s *ContestService) ListContestAllSubmissions(ctx context.Context, req *v1.ListContestAllSubmissionsRequest) (*v1.ListContestAllSubmissionsResponse, error) {
	contest, err := s.uc.GetContest(ctx, int(req.ContestId))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionView) {
		return nil, v1.ErrorForbidden("permission denied")
	}
	submissions := s.uc.ListContestAllSubmissions(ctx, contest)
	resp := new(v1.ListContestAllSubmissionsResponse)
	for _, v := range submissions {
		s := &v1.ListContestAllSubmissionsResponse_Submission{
			Id:      int32(v.ID),
			Score:   int32(v.Score),
			UserId:  int32(v.UserID),
			Problem: int32(v.ProblemNumber),
		}
		switch v.Verdict {
		case biz.SubmissionVerdictPending:
			s.Status = v1.ListContestAllSubmissionsResponse_Submission_PENDING
		case biz.SubmissionVerdictAccepted:
			s.Status = v1.ListContestAllSubmissionsResponse_Submission_CORRECT
		default:
			s.Status = v1.ListContestAllSubmissionsResponse_Submission_INCORRECT
		}
		resp.Data = append(resp.Data, s)
	}
	return resp, nil
}

// ListContestSubmissions 用户比赛提交列表
func (s *ContestService) ListContestSubmissions(ctx context.Context, req *v1.ListContestSubmissionsRequest) (*v1.ListContestSubmissionsResponse, error) {
	contest, err := s.uc.GetContest(ctx, int(req.ContestId))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if !contest.HasPermission(ctx, biz.ContestPermissionView) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	submissions, count := s.uc.ListContestSubmissions(ctx, req, contest)
	resp := new(v1.ListContestSubmissionsResponse)
	resp.Total = count
	for _, v := range submissions {
		resp.Data = append(resp.Data, &v1.ListContestSubmissionsResponse_Submission{
			Id:            int32(v.ID),
			Verdict:       int32(v.Verdict),
			Time:          int32(v.Time),
			Memory:        int32(v.Memory),
			Score:         int32(v.Score),
			UserId:        int32(v.UserID),
			ProblemNumber: int32(v.ProblemNumber),
			ProblemName:   v.ProblemName,
			CreatedAt:     timestamppb.New(v.CreatedAt),
			Language:      int32(v.Language),
			User: &v1.ListContestSubmissionsResponse_User{
				Id:       int32(v.User.ID),
				Nickname: v.User.Name,
			},
		})
	}
	return resp, nil
}

// CalculateContestRating 计算比赛积分
func (s *ContestService) CalculateContestRating(ctx context.Context, req *v1.CalculateContestRatingRequest) (*emptypb.Empty, error) {
	_, role := auth.GetUserID(ctx)
	contest, err := s.uc.GetContest(ctx, int(req.ContestId))
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
	}
	// 仅管理员有权限计算比赛积分
	if !biz.CheckAccess(role, biz.ResourceContest) || !strings.Contains(contest.Feature, biz.ContestFeatureRated) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.uc.CalculateContestRating(ctx, contest)
	return &emptypb.Empty{}, err
}

// QueryContestSpecialEffects 查询比赛特效
func (s *ContestService) QueryContestSpecialEffects(ctx context.Context, req *v1.QueryContestSpecialEffectsRequest) (*v1.QueryContestSpecialEffectsResponse, error) {
	contest, err := s.uc.GetContest(ctx, int(req.ContestId))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	return s.uc.QueryContestSpecialEffects(ctx, contest)
}
