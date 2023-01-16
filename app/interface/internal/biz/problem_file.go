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
	FileSize    int64
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
	ProblemFileFileTypeStatement  ProblemFileFileType = "statement"
	ProblemFileFileTypePackage    ProblemFileFileType = "package"
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
	uid, _ := auth.GetUserID(ctx)
	file, _ := uc.repo.GetProblemFile(ctx, id)
	problem, err := uc.repo.GetProblem(ctx, file.ProblemID)
	if err != nil {
		return err
	}
	// create a submission
	submission, _ := uc.submissionRepo.CreateSubmission(ctx, &Submission{
		ProblemID:  problem.ID,
		Source:     file.Content,
		UserID:     uid,
		Language:   1, // C++
		EntityID:   file.ID,
		EntityType: SubmissionEntityTypeProblemFile,
	})

	uc.sandboxClient.RunSubmission(ctx, &sandboxV1.RunSubmissionRequest{
		SubmissionId: int64(submission.ID),
	})
	return nil
}
