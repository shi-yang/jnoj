package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"go.mongodb.org/mongo-driver/bson"
	"gorm.io/gorm/clause"
)

type ContestProblem struct {
	ID        int
	Number    int
	ContestID int
	ProblemID int
	CreatedAt time.Time
}

// ListContestProblems .
func (r *contestRepo) ListContestProblems(ctx context.Context, req *v1.ListContestProblemsRequest) ([]*biz.ContestProblem, int64) {
	res := []ContestProblem{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Where("contest_id = ?", req.Id).
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
			ID:        v.ID,
			Number:    v.Number,
			ContestID: v.ContestID,
			Name:      problemMap[v.ProblemID],
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
		ID:        o.ID,
		Number:    o.Number,
		ContestID: o.ContestID,
		TimeLimit: problem.TimeLimit,
		Memory:    problem.MemoryLimit,
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
	var filter = bson.D{{"problem_id", problem.ID}, {"is_example", true}}
	db := r.data.mongodb.Collection(ProblemTestCollection)
	cursor, err := db.Find(ctx, filter)
	if err != nil {
		return nil, err
	}
	defer cursor.Close(ctx)
	for cursor.Next(ctx) {
		var result ProblemTest
		err := cursor.Decode(&result)
		if err != nil {
			r.log.Error("cursor.Next() error:", err)
		}
		res.SampleTest = append(res.SampleTest, &biz.SampleTest{
			Input:  string(result.InputFileContent),
			Output: string(result.OutputFileContent),
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
		Model(&ContestProblem{
			ContestID: id,
		}).
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
func (r *contestRepo) UpdateContestProblem(ctx context.Context, b *biz.ContestProblem) (*biz.ContestProblem, error) {
	res := ContestProblem{
		ID: b.ID,
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
