package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"go.mongodb.org/mongo-driver/bson"
	"go.mongodb.org/mongo-driver/bson/primitive"
)

type ProblemTest struct {
	ID                primitive.ObjectID `bson:"_id"`
	ProblemID         int                `bson:"problem_id"`
	Order             int                `bson:"order"`
	Content           string             `bson:"content"` // 预览的文件内容
	InputSize         int64              `bson:"input_size"`
	InputFileContent  []byte             `bson:"input_file_content"`
	OutputSize        int64              `bson:"output_size"`
	OutputFileContent []byte             `bson:"output_file_content"`
	Remark            string             `bson:"remark"`
	UserID            int                `bson:"user_id"`
	IsExample         bool               `bson:"is_example"`
	CreatedAt         time.Time          `bson:"created_at"`
	UpdatedAt         time.Time          `bson:"updated_at"`
}

const ProblemTestCollection = "problem_test"

// ListProblemTests .
func (r *problemRepo) ListProblemTests(ctx context.Context, req *v1.ListProblemTestsRequest) ([]*biz.ProblemTest, int64) {
	var filter = bson.D{{"problem_id", req.Id}}
	var res []*biz.ProblemTest
	db := r.data.mongodb.Collection(ProblemTestCollection)
	count, err := db.CountDocuments(ctx, filter)
	if err != nil {
		return nil, count
	}
	cursor, err := db.Find(ctx, filter)
	if err != nil {
		return nil, count
	}
	defer cursor.Close(ctx)
	for cursor.Next(ctx) {
		var result ProblemTest
		err := cursor.Decode(&result)
		if err != nil {
			r.log.Error("cursor.Next() error:", err)
		}
		res = append(res, &biz.ProblemTest{
			ID:        result.ID.Hex(),
			Content:   result.Content,
			CreatedAt: result.CreatedAt,
			Remark:    result.Remark,
			IsExample: result.IsExample,
			InputSize: result.InputSize,
			Order:     result.Order,
		})
	}
	return res, count
}

func (r *problemRepo) ListProblemSampleTest(ctx context.Context, id int) ([]*biz.SampleTest, error) {
	var filter = bson.D{{"problem_id", id}, {"is_example", true}}
	var res []*biz.SampleTest
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
		res = append(res, &biz.SampleTest{
			Input:  string(result.InputFileContent),
			Output: string(result.OutputFileContent),
		})
	}
	return res, nil
}

// GetProblemTest .
func (r *problemRepo) GetProblemTest(ctx context.Context, id string) (*biz.ProblemTest, error) {
	oid, _ := primitive.ObjectIDFromHex(id)
	filter := bson.D{{"_id", oid}}
	var res ProblemTest
	err := r.data.mongodb.Collection(ProblemTestCollection).
		FindOne(ctx, filter).
		Decode(&res)
	if err != nil {
		return nil, err
	}
	return &biz.ProblemTest{
		ID:        res.ID.Hex(),
		Remark:    res.Remark,
		Content:   res.Content,
		IsExample: res.IsExample,
	}, err
}

// CreateProblemTest .
func (r *problemRepo) CreateProblemTest(ctx context.Context, b *biz.ProblemTest) (*biz.ProblemTest, error) {
	_, err := r.data.mongodb.Collection(ProblemTestCollection).InsertOne(ctx, ProblemTest{
		ID:               primitive.NewObjectID(),
		ProblemID:        b.ProblemID,
		InputFileContent: b.InputFileContent,
		Order:            b.Order,
		Content:          b.Content,
		UserID:           b.UserID,
		IsExample:        b.IsExample,
		Remark:           b.Remark,
		InputSize:        b.InputSize,
		CreatedAt:        time.Now(),
		UpdatedAt:        time.Now(),
	})

	return &biz.ProblemTest{}, err
}

// UpdateProblemTest .
func (r *problemRepo) UpdateProblemTest(ctx context.Context, p *biz.ProblemTest) (*biz.ProblemTest, error) {
	id, _ := primitive.ObjectIDFromHex(p.ID)
	filter := bson.D{{"_id", id}}
	update := bson.D{
		{"$set", bson.D{
			{"remark", p.Remark},
			{"is_example", p.IsExample},
		}},
	}
	_, err := r.data.mongodb.Collection(ProblemTestCollection).
		UpdateOne(ctx, filter, update)
	return nil, err
}

// DeleteProblemTest .
func (r *problemRepo) DeleteProblemTest(ctx context.Context, id string) error {
	oid, err := primitive.ObjectIDFromHex(id)
	if err != nil {
		return err
	}
	filter := bson.D{{"_id", oid}}
	_, err = r.data.mongodb.Collection(ProblemTestCollection).
		DeleteOne(ctx, filter)
	return err
}

func (r *problemRepo) UpdateProblemTestStdOutput(ctx context.Context, id string, content string) error {
	oid, _ := primitive.ObjectIDFromHex(id)
	filter := bson.D{{"_id", oid}}
	update := bson.D{
		{"$set", bson.D{
			{"output_size", len(content)},
			{"output_file_content", []byte(content)},
		}},
	}
	_, err := r.data.mongodb.Collection(ProblemTestCollection).
		UpdateOne(ctx, filter, update)
	return err
}
