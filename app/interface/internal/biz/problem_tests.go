package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"time"
)

// ProblemTest is a ProblemTest model.
type ProblemTest struct {
	ID        int64
	Content   string
	Size      int64
	Remark    string
	UserID    int32
	IsExample bool
	CreatedAt time.Time
	UpdatedAt time.Time
}

// ProblemTestRepo is a ProblemTest repo.
type ProblemTestRepo interface {
	ListProblemTests(context.Context, *v1.ListProblemTestsRequest) ([]*ProblemTest, int64)
	GetProblemTest(context.Context, int) (*ProblemTest, error)
	CreateProblemTest(context.Context, *ProblemTest) (*ProblemTest, error)
	UpdateProblemTest(context.Context, *ProblemTest) (*ProblemTest, error)
	DeleteProblemTest(context.Context, int) error
}

// ListProblemTests list ProblemTest
func (uc *ProblemUsecase) ListProblemTests(ctx context.Context, req *v1.ListProblemTestsRequest) ([]*ProblemTest, int64) {
	return uc.repo.ListProblemTests(ctx, req)
}

// GetProblemTest get a ProblemTest
func (uc *ProblemUsecase) GetProblemTest(ctx context.Context, id int) (*ProblemTest, error) {
	return uc.repo.GetProblemTest(ctx, id)
}

// CreateProblemTest creates a ProblemTest, and returns the new ProblemTest.
func (uc *ProblemUsecase) CreateProblemTest(ctx context.Context, p *ProblemTest) (*ProblemTest, error) {
	return uc.repo.CreateProblemTest(ctx, p)
}

// UpdateProblemTest update a ProblemTest
func (uc *ProblemUsecase) UpdateProblemTest(ctx context.Context, p *ProblemTest) (*ProblemTest, error) {
	return uc.repo.UpdateProblemTest(ctx, p)
}

// DeleteProblemTest delete a ProblemTest
func (uc *ProblemUsecase) DeleteProblemTest(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemTest(ctx, id)
}
