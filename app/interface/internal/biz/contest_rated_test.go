package biz

import "testing"

func TestContestRated(t *testing.T) {
	var rating = []int{3182, 2203, 3042, 3622, 3279, 3741, 2530, 2530, 2530, 2530,
		2891, 3454, 2730, 3526, 2615, 2964, 2815, 2101, 2504, 2988, 2974,
		2653, 3377, 2820, 3024, 2336, 2566, 2796,
	}
	var players = []*ContestRatedPlayer{}
	for index, r := range rating {
		players = append(players, &ContestRatedPlayer{
			UserID:    index + 1,
			OldRating: r,
			Rank:      index + 1,
		})
	}
	ContestRated(players)
	for _, p := range players {
		t.Log(p.UserID, p.OldRating, "->", p.NewRating, p.NewRating-p.OldRating)
	}
}
