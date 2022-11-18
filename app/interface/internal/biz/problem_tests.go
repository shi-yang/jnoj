package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/internal/middleware/auth"
	"time"
)

// ProblemTest is a ProblemTest model.
type ProblemTest struct {
	ID                string
	ProblemID         int
	Content           string // 预览的文件内容
	IsExample         bool
	InputSize         int64
	InputFileContent  []byte
	OutputSize        int64
	OutputFileContent []byte
	Order             int
	Remark            string
	UserID            int
	CreatedAt         time.Time
	UpdatedAt         time.Time
}

type SampleTest struct {
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
	GetProblemTest(context.Context, string) (*ProblemTest, error)
	CreateProblemTest(context.Context, *ProblemTest) (*ProblemTest, error)
	UpdateProblemTest(context.Context, *ProblemTest) (*ProblemTest, error)
	DeleteProblemTest(context.Context, string) error

	ListProblemSampleTest(context.Context, int) ([]*SampleTest, error)
	UpdateProblemTestStdOutput(context.Context, string, string) error
}

// ListProblemTests list ProblemTest
func (uc *ProblemUsecase) ListProblemTests(ctx context.Context, req *v1.ListProblemTestsRequest) ([]*ProblemTest, int64) {
	if !uc.hasUpdatePermission(ctx, int(req.Id)) {
		return nil, 0
	}
	return uc.repo.ListProblemTests(ctx, req)
}

// GetProblemTest get a ProblemTest
func (uc *ProblemUsecase) GetProblemTest(ctx context.Context, id string) (*ProblemTest, error) {
	return uc.repo.GetProblemTest(ctx, id)
}

// CreateProblemTest creates a ProblemTest, and returns the new ProblemTest.
func (uc *ProblemUsecase) CreateProblemTest(ctx context.Context, p *ProblemTest) (*ProblemTest, error) {
	if !uc.hasUpdatePermission(ctx, int(p.ProblemID)) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}

	p.UserID, _ = auth.GetUserID(ctx)
	p.InputSize = int64(len(p.InputFileContent))
	// 读取 32 个字符作为内容
	if len(p.InputFileContent) < 32 {
		p.Content = string(p.InputFileContent)
	} else {
		p.Content = string(p.InputFileContent[:32])
	}
	return uc.repo.CreateProblemTest(ctx, p)
}

// UpdateProblemTest update a ProblemTest
func (uc *ProblemUsecase) UpdateProblemTest(ctx context.Context, p *ProblemTest) (*ProblemTest, error) {
	if !uc.hasUpdatePermission(ctx, int(p.ProblemID)) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	return uc.repo.UpdateProblemTest(ctx, p)
}

// DeleteProblemTest delete a ProblemTest
func (uc *ProblemUsecase) DeleteProblemTest(ctx context.Context, pid int64, tid string) error {
	if !uc.hasUpdatePermission(ctx, int(pid)) {
		return v1.ErrorPermissionDenied("permission denied")
	}
	return uc.repo.DeleteProblemTest(ctx, tid)
}
