package biz

import "time"

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
