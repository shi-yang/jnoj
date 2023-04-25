package v1

import "time"

type Contest struct {
	ID            int
	Title         string
	StartTime     time.Time
	EndTime       time.Time
	LockBoardTime *time.Time
	Status        int
	Editorial     string
	Description   string
	Type          int
	GroupID       int
	Scenario      int
	CreatedBy     int
}

type ContestAnnouncement struct {
	ID        int
	ContestID int
	Content   string
	CreatedAt time.Time
}

type ContestProblem struct {
	ID        int
	ProblemID int
	ContestID int
	Num       int
}

type ContestUser struct {
	ID           int
	UserID       int
	ContestID    int
	UserPassword string
	Rank         int
	RatingChange int
}

type Group struct {
	ID          int
	Name        string
	Description string
	Status      int
	JoinPolicy  int
	CreatedBy   int
	CreatedAt   time.Time
	UpdatedAt   time.Time
}

type GroupUser struct {
	ID        int
	GroupID   int
	UserID    int
	Role      int
	CreatedAt time.Time
}

type PolygonProblem struct {
	ID              int
	Title           string
	Description     string
	Input           string
	Output          string
	SampleInput     string
	SampleOutput    string
	Spj             bool
	SpjLang         int
	SpjSource       string
	Hint            string
	Source          string
	TimeLimit       float64
	MemoryLimit     int
	Status          int
	Accepted        int
	Submit          int
	Solved          int
	Tags            string
	Solution        string
	SolutionLang    int
	SoluitionSource string
	CreatedAt       time.Time
	UpdatedAt       time.Time
	CreatedBy       int
}

type PolygonStatus struct {
	ID        int
	ProblemID int
	Result    int
	Time      int
	Memory    int
	Info      string
	CreatedAt time.Time
	CreatedBy int
	Language  int
	Source    string
}

type Problem struct {
	ID               int
	Title            string
	Description      string
	Input            string
	Output           string
	SampleInput      string
	SampleOutput     string
	Spj              bool
	Hint             string
	Source           string
	TimeLimit        float64
	MemoryLimit      int
	Status           int
	Accepted         int
	Submit           int
	Solved           int
	Tags             string
	Solution         string
	CreatedAt        time.Time
	UpdatedAt        time.Time
	CreatedBy        int
	PolygonProblemID int
}

type Setting struct {
	ID    int
	Key   string
	Value string
}

type Solution struct {
	ID         int
	ProblemID  int
	Time       int
	Memory     int
	CreatedAt  time.Time
	Source     string
	Result     int
	Language   int
	ContestID  int
	Status     int
	CodeLength int
	Judgetime  time.Time
	PassInfo   string
	Score      int
	Judge      string
	CreatedBy  int
}

type SolutionInfo struct {
	SolutionID int
	RunInfo    string
}

type User struct {
	ID                 int
	Username           string
	Nickname           string
	AuthKey            string
	PasswordHash       string
	PasswordResetToken string
	Email              string
	Status             int
	Role               int
	Language           int
	CreatedAt          time.Time
	UpdatedAt          time.Time
	Rating             int
	IsVerifyEmail      int
	VerificationToken  string
}

type UserProfile struct {
	UserID        int
	Gender        int
	QqNumber      int
	Birthdate     time.Time
	Signature     string
	Address       string
	Description   string
	School        string
	StudentNumber string
	Major         string
}
