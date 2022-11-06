package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
)

// ProblemStatement is a ProblemStatement model.
type ProblemStatement struct {
	ID   int
	Name string
}

// ProblemStatementRepo is a ProblemStatement repo.
type ProblemStatementRepo interface {
	ListProblemStatements(context.Context, *v1.ListProblemStatementsRequest) ([]*ProblemStatement, int64)
	GetProblemStatement(context.Context, int) (*ProblemStatement, error)
	CreateProblemStatement(context.Context, *ProblemStatement) (*ProblemStatement, error)
	UpdateProblemStatement(context.Context, *ProblemStatement) (*ProblemStatement, error)
	DeleteProblemStatement(context.Context, int) error
}

// ListProblemStatements list ProblemStatement
func (uc *ProblemUsecase) ListProblemStatements(ctx context.Context, req *v1.ListProblemStatementsRequest) ([]*ProblemStatement, int64) {
	return uc.repo.ListProblemStatements(ctx, req)
}

// GetProblemStatement get a ProblemStatement
func (uc *ProblemUsecase) GetProblemStatement(ctx context.Context, id int) (*ProblemStatement, error) {
	return uc.repo.GetProblemStatement(ctx, id)
}

// CreateProblemStatement creates a ProblemStatement, and returns the new ProblemStatement.
func (uc *ProblemUsecase) CreateProblemStatement(ctx context.Context, g *ProblemStatement) (*ProblemStatement, error) {
	return uc.repo.CreateProblemStatement(ctx, g)
}

// UpdateProblemStatement update a ProblemStatement
func (uc *ProblemUsecase) UpdateProblemStatement(ctx context.Context, p *ProblemStatement) (*ProblemStatement, error) {
	return uc.repo.UpdateProblemStatement(ctx, p)
}

// DeleteProblemStatement delete a ProblemStatement
func (uc *ProblemUsecase) DeleteProblemStatement(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemStatement(ctx, id)
}
