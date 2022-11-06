package service

import (
	"context"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
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
	return nil, nil
}

// GetContest 比赛详情
func (s *ContestService) GetContest(ctx context.Context, req *v1.GetContestRequest) (*v1.Contest, error) {
	return nil, nil
}

// ListContestProblems 获取比赛题目列表
func (s *ContestService) ListContestProblems(ctx context.Context, req *v1.ListContestProblemsRequest) (*v1.ListContestProblemsResponse, error) {
	return nil, nil
}

// GetContestProblem 获取比赛题目
func (s *ContestService) GetContestProblem(ctx context.Context, req *v1.GetContestProblemRequest) (*v1.ContestProblem, error) {
	s.uc.GetContestProblem(ctx, int(req.Id))
	return nil, nil
}

// ListContestUsers 获取比赛用户
func (s *ContestService) ListContestUsers(ctx context.Context, req *v1.ListContestUsersRequest) (*v1.ListContestUsersResponse, error) {
	return nil, nil
}
