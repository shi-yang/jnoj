package biz

import "time"

// ProblemFile is a ProblemFile model.
type ProblemFile struct {
	ID          int
	Name        string
	Language    int
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
	ProblemFileFileTypeStatement  ProblemFileFileType = "statement"
	ProblemFileFileTypePackage    ProblemFileFileType = "package"
	ProblemFileFileTypeLanguage   ProblemFileFileType = "language"
)
