package biz

import (
	"math"
	"sort"
)

// ContestRatedResult
// https://en.wikipedia.org/wiki/Elo_rating_system
// http://codeforces.com/blog/entry/20762
type ContestRatedPlayer struct {
	UserID    int
	OldRating int
	NewRating int
	Rank      int
	seed      float64
	delta     int
}

func expectedScore(playerA, playerB *ContestRatedPlayer) float64 {
	return 1.0 / (1.0 + math.Pow(10.0, float64(playerB.OldRating-playerA.OldRating)/400))
}

func calculateSeed(users []*ContestRatedPlayer, idx int, rating int) float64 {
	var player ContestRatedPlayer
	player.OldRating = rating
	res := 1.0
	for i := 0; i < len(users); i++ {
		if i != idx {
			res += expectedScore(users[i], &player)
		}
	}
	return res
}

func calculateRatingToRank(users []*ContestRatedPlayer, idx int, rank float64) int {
	l, r := 1, 8000
	for r-l > 1 {
		m := (l + r) / 2
		if calculateSeed(users, idx, m) < rank {
			r = m
		} else {
			l = m
		}
	}
	return l
}

func ContestRated(users []*ContestRatedPlayer) {
	for i := 0; i < len(users); i++ {
		for j := 0; j < len(users); j++ {
			if i != j {
				users[i].seed += expectedScore(users[j], users[i])
			}
		}
	}
	sumDelta := 0
	for i := 0; i < len(users); i++ {
		m := math.Sqrt(float64(users[i].Rank) * users[i].seed)
		r := calculateRatingToRank(users, i, m)
		users[i].delta = (r - users[i].OldRating) / 2
		sumDelta += int(users[i].delta)
	}
	inc := -(sumDelta / len(users)) - 1
	for i := 0; i < len(users); i++ {
		users[i].delta += inc
	}

	sort.Slice(users, func(i, j int) bool {
		return users[i].OldRating > users[j].OldRating
	})

	var s = min(int(4*math.Sqrt(float64(len(users)))), len(users))
	sumS := 0
	for i := 0; i < s; i++ {
		sumS += users[i].delta
	}
	inc = min(max(-(sumS/s), -10), 0)
	for i := 0; i < len(users); i++ {
		users[i].delta += inc
	}
	for i := 0; i < len(users); i++ {
		users[i].NewRating = users[i].OldRating + users[i].delta
	}
	sort.Slice(users, func(i, j int) bool {
		return users[i].Rank < users[j].Rank
	})
}

func min(a, b int) int {
	if a > b {
		return b
	}
	return a
}

func max(a, b int) int {
	if a > b {
		return a
	}
	return b
}
