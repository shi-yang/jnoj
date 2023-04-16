package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"

	"github.com/go-kratos/kratos/v2/log"
)

// RankingRepo is a Ranking repo.
type RankingRepo interface {
	ListProblemRankings(context.Context, *v1.ListProblemRankingsRequest) ([]*ProblemRanking, int64)
}

// RankingUsecase is a Ranking usecase.
type RankingUsecase struct {
	repo RankingRepo
	log  *log.Helper
}

type ProblemRanking struct {
	Rank     int
	UserId   int
	Nickname string
	Solved   int
}

// NewRankingUsecase new a Ranking usecase.
func NewRankingUsecase(repo RankingRepo,
	logger log.Logger,
) *RankingUsecase {
	return &RankingUsecase{
		repo: repo,
		log:  log.NewHelper(logger),
	}
}

// ListProblemRankings 获取题目解答排行榜
func (uc *RankingUsecase) ListProblemRankings(ctx context.Context, req *v1.ListProblemRankingsRequest) ([]*ProblemRanking, int64) {
	return uc.repo.ListProblemRankings(ctx, req)
}
