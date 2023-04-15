package v2

import (
	"time"

	"gorm.io/gorm"
)

const (
	SubmissionVerdictPending = iota + 1
	SubmissionVerdictCompileError
	SubmissionVerdictWrongAnswer
	SubmissionVerdictAccepted
	SubmissionVerdictPresentationError
	SubmissionVerdictTimeLimit
	SubmissionVerdictMemoryLimit
	SubmissionVerdictRuntimeError
	SubmissionVerdictSysemError
)

const (
	SubmissionEntityTypeCommon = iota
	SubmissionEntityTypeContest
	SubmissionEntityTypeProblemFile
)

const (
	GroupPrivacyPrivate = iota
	GroupPrivatePublic
)

const (
	GroupMembershipAllowAnyone = iota
	GroupMembershipInvitationCode
)

const (
	GroupUserRoleAdmin = iota
	GroupUserRoleManager
	GroupUserRoleMember
	GroupUserRoleGuest
)

const (
	ContestPrivacyPrivate = iota // 私有
	ContestPrivacyPublic         // 公开
)

// 测试点的储存路径
const ProblemTestInputPath = "/problem_tests/%d/%d.in"
const ProblemTestOutputPath = "/problem_tests/%d/%d.out"

// 定义文件储存路径。定义了的储存在公开的对象储存，没有定义的直接储存在数据库
// %d problemId
// %s filename
var ProblemFileStorePath = map[ProblemFileFileType]string{
	ProblemFileFileTypeAttachment: "/problem_files/%d/attachment/%s",
	ProblemFileFileTypePackage:    "/problem_files/%d/package/%s",
	ProblemFileFileTypeStatement:  "/problem_files/%d/statement/%s",
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

const (
	// TypeSolution
	ProblemFileTypeModelSolution = "model_solution"
)

type ContestProblem struct {
	ID            int
	Number        int
	ContestID     int
	ProblemID     int
	AcceptedCount int
	SubmitCount   int
	CreatedAt     time.Time
}

type ContestUser struct {
	ID        int
	ContestID int
	UserID    int
	Name      string
	Role      int
	CreatedAt time.Time
}

type Contest struct {
	ID               int
	Name             string
	StartTime        time.Time
	EndTime          time.Time
	FrozenTime       *time.Time
	Type             int
	Privacy          int    // 隐私设置，私有、公开
	Membership       int    // 参赛资格
	InvitationCode   string // 邀请码
	Description      string
	GroupID          int
	UserID           int
	ParticipantCount int
	CreatedAt        time.Time
	UpdatedAt        time.Time
	DeletedAt        gorm.DeletedAt
}

type Group struct {
	ID             int
	Name           string
	Description    string
	Privacy        int    // 隐私设置
	Membership     int    // 加入资格
	InvitationCode string // 邀请码
	MemberCount    int
	UserID         int
	CreatedAt      time.Time
}

// GroupUser .
type GroupUser struct {
	ID        int
	GroupID   int
	UserID    int
	Role      int
	CreatedAt time.Time
}

type ProblemFile struct {
	ID        int
	Name      string
	Language  int    // 语言
	Content   string // 文件内容或路径
	Type      string
	FileSize  int64 // 文件大小
	ProblemID int
	UserID    int
	FileType  string // 业务类型
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

type ProblemStatement struct {
	ID        int
	ProblemID int
	Name      string
	Language  string
	Legend    string
	Input     string
	Output    string
	Note      string
	UserID    int
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

// ProblemTag 题目标签
type ProblemTag struct {
	ID   int
	Name string
}

// ProblemTagProblem 题目标签-题目关联表
type ProblemTagProblem struct {
	ID           int
	ProblemID    int
	ProblemTagID int
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
	CreatedAt     time.Time
	UpdatedAt     time.Time
}

type ProblemVerification struct {
	ID                 int
	ProblemID          int
	VerificationStatus int // 题目完整性
	VerificationInfo   string
	CreatedAt          time.Time
}

type Problem struct {
	ID                int
	Name              string
	TimeLimit         int64
	MemoryLimit       int64
	AcceptedCount     int
	SubmitCount       int
	UserID            int
	Type              int
	CheckerID         int
	Status            int
	Source            string
	CreatedAt         time.Time
	UpdatedAt         time.Time
	DeletedAt         gorm.DeletedAt
	ProblemStatements []*ProblemStatement `gorm:"ForeignKey:ProblemID"`
	ProblemTags       []*ProblemTag       `gorm:"many2many:problem_tag_problem"`
}

type Problemset struct {
	ID           int
	Name         string
	Description  string
	UserID       int
	ProblemCount int
	CreatedAt    time.Time
	UpdatedAt    time.Time
	DeletedAt    gorm.DeletedAt
}

type ProblemsetProblem struct {
	ID           int
	ProblemID    int
	ProblemsetID int
	Order        int
	Problem      *Problem `gorm:"ForeignKey:ProblemID"`
}

type Submission struct {
	ID         int
	ProblemID  int
	Time       int
	Memory     int
	Verdict    int
	Language   int
	Score      int
	UserID     int
	EntityID   int
	EntityType int
	Source     string
	CreatedAt  time.Time
}

type SubmissionInfo struct {
	SubmissionID int
	RunInfo      string
}

type User struct {
	ID        int
	Username  string
	Nickname  string
	Password  string
	Email     string
	Phone     string
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}
