package biz

import (
	"archive/zip"
	"bytes"
	"context"
	"fmt"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"
)

// ProblemTest is a ProblemTest model.
// 题目的测试点，包含测试点的输入和测试点的输出
type ProblemTest struct {
	ID                int
	ProblemID         int
	IsExample         bool   // 是否样例
	IsTestPoint       bool   // 是否测试点
	Name              string // 测试点名称
	InputSize         int64  // 输入文件大小
	InputPreview      string // 输入文件预览
	InputFileContent  []byte // 输入文件内容
	OutputSize        int64  // 输出文件大小
	OutputFileContent []byte // 输出文件内容
	OutputPreview     string // 输出文件预览
	Order             int    // 测评顺序
	Remark            string // 备注
	UserID            int
	CreatedAt         time.Time
	UpdatedAt         time.Time
}

type Test struct {
	ID     int
	Input  string
	Output string
}

// TestScore OI 测试点分数
type TestScore struct {
	ProblemID int
}

type TestGroup struct {
	TestID []int
	Score  int
}

// ProblemTestRepo is a ProblemTest repo.
type ProblemTestRepo interface {
	ListProblemTests(context.Context, *v1.ListProblemTestsRequest) ([]*ProblemTest, int64)
	GetProblemTest(context.Context, int) (*ProblemTest, error)
	CreateProblemTest(context.Context, *ProblemTest) (*ProblemTest, error)
	UpdateProblemTest(context.Context, *ProblemTest) (*ProblemTest, error)
	DeleteProblemTest(ctx context.Context, pid int, testIds []int32) error

	ListProblemTestContent(ctx context.Context, pid int, testIds []int, isSample bool) ([]*Test, error)
	SortProblemTests(context.Context, *v1.SortProblemTestsRequest)
	IsProblemTestSampleFirst(ctx context.Context, pid int) bool
}

// ListProblemTests list ProblemTest
func (uc *ProblemUsecase) ListProblemTests(ctx context.Context, req *v1.ListProblemTestsRequest) ([]*ProblemTest, int64, bool) {
	tests, count := uc.repo.ListProblemTests(ctx, req)
	isSampleFirst := uc.repo.IsProblemTestSampleFirst(ctx, int(req.Id))
	return tests, count, isSampleFirst
}

// GetProblemTest get a ProblemTest
func (uc *ProblemUsecase) GetProblemTest(ctx context.Context, id int) (*ProblemTest, error) {
	return uc.repo.GetProblemTest(ctx, id)
}

// CreateProblemTest creates a ProblemTest, and returns the new ProblemTest.
func (uc *ProblemUsecase) CreateProblemTest(ctx context.Context, p *ProblemTest) (*ProblemTest, error) {
	p.UserID, _ = auth.GetUserID(ctx)
	p.InputSize = int64(len(p.InputFileContent))
	// 读取 32 个字符作为内容
	if len(p.InputFileContent) < 32 {
		p.InputPreview = string(p.InputFileContent)
	} else {
		p.InputPreview = string(p.InputFileContent[:32]) + "..."
	}
	return uc.repo.CreateProblemTest(ctx, p)
}

// UpdateProblemTest update a ProblemTest
func (uc *ProblemUsecase) UpdateProblemTest(ctx context.Context, p *ProblemTest) (*ProblemTest, error) {
	return uc.repo.UpdateProblemTest(ctx, p)
}

// DeleteProblemTest delete a ProblemTest
func (uc *ProblemUsecase) DeleteProblemTest(ctx context.Context, pid int, testIds []int32) error {
	return uc.repo.DeleteProblemTest(ctx, pid, testIds)
}

// SortProblemTests .
func (uc *ProblemUsecase) SortProblemTests(ctx context.Context, req *v1.SortProblemTestsRequest) {
	uc.repo.SortProblemTests(ctx, req)
}

// DownloadProblemTests 下载题目测试点
func (uc *ProblemUsecase) DownloadProblemTests(ctx context.Context, problem *Problem, testIds []int) (*bytes.Buffer, error) {
	// 创建一个压缩文档
	buf := new(bytes.Buffer)
	zipFile := zip.NewWriter(buf)

	// 创建 tests 文件
	tests, _ := uc.repo.ListProblemTestContent(ctx, problem.ID, testIds, false)
	zero := 2
	if len(tests) >= 100 {
		zero = 3
	}
	for index, v := range tests {
		fin, _ := zipFile.Create(fmt.Sprintf("%0*d", zero, index+1))
		fin.Write([]byte(v.Input))
		fout, _ := zipFile.Create(fmt.Sprintf("%0*d.ans", zero, index+1))
		fout.Write([]byte(v.Output))
	}
	// zip 结束
	if err := zipFile.Close(); err != nil {
		return nil, err
	}
	return buf, nil
}
