package data

import (
	"context"
	"fmt"
	"time"

	"jnoj/app/interface/internal/biz"
	objectstorage "jnoj/pkg/object_storage"

	"gorm.io/gorm/clause"
)

type ContestProblem struct {
	ID            int
	Number        int
	ContestID     int
	ProblemID     int
	AcceptedCount int
	SubmitCount   int
	CreatedAt     time.Time
}

// ListContestProblems .
func (r *contestRepo) ListContestProblems(ctx context.Context, cid int) ([]*biz.ContestProblem, int64) {
	res := []ContestProblem{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Where("contest_id = ?", cid).
		Order("number").
		Find(&res).
		Count(&count)
	problemId := make([]int, 0)
	for _, v := range res {
		problemId = append(problemId, v.ProblemID)
	}
	// 查询对应题目名称
	var statements []ProblemStatement
	r.data.db.WithContext(ctx).
		Select("problem_id, name").
		Where("problem_id in (?)", problemId).
		Find(&statements)
	problemMap := make(map[int]string)
	for _, v := range statements {
		_, ok := problemMap[v.ProblemID]
		if !ok {
			problemMap[v.ProblemID] = v.Name
		}
	}
	rv := make([]*biz.ContestProblem, 0)
	for _, v := range res {
		rv = append(rv, &biz.ContestProblem{
			ID:            v.ID,
			Number:        v.Number,
			ProblemID:     v.ProblemID,
			ContestID:     v.ContestID,
			SubmitCount:   v.SubmitCount,
			AcceptedCount: v.AcceptedCount,
			Name:          problemMap[v.ProblemID],
		})
	}
	return rv, count
}

// GetContestProblemByNumber .
func (r *contestRepo) GetContestProblemByNumber(ctx context.Context, cid int, number int) (*biz.ContestProblem, error) {
	var o ContestProblem
	var problem Problem
	var statements []ProblemStatement
	err := r.data.db.
		Model(ContestProblem{}).
		First(&o, "contest_id = ? and number = ?", cid, number).
		Error
	if err != nil {
		return nil, err
	}
	err = r.data.db.
		Model(Problem{}).
		First(&problem, "id = ?", o.ProblemID).
		Error
	if err != nil {
		return nil, err
	}
	r.data.db.
		Model(ProblemStatement{}).
		Find(&statements, "problem_id = ?", o.ProblemID)
	res := &biz.ContestProblem{
		ID:            o.ID,
		Number:        o.Number,
		ContestID:     o.ContestID,
		ProblemID:     o.ProblemID,
		SubmitCount:   o.SubmitCount,
		AcceptedCount: o.AcceptedCount,
		TimeLimit:     problem.TimeLimit,
		Memory:        problem.MemoryLimit,
		Type:          problem.Type,
	}
	if len(statements) > 0 {
		res.Name = statements[0].Name
	}
	for _, v := range statements {
		res.Statements = append(res.Statements, &biz.ProblemStatement{
			ID:       v.ID,
			Name:     v.Name,
			Input:    v.Input,
			Output:   v.Output,
			Legend:   v.Legend,
			Language: v.Language,
			Note:     v.Note,
		})
	}
	// 查样例
	var tests []ProblemTest
	r.data.db.WithContext(ctx).
		Model(&ProblemTest{}).
		Where("is_example = ?", true).
		Where("problem_id = ?", o.ProblemID).
		Find(&tests)

	for _, v := range tests {
		store := objectstorage.NewSeaweed()
		in, _ := store.GetObject(r.data.conf.ObjectStorage.PrivateBucket, fmt.Sprintf(problemTestInputPath, o.ProblemID, v.ID))
		out, _ := store.GetObject(r.data.conf.ObjectStorage.PrivateBucket, fmt.Sprintf(problemTestOutputPath, o.ProblemID, v.ID))
		res.SampleTest = append(res.SampleTest, &biz.Test{
			Input:  string(in),
			Output: string(out),
		})
	}
	return res, nil
}

// GetContestProblemByProblemID .
func (r *contestRepo) GetContestProblemByProblemID(ctx context.Context, cid int, problemID int) (*biz.ContestProblem, error) {
	var res ContestProblem
	err := r.data.db.WithContext(ctx).Model(ContestProblem{}).
		First(&res, "contest_id = ? and problem_id = ?", cid, problemID).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.ContestProblem{
		ID:        res.ID,
		ContestID: res.ContestID,
		ProblemID: res.ProblemID,
		Number:    res.Number,
	}, err
}

// CountContestProblem .
func (r *contestRepo) CountContestProblem(ctx context.Context, id int) int {
	var count int64
	r.data.db.WithContext(ctx).
		Model(&ContestProblem{}).
		Where("contest_id = ?", id).
		Count(&count)
	return int(count)
}

// CreateContestProblem .
func (r *contestRepo) CreateContestProblem(ctx context.Context, b *biz.ContestProblem) (*biz.ContestProblem, error) {
	res := ContestProblem{
		Number:    b.Number,
		ContestID: b.ContestID,
		ProblemID: b.ProblemID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.ContestProblem{
		ID: res.ID,
	}, err
}

// UpdateContestProblem .
func (r *contestRepo) UpdateContestProblem(ctx context.Context, c *biz.ContestProblem) (*biz.ContestProblem, error) {
	res := ContestProblem{
		ID:            c.ID,
		AcceptedCount: c.AcceptedCount,
		SubmitCount:   c.SubmitCount,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// DeleteContestProblem .
func (r *contestRepo) DeleteContestProblem(ctx context.Context, cid int, problemNumber int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete(ContestProblem{}, "contest_id = ? and number = ?", cid, problemNumber).
		Error
	if err != nil {
		return err
	}
	// 删除后，对题号重新排列
	var problems []ContestProblem
	r.data.db.WithContext(ctx).
		Select("id, number").
		Where("contest_id = ?", cid).
		Find(&problems)
	for index, item := range problems {
		r.data.db.WithContext(ctx).
			Model(ContestProblem{}).
			Where("id = ?", item.ID).
			UpdateColumn("number", index)
	}
	return nil
}
