package biz

import (
	"archive/zip"
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

// Problem is a Problem model.
type Problem struct {
	ID            int
	Name          string
	UserID        int
	TimeLimit     int64
	MemoryLimit   int64
	AcceptedCount int
	SubmitCount   int
	CheckerID     int
	Status        int
	Source        string
	CreatedAt     time.Time
	UpdatedAt     time.Time

	Statements  []*ProblemStatement
	SampleTests []*Test
}

const (
	ProblemStatusPrivate = iota + 1 // 私有
	ProblemStatusPublic             // 公开
)

// 题目权限
type ProblemPermissionType int32

const (
	ProblemPermissionView   ProblemPermissionType = 0 // 查看权限
	ProblemPermissionUpdate ProblemPermissionType = 1 // 修改权限
)

// HasPermission 是否有权限
// 查看权限，需要题目出于公开或者是创建人才能查看
// 修改权限，仅题目创建人可以看
func (p *Problem) HasPermission(ctx context.Context, t ProblemPermissionType) bool {
	userID, _ := auth.GetUserID(ctx)
	if t == ProblemPermissionView {
		return p.UserID == userID || p.Status == ProblemStatusPublic
	}
	return p.UserID == userID
}

// ProblemRepo is a Problem repo.
type ProblemRepo interface {
	ListProblems(context.Context, *v1.ListProblemsRequest) ([]*Problem, int64)
	GetProblem(context.Context, int) (*Problem, error)
	CreateProblem(context.Context, *Problem) (*Problem, error)
	UpdateProblem(context.Context, *Problem) (*Problem, error)
	DeleteProblem(context.Context, int) error

	UpdateProblemChecker(context.Context, int, int) error
	ProblemTestRepo
	ProblemStatementRepo
	ProblemFileRepo
	ProblemVerificationRepo
}

// ProblemUsecase is a Problem usecase.
type ProblemUsecase struct {
	repo           ProblemRepo
	sandboxClient  sandboxV1.SandboxServiceClient
	submissionRepo SubmissionRepo
	log            *log.Helper
}

// NewProblemUsecase new a Problem usecase.
func NewProblemUsecase(repo ProblemRepo,
	sandboxClient sandboxV1.SandboxServiceClient,
	logger log.Logger,
	submissionRepo SubmissionRepo,
) *ProblemUsecase {
	return &ProblemUsecase{
		repo:           repo,
		sandboxClient:  sandboxClient,
		submissionRepo: submissionRepo,
		log:            log.NewHelper(logger),
	}
}

// ListProblems list Problem
func (uc *ProblemUsecase) ListProblems(ctx context.Context, req *v1.ListProblemsRequest) ([]*Problem, int64) {
	return uc.repo.ListProblems(ctx, req)
}

// GetProblem get a Problem
func (uc *ProblemUsecase) GetProblem(ctx context.Context, id int) (*Problem, error) {
	p, err := uc.repo.GetProblem(ctx, id)
	if err != nil {
		return nil, err
	}
	statements, _ := uc.repo.ListProblemStatements(ctx, &v1.ListProblemStatementsRequest{
		Id: int32(id),
	})
	tests, _ := uc.repo.ListProblemTestContent(ctx, id, true)
	for _, v := range statements {
		p.Statements = append(p.Statements, &ProblemStatement{
			ID:       v.ID,
			Input:    v.Input,
			Output:   v.Output,
			Name:     v.Name,
			Legend:   v.Legend,
			Language: v.Language,
			Note:     v.Note,
		})
	}
	for _, v := range tests {
		p.SampleTests = append(p.SampleTests, &Test{
			Input:  v.Input,
			Output: v.Output,
		})
	}
	return p, nil
}

// CreateProblem creates a Problem, and returns the new Problem.
func (uc *ProblemUsecase) CreateProblem(ctx context.Context, p *Problem) (*Problem, error) {
	userID, _ := auth.GetUserID(ctx)
	p.UserID = userID
	p.MemoryLimit = 256
	p.TimeLimit = 1000
	p.Status = ProblemStatusPrivate
	return uc.repo.CreateProblem(ctx, p)
}

// UpdateProblem update a Problem
func (uc *ProblemUsecase) UpdateProblem(ctx context.Context, p *Problem) (*Problem, error) {
	// 题目设为公开，需要保证题目通过验证
	if p.Status == ProblemStatusPublic {
		// 检查题目是否通过验证
		if res, err := uc.GetProblemVerification(ctx, p.ID); err != nil || res.VerificationStatus != VerificationStatusSuccess {
			return nil, v1.ErrorProblemNotVerification("题目未通过验证")
		}
	}
	return uc.repo.UpdateProblem(ctx, p)
}

// DeleteProblem delete a Problem
func (uc *ProblemUsecase) DeleteProblem(ctx context.Context, id int) error {
	return uc.repo.DeleteProblem(ctx, id)
}

func (uc *ProblemUsecase) UpdateProblemChecker(ctx context.Context, id int, checkerID int) error {
	return uc.repo.UpdateProblemChecker(ctx, id, checkerID)
}

// PackProblem 打包题目
func (uc *ProblemUsecase) PackProblem(ctx context.Context, id int) error {
	problem, _ := uc.repo.GetProblem(ctx, id)
	// 创建一个压缩文档
	buf := new(bytes.Buffer)
	zipFile := zip.NewWriter(buf)

	// 创建 tests 文件
	tests, _ := uc.repo.ListProblemTestContent(ctx, id, false)
	zero := 2
	if len(tests) >= 100 {
		zero = 3
	}
	for index, v := range tests {
		fin, _ := zipFile.Create(fmt.Sprintf("tests/%0*d", zero, index+1))
		fin.Write([]byte(v.Input))
		fout, _ := zipFile.Create(fmt.Sprintf("tests/%0*d.ans", zero, index+1))
		fout.Write([]byte(v.Output))
	}
	// 创建 statements
	statements, _ := uc.repo.ListProblemStatements(ctx, &v1.ListProblemStatementsRequest{
		Id: int32(id),
	})
	type statementSampleTest struct {
		Input string `json:"input"`
		Ouput string `json:"output"`
	}
	type statement struct {
		Name        string                `json:"name"`
		Input       string                `json:"input"`
		Output      string                `json:"output"`
		Notes       string                `json:"notes"`
		Legend      string                `json:"legend"`
		TimeLimit   int64                 `json:"timeLimit"`
		MemoryLimit int64                 `json:"memoryLimit"`
		Language    string                `json:"language"`
		SampleTest  []statementSampleTest `json:"sampleTests"`
	}
	samples, _ := uc.repo.ListProblemTestContent(ctx, id, true)
	for _, v := range statements {
		fstatement, _ := zipFile.Create(fmt.Sprintf("%s/problem-properites.json", v.Language))
		s := statement{
			Name:        v.Name,
			Input:       v.Input,
			Output:      v.Output,
			Notes:       v.Note,
			Legend:      v.Legend,
			TimeLimit:   problem.TimeLimit,
			MemoryLimit: problem.MemoryLimit * 1024 * 1024,
			Language:    v.Language,
		}
		for _, sample := range samples {
			s.SampleTest = append(s.SampleTest, statementSampleTest{
				Input: sample.Input,
				Ouput: sample.Output,
			})
		}
		sjson, _ := json.Marshal(s)
		fstatement.Write(sjson)
	}
	// 创建 check
	checker, err := uc.repo.GetProblemChecker(ctx, id)
	if err == nil {
		checkFile, _ := zipFile.Create("check.cpp")
		checkFile.Write([]byte(checker.Content))
	}
	// zip 结束
	if err := zipFile.Close(); err != nil {
		return err
	}
	// 储存文件
	uc.repo.CreateProblemFile(ctx, &ProblemFile{
		ProblemID:   id,
		Name:        problem.Name + ".zip",
		FileType:    string(ProblemFileFileTypePackage),
		UserID:      problem.UserID,
		FileContent: buf.Bytes(),
	})
	return nil
}
