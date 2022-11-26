package service

import (
	"context"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"

	"google.golang.org/protobuf/types/known/emptypb"
	"google.golang.org/protobuf/types/known/timestamppb"
)

// ContestService is a contest service.
type ContestService struct {
	uc *biz.ContestUsecase
}

// NewContestService new a contest service.
func NewContestService(uc *biz.ContestUsecase) *ContestService {
	return &ContestService{uc: uc}
}

// ListContests 比赛列表
func (s *ContestService) ListContests(ctx context.Context, req *v1.ListContestsRequest) (*v1.ListContestsResponse, error) {
	res, count := s.uc.ListContests(ctx, req)
	resp := new(v1.ListContestsResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.Contest{
			Id:               int32(v.ID),
			Name:             v.Name,
			ParticipantCount: int32(v.ParticipantCount),
			StartTime:        timestamppb.New(v.StartTime),
			EndTime:          timestamppb.New(v.EndTime),
			Type:             int32(v.Type),
		})
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
		Type:             int32(res.Type),
		Description:      res.Description,
		ParticipantCount: int32(res.ParticipantCount),
		StartTime:        timestamppb.New(res.StartTime),
		EndTime:          timestamppb.New(res.EndTime),
		IsRegistered:     res.IsRegistered,
	}
	if res.Role == biz.ContestRoleAdmin {
		resp.Role = v1.Contest_ADMIN
	} else if res.Role == biz.ContestRolePlayer {
		resp.Role = v1.Contest_PLAYER
	} else {
		resp.Role = v1.Contest_GUEST
	}
	return resp, nil
}

// UpdateContest 编辑比赛
func (s *ContestService) UpdateContest(ctx context.Context, req *v1.UpdateContestRequest) (*v1.Contest, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if contest.HasPermission(ctx, biz.ContestPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, err := s.uc.UpdateContest(ctx, &biz.Contest{
		ID:          int(req.Id),
		Name:        req.Name,
		Description: req.Description,
		StartTime:   req.StartTime.AsTime(),
		EndTime:     req.EndTime.AsTime(),
		Type:        int(req.Type),
		Status:      int(req.Status),
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
		StartTime: req.StartTime.AsTime(),
		EndTime:   req.EndTime.AsTime(),
	})
	if err != nil {
		return nil, err
	}
	return &v1.Contest{
		Id: int32(res.ID),
	}, nil
}

// ListContestProblems 获取比赛题目列表
func (s *ContestService) ListContestProblems(ctx context.Context, req *v1.ListContestProblemsRequest) (*v1.ListContestProblemsResponse, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if contest.HasPermission(ctx, biz.ContestPermissionView) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, count := s.uc.ListContestProblems(ctx, req)
	resp := new(v1.ListContestProblemsResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ContestProblem{
			Id:     int32(v.ID),
			Number: int32(v.Number),
			Name:   v.Name,
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
	if contest.HasPermission(ctx, biz.ContestPermissionView) {
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
	if contest.HasPermission(ctx, biz.ContestPermissionUpdate) {
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
	if contest.HasPermission(ctx, biz.ContestPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.uc.DeleteContestProblem(ctx, int(req.Id), int(req.Number))
	if err != nil {
		return nil, err
	}
	return &emptypb.Empty{}, nil
}

// ListContestUsers 获取比赛用户
func (s *ContestService) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) (*v1.ListContestUsersResponse, error) {
	users, count := s.uc.ListContestUsers(ctx, req)
	resp := new(v1.ListContestUsersResponse)
	for _, v := range users {
		resp.Data = append(resp.Data, &v1.ContestUser{
			Id:       int32(v.ID),
			UserId:   int32(v.UserID),
			Nickname: v.Nickname,
		})
	}
	resp.Total = count
	return resp, nil
}

// CreateContestUsers 新增比赛用户
func (s *ContestService) CreateContestUser(ctx context.Context, req *v1.CreateContestUserRequest) (*v1.ContestUser, error) {
	u := &biz.ContestUser{
		ContestID: int(req.Id),
	}
	u.UserID, _ = auth.GetUserID(ctx)
	res, _ := s.uc.CreateContestUser(ctx, u)
	return &v1.ContestUser{
		Id: int32(res.ID),
	}, nil
}

// ListContestStandings 用户比赛提交榜单
func (s *ContestService) ListContestStandings(ctx context.Context, req *v1.ListContestStandingsRequest) (*v1.ListContestStandingsResponse, error) {
	submissions := s.uc.ListContestStandings(ctx, int(req.Id))
	resp := new(v1.ListContestStandingsResponse)
	for _, v := range submissions {
		s := &v1.ListContestStandingsResponse_Submission{
			Id:            int32(v.ID),
			Score:         int32(v.Score),
			UserId:        int32(v.UserID),
			ProblemNumber: int32(v.ProblemNumber),
		}
		switch v.Verdict {
		case biz.SubmissionVerdictPending:
			s.Status = v1.ListContestStandingsResponse_Submission_PENDING
		case biz.SubmissionVerdictAccepted:
			s.Status = v1.ListContestStandingsResponse_Submission_CORRECT
		default:
			s.Status = v1.ListContestStandingsResponse_Submission_INCORRECT
		}
		resp.Data = append(resp.Data, s)
	}
	return resp, nil
}

// ListContestStandings 用户比赛提交列表
func (s *ContestService) ListContestSubmissions(ctx context.Context, req *v1.ListContestSubmissionsRequest) (*v1.ListContestSubmissionsResponse, error) {
	contest, err := s.uc.GetContest(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorContestNotFound(err.Error())
	}
	if contest.HasPermission(ctx, biz.ContestPermissionView) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	submissions, count := s.uc.ListContestSubmissions(ctx, req)
	resp := new(v1.ListContestSubmissionsResponse)
	resp.Total = count
	for _, v := range submissions {
		resp.Data = append(resp.Data, &v1.ListContestSubmissionsResponse_Submission{
			Id:            int32(v.ID),
			Verdict:       int32(v.Verdict),
			Score:         int32(v.Score),
			UserId:        int32(v.UserID),
			ProblemNumber: int32(v.ProblemNumber),
			ProblemName:   v.ProblemName,
			CreatedAt:     timestamppb.New(v.CreatedAt),
			Language:      int32(v.Language),
			User: &v1.ListContestSubmissionsResponse_User{
				Id:       int32(v.User.ID),
				Nickname: v.User.Nickname,
			},
		})
	}
	return resp, nil
}
