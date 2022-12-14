package data

import (
	"time"

	"gorm.io/gorm"
)

type ProblemFile struct {
	ID        int
	Name      string
	Content   string
	Type      string
	ProblemID int
	UserID    int
	FileType  string
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}
