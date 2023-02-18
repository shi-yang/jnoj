package data

import (
	"bytes"
	"context"
	"fmt"
	"runtime"
	"strconv"
	"time"

	"jnoj/app/sandbox/internal/biz"

	queueV1 "jnoj/api/queue/v1"
	objectstorage "jnoj/pkg/object_storage"

	"github.com/go-kratos/kratos/v2/encoding"
	_ "github.com/go-kratos/kratos/v2/encoding/json"
	"github.com/go-kratos/kratos/v2/log"
	"github.com/wagslane/go-rabbitmq"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type submissionRepo struct {
	data                *Data
	log                 *log.Helper
	websocketPublisher  *rabbitmq.Publisher
	submissionPublisher *rabbitmq.Publisher
}

// NewSubmissionRepo .
func NewSubmissionRepo(data *Data, logger log.Logger) biz.SubmissionRepo {
	websocketPublisher, err := rabbitmq.NewPublisher(
		data.mqConn,
		rabbitmq.WithPublisherOptionsLogging,
		rabbitmq.WithPublisherOptionsExchangeName("events"),
		rabbitmq.WithPublisherOptionsExchangeDeclare,
	)
	if err != nil {
		log.Fatal(err)
	}
	submissionPublisher, err := rabbitmq.NewPublisher(
		data.mqConn,
		rabbitmq.WithPublisherOptionsLogging,
		rabbitmq.WithPublisherOptionsExchangeName("events"),
		rabbitmq.WithPublisherOptionsExchangeDeclare,
	)
	if err != nil {
		log.Fatal(err)
	}
	return &submissionRepo{
		data:                data,
		log:                 log.NewHelper(logger),
		websocketPublisher:  websocketPublisher,
		submissionPublisher: submissionPublisher,
	}
}

type Submission struct {
	ID         int
	ProblemID  int
	Time       int
	Memory     int
	Verdict    int
	Language   int
	Score      int
	UserID     int
	Source     string
	EntityID   int
	EntityType int
	CreatedAt  time.Time
}

type SubmissionInfo struct {
	SubmissionID int
	RunInfo      string
}

type Problem struct {
	ID                 int
	Name               string
	Type               int
	TimeLimit          int64
	MemoryLimit        int64
	AcceptedCount      int
	SubmitCount        int
	UserID             int
	CheckerID          int
	VerificationStatus int
	CreatedAt          time.Time
	UpdatedAt          time.Time
	DeletedAt          gorm.DeletedAt
}

type ProblemTest struct {
	ID            int
	ProblemID     int
	Order         int
	Name          string // 测试点名称
	InputSize     int64  // 输入文件大小
	InputPreview  string // 输入文件预览
	OutputSize    int64  // 输出文件大小
	OutputPreview string // 输出文件预览
	Remark        string
	UserID        int
	IsExample     bool
	CreatedAt     time.Time
	UpdatedAt     time.Time
}

const problemTestInputPath = "/problem_tests/%d/%d.in"
const problemTestOutputPath = "/problem_tests/%d/%d.out"

type ContestProblem struct {
	ID            int
	Number        int
	ContestID     int
	ProblemID     int
	AcceptedCount int
	SubmitCount   int
	CreatedAt     time.Time
}

const ProblemTestCollection = "problem_test"

// ListSubmissions .
func (r *submissionRepo) GetProblem(ctx context.Context, id int) (*biz.Problem, error) {
	var p Problem
	err := r.data.db.WithContext(ctx).Model(&Problem{}).
		First(&p, "id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	res := &biz.Problem{
		ID:            p.ID,
		Type:          p.Type,
		TimeLimit:     p.TimeLimit,
		MemoryLimit:   p.MemoryLimit,
		AcceptedCount: p.AcceptedCount,
	}
	res.Checker, err = r.getProblemChecker(ctx, id)
	if err != nil {
		return res, err
	}
	return res, nil
}

func (r *submissionRepo) UpdateProblem(ctx context.Context, p *biz.Problem) (*biz.Problem, error) {
	update := Problem{
		ID:            p.ID,
		AcceptedCount: p.AcceptedCount,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&update).Error
	return nil, err
}

// GetContestProblemByProblemID .
func (r *submissionRepo) GetContestProblemByProblemID(ctx context.Context, cid int, problemID int) (*biz.ContestProblem, error) {
	var res ContestProblem
	err := r.data.db.WithContext(ctx).Model(ContestProblem{}).
		First(&res, "contest_id = ? and problem_id = ?", cid, problemID).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.ContestProblem{
		ID:            res.ID,
		ContestID:     res.ContestID,
		ProblemID:     res.ProblemID,
		Number:        res.Number,
		AcceptedCount: res.AcceptedCount,
	}, nil
}

// UpdateContestProblem .
func (r *submissionRepo) UpdateContestProblem(ctx context.Context, c *biz.ContestProblem) (*biz.ContestProblem, error) {
	res := ContestProblem{
		ID:            c.ID,
		AcceptedCount: c.AcceptedCount,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return &biz.ContestProblem{
		ID:            res.ID,
		AcceptedCount: res.AcceptedCount,
	}, err
}

func (r *submissionRepo) getProblemChecker(ctx context.Context, id int) (string, error) {
	var f ProblemFile
	err := r.data.db.WithContext(ctx).
		Where("id = (?)", r.data.db.Select("checker_id").Model(&Problem{}).Where("id = ?", id)).
		First(&f).Error
	if err != nil {
		return "", err
	}
	return f.Content, nil
}

func (r *submissionRepo) ListProblemTests(ctx context.Context, id int) []*biz.Test {
	var tests []ProblemTest
	r.data.db.WithContext(ctx).
		Model(&ProblemTest{}).
		Where("problem_id = ?", id).
		Order("`order`").
		Find(&tests)

	res := make([]*biz.Test, 0)
	for index, v := range tests {
		store := objectstorage.NewSeaweed()
		in, _ := store.GetObject(r.data.conf.ObjectStorage.PrivateBucket, fmt.Sprintf(problemTestInputPath, id, v.ID))
		out, _ := store.GetObject(r.data.conf.ObjectStorage.PrivateBucket, fmt.Sprintf(problemTestOutputPath, id, v.ID))
		res = append(res, &biz.Test{
			ID:     v.ID,
			Order:  index + 1,
			Input:  in,
			Output: out,
		})
	}
	return res
}

// GetSubmission .
func (r *submissionRepo) GetSubmission(ctx context.Context, id int) (*biz.Submission, error) {
	var res Submission
	err := r.data.db.Model(Submission{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.Submission{
		ID:         res.ID,
		ProblemID:  res.ProblemID,
		EntityID:   res.EntityID,
		EntityType: res.EntityType,
		Source:     res.Source,
		Memory:     res.Memory,
		Time:       res.Time,
		Verdict:    res.Verdict,
		Language:   res.Language,
		UserID:     res.UserID,
		CreatedAt:  res.CreatedAt,
	}, err
}

// UpdateSubmission .
func (r *submissionRepo) UpdateSubmission(ctx context.Context, s *biz.Submission) (*biz.Submission, error) {
	res := Submission{
		ID:      s.ID,
		Memory:  s.Memory,
		Time:    s.Time,
		Verdict: s.Verdict,
		Score:   s.Score,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// CreateSubmissionInfo .
func (r *submissionRepo) CreateSubmissionInfo(ctx context.Context, id int, runInfo string) error {
	res := SubmissionInfo{
		SubmissionID: id,
		RunInfo:      runInfo,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return err
}

func (r *submissionRepo) UpdateProblemTestStdOutput(ctx context.Context, id int, outputContent []byte, outputPreview string) error {
	var res ProblemTest
	err := r.data.db.Model(&ProblemTest{}).
		First(&res, "id = ?", id).
		Error
	if err != nil {
		return err
	}
	update := &ProblemTest{
		ID:            id,
		OutputSize:    int64(len(outputContent)),
		OutputPreview: outputPreview,
	}
	err = r.data.db.WithContext(ctx).
		Model(&ProblemTest{ID: id}).
		Select("OutputSize", "OutputPreview").
		Updates(update).Error
	if err != nil {
		return err
	}
	// 保存文件
	if update.OutputSize > 0 {
		store := objectstorage.NewSeaweed()
		storeName := fmt.Sprintf(problemTestOutputPath, res.ProblemID, res.ID)
		store.PutObject(r.data.conf.ObjectStorage.PrivateBucket, storeName, bytes.NewReader(outputContent))
	}
	return nil
}

// SendSubmissionToQueue 将提交加入到测评队列
func (r *submissionRepo) SendSubmissionToQueue(ctx context.Context, id int) error {
	idStr := strconv.Itoa(id)
	err := r.submissionPublisher.PublishWithContext(
		context.Background(),
		[]byte(idStr),
		[]string{"submission_key"},
		rabbitmq.WithPublishOptionsMandatory,
		rabbitmq.WithPublishOptionsPersistentDelivery,
		rabbitmq.WithPublishOptionsExchange("submission"),
	)
	return err
}

// RunSubmissionFromQueue 从测评队列中取出队列来进行测评
func (r *submissionRepo) RunSubmissionFromQueue(ctx context.Context, handler func(context.Context, int) error) error {
	concurrency := runtime.NumCPU()
	// TODO: 这里可以优化下，以充分利用CPU
	if concurrency > 4 {
		concurrency /= 2
	}
	_, err := rabbitmq.NewConsumer(
		r.data.mqConn,
		func(d rabbitmq.Delivery) rabbitmq.Action {
			submissionId, _ := strconv.Atoi(string(d.Body))
			handler(context.TODO(), submissionId)
			// rabbitmq.Ack, rabbitmq.NackDiscard, rabbitmq.NackRequeue
			return rabbitmq.Ack
		},
		"submission",
		rabbitmq.WithConsumerOptionsRoutingKey("submission_key"),
		rabbitmq.WithConsumerOptionsExchangeName("submission"),
		rabbitmq.WithConsumerOptionsExchangeDeclare,
		rabbitmq.WithConsumerOptionsExchangeDurable,
		rabbitmq.WithConsumerOptionsConcurrency(concurrency), // 并发执行数量
	)
	return err
}

// SendWebscoketMessage 发送 websocket 消息
func (r *submissionRepo) SendWebsocketMessage(ctx context.Context, message *queueV1.Message) error {
	jsonCodec := encoding.GetCodec("json")
	res, _ := jsonCodec.Marshal(message)
	r.websocketPublisher.PublishWithContext(
		context.Background(),
		res,
		[]string{"websocket"},
		rabbitmq.WithPublishOptionsExchange("websocket"),
	)
	return nil
}
