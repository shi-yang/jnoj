package data

import (
	"time"

	"gorm.io/gorm"
)

type ProblemsetProblem struct {
	ID           int
	ProblemID    int
	ProblemsetID int
	Order        int
	Problem      *Problem `gorm:"ForeignKey:ProblemID"`
}

type ProblemsetUser struct {
	ID            int
	ProblemsetID  int
	UserID        int
	AcceptedCount int // 过题量
	CreatedAt     time.Time
}

type Problem struct {
	ID                 int
	Name               string
	Type               int
	TimeLimit          int64
	MemoryLimit        int64
	AcceptedCount      int
	SubmitCount        int
	UserID             int
	CheckerID          int
	VerificationStatus int
	CreatedAt          time.Time
	UpdatedAt          time.Time
	DeletedAt          gorm.DeletedAt
}

type ProblemTest struct {
	ID            int
	ProblemID     int
	Order         int
	Name          string // 测试点名称
	InputSize     int64  // 输入文件大小
	InputPreview  string // 输入文件预览
	OutputSize    int64  // 输出文件大小
	OutputPreview string // 输出文件预览
	Remark        string
	UserID        int
	IsExample     bool
	IsTestPoint   bool
	CreatedAt     time.Time
	UpdatedAt     time.Time
}

const problemTestInputPath = "/problem_tests/%d/%d.in"
const problemTestOutputPath = "/problem_tests/%d/%d.out"
