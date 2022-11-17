package service

import (
	"context"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"

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
			Id:        int32(v.ID),
			Name:      v.Name,
			StartTime: timestamppb.New(v.StartTime),
			EndTime:   timestamppb.New(v.EndTime),
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
		Id:        int32(res.ID),
		Name:      res.Name,
		StartTime: timestamppb.New(res.StartTime),
		EndTime:   timestamppb.New(res.EndTime),
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

// ListContestUsers 获取比赛用户
func (s *ContestService) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) (*v1.ListContestUsersResponse, error) {
	return nil, nil
}

func (s *ContestService) ListContestStandings(ctx context.Context, req *v1.ListContestStandingsRequest) (*v1.ListContestStandingsResponse, error) {
	return nil, nil
}
