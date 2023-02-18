package biz

import (
	"context"
)

// ProblemTag 题目标签
type ProblemTag struct {
	ID   int
	Name string
}

// ProblemTagRepo is a ProblemTag repo.
type ProblemTagRepo interface {
	UpdateProblemTag(ctx context.Context, problemId int, newTags []string)
	GetProblemTags(ctx context.Context, problemId int) []string
}
