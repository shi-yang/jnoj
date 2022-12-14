package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"time"
)

// ProblemFile is a ProblemFile model.
type ProblemFile struct {
	ID          int
	Name        string
	Content     string
	FileType    string
	ProblemID   int
	UserID      int
	Type        string
	FileContent []byte
	CreatedAt   time.Time
	UpdatedAt   time.Time
}

type ProblemFileFileType string

const (
	ProblemFileFileTypeChecker    ProblemFileFileType = "checker"
	ProblemFileFileTypeValidator  ProblemFileFileType = "validator"
	ProblemFileFileTypeSolution   ProblemFileFileType = "solution"
	ProblemFileFileTypeAttachment ProblemFileFileType = "attachment"
)

const (
	ProblemFileTypeModelSolution = "model_solution"
)

// ProblemFileRepo is a ProblemFile repo.
type ProblemFileRepo interface {
	ListProblemFiles(context.Context, *v1.ListProblemFilesRequest) ([]*ProblemFile, int64)
	GetProblemFile(context.Context, int) (*ProblemFile, error)
	CreateProblemFile(context.Context, *ProblemFile) (*ProblemFile, error)
	UpdateProblemFile(context.Context, *ProblemFile) (*ProblemFile, error)
	DeleteProblemFile(context.Context, int) error

	GetProblemChecker(context.Context, int) (*ProblemFile, error)
}

// ListProblemFiles list ProblemFile
func (uc *ProblemUsecase) ListProblemFiles(ctx context.Context, req *v1.ListProblemFilesRequest) ([]*ProblemFile, int64) {
	return uc.repo.ListProblemFiles(ctx, req)
}

// GetProblemFile get a ProblemFile
func (uc *ProblemUsecase) GetProblemFile(ctx context.Context, id int) (*ProblemFile, error) {
	return uc.repo.GetProblemFile(ctx, id)
}

// CreateProblemFile creates a ProblemFile, and returns the new ProblemFile.
func (uc *ProblemUsecase) CreateProblemFile(ctx context.Context, p *ProblemFile) (*ProblemFile, error) {
	p.UserID, _ = auth.GetUserID(ctx)
	return uc.repo.CreateProblemFile(ctx, p)
}

// UpdateProblemFile update a ProblemFile
func (uc *ProblemUsecase) UpdateProblemFile(ctx context.Context, p *ProblemFile) (*ProblemFile, error) {
	return uc.repo.UpdateProblemFile(ctx, p)
}

// DeleteProblemFile delete a ProblemFile
func (uc *ProblemUsecase) DeleteProblemFile(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemFile(ctx, id)
}

// RunProblemFile .
func (uc *ProblemUsecase) RunProblemFile(ctx context.Context, id int) error {
	solution, _ := uc.repo.GetProblemFile(ctx, id)
	problem, err := uc.repo.GetProblem(ctx, solution.ProblemID)
	if err != nil {
		return err
	}
	tests, _ := uc.repo.ListProblemTestContent(context.TODO(), solution.ProblemID, false)
	// 生成标准输出
	for _, test := range tests {
		if solution.Type == "model_solution" {
			resp, err := uc.sandboxClient.Run(ctx, &sandboxV1.RunRequest{
				Stdin:       test.Input,
				Source:      solution.Content,
				Language:    1, // C++
				MemoryLimit: problem.MemoryLimit,
				TimeLimit:   problem.TimeLimit,
			})
			if err != nil {
				return err
			}
			var outputPreview string
			if len(resp.Stdout) > 32 {
				outputPreview = string(resp.Stdout[:32])
			} else {
				outputPreview = string(resp.Stdout)
			}
			uc.log.Info("stdin", len(test.Input))
			uc.log.Info("stdout", len(resp.Stdout))
			uc.repo.UpdateProblemTestStdOutput(ctx, test.ID, []byte(resp.Stdout), outputPreview)
		}
	}
	return nil
}
