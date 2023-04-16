package data

import (
	"context"
	"encoding/json"
	"fmt"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"sort"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

type rankingRepo struct {
	data *Data
	log  *log.Helper
}

// NewRankingRepo .
func NewRankingRepo(data *Data, logger log.Logger) biz.RankingRepo {
	return &rankingRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListProblemRankings .
func (r *rankingRepo) ListProblemRankings(ctx context.Context, req *v1.ListProblemRankingsRequest) ([]*biz.ProblemRanking, int64) {
	res := make([]*biz.ProblemRanking, 0)
	cacheKey := "list_problem_ranking"
	cacheRes, err := r.data.redisdb.Get(ctx, cacheKey).Result()
	if err == nil {
		json.Unmarshal([]byte(cacheRes), &res)
		return res, int64(len(res))
	}

	var submissions []struct {
		Verdict   int
		ProblemID int
		UserID    int
		Nickname  string
	}
	r.data.db.WithContext(ctx).
		Model(&Submission{}).
		Select("submission.verdict, submission.problem_id, submission.user_id, user.nickname").
		Joins("LEFT JOIN user ON user.id=submission.user_id").
		Find(&submissions)
	userAndProblemMap := make(map[string]int)
	userCount := make(map[int]*biz.ProblemRanking)
	for _, s := range submissions {
		key := fmt.Sprintf("%d_%d", s.ProblemID, s.UserID)
		_, ok := userAndProblemMap[key]
		if s.Verdict == biz.SubmissionVerdictAccepted && !ok {
			userAndProblemMap[key] = 1
			if _, ok := userCount[s.UserID]; !ok {
				userCount[s.UserID] = &biz.ProblemRanking{
					Nickname: s.Nickname,
				}
			}
			userCount[s.UserID].Solved += 1
		}
	}
	for k, v := range userCount {
		res = append(res, &biz.ProblemRanking{
			UserId:   k,
			Solved:   v.Solved,
			Nickname: v.Nickname,
		})
	}
	sort.Slice(res, func(i, j int) bool {
		return res[i].Solved > res[j].Solved
	})
	for i := 0; i < len(res); i++ {
		res[i].Rank = i + 1
	}
	t, _ := json.Marshal(res)
	r.data.redisdb.Set(ctx, cacheKey, t, time.Minute*5)
	return res, int64(len(res))
}
