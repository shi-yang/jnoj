package data

import (
	"context"
)

// ProblemTag 题目标签
type ProblemTag struct {
	ID   int
	Name string
}

// ProblemTagProblem 题目标签-题目关联表
type ProblemTagProblem struct {
	ID           int
	ProblemID    int
	ProblemTagID int
}

// GetProblemTags 查询题目标签
func (r *problemRepo) GetProblemTags(ctx context.Context, problemId int) []string {
	var res []string
	r.data.db.WithContext(ctx).
		Select("name").
		Model(&ProblemTag{}).
		Where("id in (?)", r.data.db.WithContext(ctx).
			Select("problem_tag_id").
			Model(&ProblemTagProblem{}).
			Where("problem_id = ?", problemId)).
		Find(&res)
	return res
}

// DeleteProblemTag 删除题目标签
func (r *problemRepo) DeleteProblemTag(ctx context.Context, problemId int, tag string) error {
	tagQuery := r.data.db.WithContext(ctx).Select("id").Model(&ProblemTag{}).Where("name = ?", tag)
	err := r.data.db.
		WithContext(ctx).
		Delete(&ProblemTagProblem{}, "problem_id = ? and problem_tag_id = (?)", problemId, tagQuery).
		Error
	if err != nil {
		return err
	}
	var count int64
	r.data.db.WithContext(ctx).Model(&ProblemTag{}).Where("name = ?", tag).Count(&count)
	if count == 0 {
		r.data.db.WithContext(ctx).Delete(&ProblemTag{}, "name = ?", tag)
	}
	return nil
}

// UpdateProblemTag 修改题目标签
func (r *problemRepo) UpdateProblemTag(ctx context.Context, problemId int, newTags []string) {
	oldTags := r.GetProblemTags(ctx, problemId)
	var (
		toBeDeleted []string
		toBeAdded   []string
	)
	// 比对需要删除的标签
	for _, oldTag := range oldTags {
		found := false
		for _, name := range newTags {
			if oldTag == name {
				found = true
			}
		}
		if !found {
			toBeDeleted = append(toBeDeleted, oldTag)
		}
	}
	// 比对需要添加的标签
	for _, newTag := range newTags {
		found := false
		for _, oldTag := range oldTags {
			if oldTag == newTag {
				found = true
			}
		}
		if !found {
			toBeAdded = append(toBeAdded, newTag)
		}
	}
	// 删除标签
	for _, tag := range toBeDeleted {
		r.DeleteProblemTag(ctx, problemId, tag)
	}
	for _, tag := range toBeAdded {
		var problemTag ProblemTag
		err := r.data.db.WithContext(ctx).
			First(&problemTag, "name = ?", tag).Error
		if err != nil {
			problemTag.Name = tag
			r.data.db.WithContext(ctx).Create(&problemTag)
		}
		r.data.db.WithContext(ctx).Create(&ProblemTagProblem{
			ProblemID:    problemId,
			ProblemTagID: problemTag.ID,
		})
	}
}
