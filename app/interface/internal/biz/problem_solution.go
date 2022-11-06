package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
)

// ProblemSolution is a ProblemSolution model.
type ProblemSolution struct {
	ID   int
	Name string
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
func (uc *ProblemUsecase) CreateProblemSolution(ctx context.Context, g *ProblemSolution) (*ProblemSolution, error) {
	return uc.repo.CreateProblemSolution(ctx, g)
}

// UpdateProblemSolution update a ProblemSolution
func (uc *ProblemUsecase) UpdateProblemSolution(ctx context.Context, p *ProblemSolution) (*ProblemSolution, error) {
	return uc.repo.UpdateProblemSolution(ctx, p)
}

// DeleteProblemSolution delete a ProblemSolution
func (uc *ProblemUsecase) DeleteProblemSolution(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemSolution(ctx, id)
}
