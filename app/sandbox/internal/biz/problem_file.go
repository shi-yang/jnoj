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
	ProblemFileFileTypeChecker    ProblemFileFileType = "checker" // 检查器
	ProblemFileFileTypeValidator  ProblemFileFileType = "validator"
	ProblemFileFileTypeSolution   ProblemFileFileType = "solution"   // 解答方案
	ProblemFileFileTypeAttachment ProblemFileFileType = "attachment" // 附件
	ProblemFileFileTypeStatement  ProblemFileFileType = "statement"  // 描述图片
	ProblemFileFileTypePackage    ProblemFileFileType = "package"    // 打包文件
	ProblemFileFileTypeLanguage   ProblemFileFileType = "language"   // 语言
	ProblemFileFileTypeSubtask    ProblemFileFileType = "subtask"    // 子任务定义
)
