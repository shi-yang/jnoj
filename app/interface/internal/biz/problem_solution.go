package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"time"
)

// ProblemSolution is a ProblemSolution model.
type ProblemSolution struct {
	ID        int
	Name      string
	ProblemID int
	Content   string
	UserID    int
	Type      string
	CreatedAt time.Time
	UpdatedAt time.Time
}

// ProblemSolutionRepo is a ProblemSolution repo.
type ProblemSolutionRepo interface {
	ListProblemSolutions(context.Context, *v1.ListProblemSolutionsRequest) ([]*ProblemSolution, int64)
	GetProblemSolution(context.Context, int) (*ProblemSolution, error)
	CreateProblemSolution(context.Context, *ProblemSolution) (*ProblemSolution, error)
	UpdateProblemSolution(context.Context, *ProblemSolution) (*ProblemSolution, error)
	DeleteProblemSolution(context.Context, int) error
}

// ListProblemSolutions list ProblemSolution
func (uc *ProblemUsecase) ListProblemSolutions(ctx context.Context, req *v1.ListProblemSolutionsRequest) ([]*ProblemSolution, int64) {
	return uc.repo.ListProblemSolutions(ctx, req)
}

// GetProblemSolution get a ProblemSolution
func (uc *ProblemUsecase) GetProblemSolution(ctx context.Context, id int) (*ProblemSolution, error) {
	return uc.repo.GetProblemSolution(ctx, id)
}

// CreateProblemSolution creates a ProblemSolution, and returns the new ProblemSolution.
func (uc *ProblemUsecase) CreateProblemSolution(ctx context.Context, p *ProblemSolution) (*ProblemSolution, error) {
	p.UserID, _ = auth.GetUserID(ctx)
	return uc.repo.CreateProblemSolution(ctx, p)
}

// UpdateProblemSolution update a ProblemSolution
func (uc *ProblemUsecase) UpdateProblemSolution(ctx context.Context, p *ProblemSolution) (*ProblemSolution, error) {
	return uc.repo.UpdateProblemSolution(ctx, p)
}

// DeleteProblemSolution delete a ProblemSolution
func (uc *ProblemUsecase) DeleteProblemSolution(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemSolution(ctx, id)
}

// RunProblemSolution .
func (uc *ProblemUsecase) RunProblemSolution(ctx context.Context, id int) error {
	solution, _ := uc.repo.GetProblemSolution(ctx, id)
	problem, err := uc.repo.GetProblem(ctx, solution.ProblemID)
	if err != nil {
		return err
	}
	tests, _ := uc.repo.ListProblemTests(context.TODO(), &v1.ListProblemTestsRequest{Id: int32(solution.ProblemID)})
	// 生成标准输出
	for _, test := range tests {
		if solution.Type == "model_solution" {
			resp, err := uc.sandboxClient.Run(ctx, &sandboxV1.RunRequest{
				Stdin:       string(test.InputFileContent),
				Source:      solution.Content,
				Language:    1, // C++
				MemoryLimit: problem.MemoryLimit,
				TimeLimit:   problem.TimeLimit,
			})
			if err != nil {
				return err
			}
			uc.repo.UpdateProblemTestStdOutput(ctx, test.ID, resp.Stdout)
		}
	}
	return nil
}
