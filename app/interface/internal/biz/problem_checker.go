package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
)

// ProblemChecker is a ProblemChecker model.
type ProblemChecker struct {
	ID   int
	Name string
}

// ProblemCheckerRepo is a ProblemChecker repo.
type ProblemCheckerRepo interface {
	ListProblemCheckers(context.Context, *v1.ListProblemCheckersRequest) ([]*ProblemChecker, int64)
	GetProblemChecker(context.Context, int) (*ProblemChecker, error)
	CreateProblemChecker(context.Context, *ProblemChecker) (*ProblemChecker, error)
	UpdateProblemChecker(context.Context, *ProblemChecker) (*ProblemChecker, error)
	DeleteProblemChecker(context.Context, int) error
}

// ListProblemCheckers list ProblemChecker
func (uc *ProblemUsecase) ListProblemCheckers(ctx context.Context, req *v1.ListProblemCheckersRequest) ([]*ProblemChecker, int64) {
	if !uc.hasUpdatePermission(ctx, int(req.Id)) {
		return nil, 0
	}
	return uc.repo.ListProblemCheckers(ctx, req)
}

// GetProblemChecker get a ProblemChecker
func (uc *ProblemUsecase) GetProblemChecker(ctx context.Context, id int) (*ProblemChecker, error) {
	if !uc.hasUpdatePermission(ctx, int(id)) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	return uc.repo.GetProblemChecker(ctx, id)
}

// CreateProblemChecker creates a ProblemChecker, and returns the new ProblemChecker.
func (uc *ProblemUsecase) CreateProblemChecker(ctx context.Context, g *ProblemChecker) (*ProblemChecker, error) {
	return uc.repo.CreateProblemChecker(ctx, g)
}

// UpdateProblemChecker update a ProblemChecker
func (uc *ProblemUsecase) UpdateProblemChecker(ctx context.Context, p *ProblemChecker) (*ProblemChecker, error) {
	return uc.repo.UpdateProblemChecker(ctx, p)
}

// DeleteProblemChecker delete a ProblemChecker
func (uc *ProblemUsecase) DeleteProblemChecker(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemChecker(ctx, id)
}
