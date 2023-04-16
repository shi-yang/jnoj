package service

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
)

// RankingService is a ranking service.
type RankingService struct {
	uc *biz.RankingUsecase
}

// NewRankingService new a ranking service.
func NewRankingService(uc *biz.RankingUsecase) *RankingService {
	return &RankingService{uc: uc}
}

// ListProblemRankings 获取题目排行榜
func (s *RankingService) ListProblemRankings(ctx context.Context, req *v1.ListProblemRankingsRequest) (*v1.ListProblemRankingsResponse, error) {
	res, count := s.uc.ListProblemRankings(ctx, req)
	resp := new(v1.ListProblemRankingsResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ProblemRanking{
			Rank:     int32(v.Rank),
			Solved:   int32(v.Solved),
			Nickname: v.Nickname,
			UserId:   int32(v.UserId),
		})
	}
	return resp, nil
}
