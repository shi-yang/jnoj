package biz

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"regexp"
	"strconv"
	"strings"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/xuri/excelize/v2"
)

// Problemset is a Problemset model.
type Problemset struct {
	ID             int
	ParentID       int
	ChildOrder     int
	Name           string
	Type           int
	UserID         int
	Description    string
	Role           int    // 登录用户角色
	Membership     int    // 加入资格
	InvitationCode string // 邀请码
	ProblemCount   int
	MemberCount    int
	User           *User
	CreatedAt      time.Time
	Children       []*Problemset
	Parent         *Problemset
}

const (
	ProblemsetMembershipAllowAnyone    = iota // 允许任何人
	ProblemsetMembershipInvitationCode        // 凭邀请码
)

const (
	ProblemsetRoleGuest  = iota // 游客
	ProblemsetRolePlayer        // 选手
	ProblemsetRoleAdmin         // 管理
)

type ProblemsetAnswer struct {
	ID                   int
	ProblemsetID         int
	UserID               int
	Score                float32
	Answer               string
	AnsweredProblemIDs   string // 回答题目题目
	UnansweredProblemIDs string // 未回答题目
	CorrectProblemIDs    string // 正确题目
	WrongProblemIDs      string // 错误题目
	SubmissionIDs        string // 编程题提交ID
	Submissions          []*Submission
	SubmittedAt          *time.Time // 提交时间
	CreatedAt            time.Time  // 创建时间
	UpdatedAt            time.Time
}

const (
	ProblemsetTypeSimple = iota
	ProblemsetTypeExam
)

// HasPermission 是否有权限修改
func (p *Problemset) HasPermission(ctx context.Context) bool {
	uid, role := auth.GetUserID(ctx)
	return uid == p.UserID || role != UserRoleSuperAdmin
}

// ProblemsetProblem Problemset's Problem model.
type ProblemsetProblem struct {
	ID            int
	Name          string
	Order         int     // 题目次序 1,2,3,4
	Score         float32 // 题目分数
	Type          int
	ProblemID     int
	ProblemsetID  int
	SubmitCount   int
	AcceptedCount int
	TimeLimit     int
	MemoryLimit   int
	Statement     *ProblemStatement
	Source        string
	Tags          []string
	Status        int
	CreatedAt     time.Time

	SampleTests []*Test
}

type ProblemsetUser struct {
	ID            int
	ProblemsetID  int
	UserID        int
	UserNickname  string
	UserAvatar    string
	Role          int // 用户角色
	AcceptedCount int // 过题量
	CreatedAt     time.Time
}

// ProblemsetRepo is a Problemset repo.
type ProblemsetRepo interface {
	ListProblemsets(context.Context, *v1.ListProblemsetsRequest) ([]*Problemset, int64)
	GetProblemset(context.Context, int) (*Problemset, error)
	CreateProblemset(context.Context, *Problemset) (*Problemset, error)
	UpdateProblemset(context.Context, *Problemset) (*Problemset, error)
	DeleteProblemset(context.Context, int) error

	CreateProblemsetChild(ctx context.Context, sid int, childId int) error
	DeleteProblemsetChild(ctx context.Context, sid int, childId int) error
	SortProblemsetChild(ctx context.Context, req *v1.SortProblemsetChildRequest) error

	ListProblemsetProblems(context.Context, *v1.ListProblemsetProblemsRequest) ([]*ProblemsetProblem, int64)
	ListProblemsetProblemStatements(context.Context, []int) map[int]*ProblemStatement
	UpdateProblemsetProblem(ctx context.Context, sid int, pid int, problem *ProblemsetProblem) (*ProblemsetProblem, error)
	GetProblemsetProblem(ctx context.Context, sid int, order int) (*ProblemsetProblem, error)
	GetProblemsetLateralProblem(context.Context, int, int) (int, int)
	AddProblemToProblemset(context.Context, *ProblemsetProblem) error
	DeleteProblemFromProblemset(ctx context.Context, sid int, order int) error
	SortProblemsetProblems(ctx context.Context, req *v1.SortProblemsetProblemsRequest) error

	ListProblemsetUsers(ctx context.Context, req *v1.ListProblemsetUsersRequest) ([]*ProblemsetUser, int64)
	CreateProblemsetUser(ctx context.Context, g *ProblemsetUser) (*ProblemsetUser, error)
	DeleteProblemsetUser(ctx context.Context, sid int, uid int) error

	CreateProblemsetAnswer(ctx context.Context, answer *ProblemsetAnswer) (*ProblemsetAnswer, error)
	ListProblemsetAnswers(ctx context.Context, req *v1.ListProblemsetAnswersRequest) ([]*ProblemsetAnswer, int64)
	GetProblemsetAnswer(ctx context.Context, pid int, answerid int) (*ProblemsetAnswer, error)
	UpdateProblemsetAnswer(ctx context.Context, id int, answer *ProblemsetAnswer) error
}

// ProblemsetUsecase is a Problemset usecase.
type ProblemsetUsecase struct {
	repo           ProblemsetRepo
	userRepo       UserRepo
	problemRepo    ProblemRepo
	submissionRepo SubmissionRepo
	sandboxClient  sandboxV1.SandboxServiceClient
	log            *log.Helper
}

// NewProblemsetUsecase new a Problemset usecase.
func NewProblemsetUsecase(
	repo ProblemsetRepo,
	problemRepo ProblemRepo,
	submissionRepo SubmissionRepo,
	userRepo UserRepo,
	sandboxClient sandboxV1.SandboxServiceClient,
	logger log.Logger,
) *ProblemsetUsecase {
	return &ProblemsetUsecase{
		repo:           repo,
		problemRepo:    problemRepo,
		submissionRepo: submissionRepo,
		userRepo:       userRepo,
		sandboxClient:  sandboxClient,
		log:            log.NewHelper(logger),
	}
}

// ListProblemsets list Problemset
func (uc *ProblemsetUsecase) ListProblemsets(ctx context.Context, req *v1.ListProblemsetsRequest) ([]*Problemset, int64) {
	return uc.repo.ListProblemsets(ctx, req)
}

// GetProblemset get a Problemset
func (uc *ProblemsetUsecase) GetProblemset(ctx context.Context, id int) (*Problemset, error) {
	return uc.repo.GetProblemset(ctx, id)
}

// CreateProblemset creates a Problemset, and returns the new Problemset.
func (uc *ProblemsetUsecase) CreateProblemset(ctx context.Context, g *Problemset) (*Problemset, error) {
	return uc.repo.CreateProblemset(ctx, g)
}

// UpdateProblemset update a Problemset
func (uc *ProblemsetUsecase) UpdateProblemset(ctx context.Context, p *Problemset) (*Problemset, error) {
	return uc.repo.UpdateProblemset(ctx, p)
}

// DeleteProblemset delete a Problemset
func (uc *ProblemsetUsecase) DeleteProblemset(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemset(ctx, id)
}

// CreateProblemsetChild create a ProblemsetChild
func (uc *ProblemsetUsecase) CreateProblemsetChild(ctx context.Context, sid int, childId int) error {
	problemset, err := uc.repo.GetProblemset(ctx, sid)
	if err != nil {
		return v1.ErrorBadRequest("problemset not found")
	}
	// 权限校验
	if problemset.Role != ProblemsetRoleAdmin {
		return v1.ErrorForbidden("no permission")
	}
	child, err := uc.repo.GetProblemset(ctx, childId)
	if err != nil {
		return v1.ErrorBadRequest("problemset not found")
	}
	// 权限校验
	if child.Role != ProblemsetRoleAdmin {
		return v1.ErrorForbidden("no permission")
	}
	// 禁止套娃，已经有儿子的，不能作为其它题单的儿子
	if len(child.Children) > 0 {
		return v1.ErrorBadRequest("child has children")
	}
	// 已经有父亲的，不能作为其它题单的儿子
	if child.ParentID != 0 {
		return v1.ErrorBadRequest("child has parent")
	}
	return uc.repo.CreateProblemsetChild(ctx, sid, childId)
}

// DeleteProblemsetChild delete a ProblemsetChild
func (uc *ProblemsetUsecase) DeleteProblemsetChild(ctx context.Context, sid int, childId int) error {
	return uc.repo.DeleteProblemsetChild(ctx, sid, childId)
}

// SortProblemsetChild 调整子题单顺序
func (uc *ProblemsetUsecase) SortProblemsetChild(ctx context.Context, req *v1.SortProblemsetChildRequest) error {
	return uc.repo.SortProblemsetChild(ctx, req)
}

// ListProblemsetUsers 获取题单的用户
func (uc *ProblemsetUsecase) ListProblemsetUsers(ctx context.Context, req *v1.ListProblemsetUsersRequest) ([]*ProblemsetUser, int64) {
	return uc.repo.ListProblemsetUsers(ctx, req)
}

// CreateProblemsetUser 添加用户到题单
// 刷题资格为任何人时，提交代码会自动加入，因此此时只需要对两种加入情况进行处理：
// 1. 题单创建人在题单管理页面加入
// 2. 刷题资格为邀请码时，用户填写邀请码来加入
func (uc *ProblemsetUsecase) CreateProblemsetUser(ctx context.Context, req *v1.CreateProblemsetUserRequest) (*ProblemsetUser, error) {
	problemset, err := uc.repo.GetProblemset(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorBadRequest("problemset not found")
	}
	if req.Username == "" && req.InvitationCode == "" {
		return nil, v1.ErrorBadRequest("username or invitation code is required")
	}
	uid, _ := auth.GetUserID(ctx)
	// 管理后台加入，需要当前登录用户有权限
	if req.Username != "" {
		if problemset.Role != ProblemsetRoleAdmin {
			return nil, v1.ErrorForbidden("permission denied")
		}
		user, err := uc.userRepo.GetUser(ctx, &User{Username: req.Username})
		if err != nil {
			return nil, v1.ErrorBadRequest("user not found")
		}
		uid = user.ID
	}
	// 用户邀请码方式加入，校验邀请码
	if req.InvitationCode != "" {
		if strings.TrimSpace(req.InvitationCode) != strings.TrimSpace(problemset.InvitationCode) {
			return nil, v1.ErrorBadRequest("invalid invitation code")
		}
		if problemset.Role != ProblemsetRoleGuest {
			return nil, v1.ErrorBadRequest("you are already in this problemset")
		}
	}
	return uc.repo.CreateProblemsetUser(ctx, &ProblemsetUser{
		UserID:       uid,
		ProblemsetID: int(req.Id),
	})
}

// DeleteProblemsetUser 删除题单用户
func (uc *ProblemsetUsecase) DeleteProblemsetUser(ctx context.Context, sid int, uid int) error {
	return uc.repo.DeleteProblemsetUser(ctx, sid, uid)
}

func (uc *ProblemsetUsecase) ListProblemsetProblems(ctx context.Context, problemset *Problemset, req *v1.ListProblemsetProblemsRequest) ([]*ProblemsetProblem, int64) {
	problems, count := uc.repo.ListProblemsetProblems(ctx, req)
	// 登录用户查询解答情况
	uid, _ := auth.GetUserID(ctx)
	ids := make([]int, 0)
	for _, v := range problems {
		ids = append(ids, v.ProblemID)
	}
	if uid != 0 {
		statusMap := uc.problemRepo.GetProblemsStatus(ctx, SubmissionEntityTypeProblemset, nil, uid, ids)
		for k, v := range problems {
			problems[k].Status = statusMap[v.ProblemID]
		}
	}
	// 试卷题单直接展示所有题目内容
	if problemset.Type == ProblemsetTypeExam {
		statementMap := uc.repo.ListProblemsetProblemStatements(ctx, ids)
		for k, v := range problems {
			if v.Type == ProblemTypeDefault || v.Type == ProblemTypeFunction {
				p, _ := uc.problemRepo.GetProblem(ctx, v.ProblemID)
				problems[k].TimeLimit = int(p.TimeLimit)
				problems[k].MemoryLimit = int(p.MemoryLimit)
				tests, _ := uc.problemRepo.ListProblemTestContent(ctx, p.ID, nil, true)
				problems[k].SampleTests = tests
			}
			problems[k].Statement = statementMap[v.ProblemID]
		}
	}
	return problems, count
}

func (uc *ProblemsetUsecase) GetProblemsetProblem(ctx context.Context, sid int, order int) (*ProblemsetProblem, error) {
	return uc.repo.GetProblemsetProblem(ctx, sid, order)
}

func (uc *ProblemsetUsecase) UpdateProblemsetProblem(ctx context.Context, sid int, pid int, problem *ProblemsetProblem) (*ProblemsetProblem, error) {
	return uc.repo.UpdateProblemsetProblem(ctx, sid, pid, problem)
}

func (uc *ProblemsetUsecase) GetProblemsetLateralProblem(ctx context.Context, id int, pid int) (int, int) {
	return uc.repo.GetProblemsetLateralProblem(ctx, id, pid)
}

func (uc *ProblemsetUsecase) AddProblemToProblemset(ctx context.Context, problemset *Problemset, pid int) error {
	if problemset.Type == ProblemsetTypeExam && problemset.ProblemCount >= 100 {
		return v1.ErrorBadRequest("limited to 100 problems in an exam")
	}
	return uc.repo.AddProblemToProblemset(ctx, &ProblemsetProblem{
		ProblemID:    pid,
		ProblemsetID: problemset.ID,
	})
}

// BatchAddProblemToProblemset 预览上传的Excel格式是否正确，以便批量添加题目到题单
func (uc *ProblemsetUsecase) BatchAddProblemToProblemsetPreview(ctx context.Context, problemset *Problemset, fileContent []byte) (*v1.BatchAddProblemToProblemsetPreviewResponse, error) {
	uid, _ := auth.GetUserID(ctx)
	const (
		ColumnID            = "id"
		ColumnType          = "type"
		ColumnContent       = "content"
		ColumnAnswer        = "answer"
		ColumnAnswerDetail  = "answer_detail"
		ColumnScore         = "score"
		ColumnChoice        = "choice"
		ColumnTypeChoice    = "单选"
		ColumnTypeMultiple  = "多选"
		ColumnTypeFillBlank = "填空"
	)
	f, err := excelize.OpenReader(bytes.NewBuffer(fileContent))
	if err != nil {
		return nil, err
	}
	f.SetActiveSheet(0)
	sheet := f.GetSheetName(0)
	rows, err := f.GetRows(sheet)
	if err != nil {
		return &v1.BatchAddProblemToProblemsetPreviewResponse{
			FailedReason: []string{fmt.Sprintf("表样式错误: %s", err.Error())},
		}, nil
	}
	if len(rows) <= 1 {
		return &v1.BatchAddProblemToProblemsetPreviewResponse{
			FailedReason: []string{"没有匹配行"},
		}, nil
	}
	// 处理每行
	columnMap := make(map[string]int)
	for index, column := range rows[0] {
		if strings.Contains(column, "题型") {
			columnMap[ColumnType] = index
		} else if strings.Contains(column, "ID") {
			columnMap[ColumnID] = index
		} else if strings.Contains(column, "题目") {
			columnMap[ColumnContent] = index
		} else if strings.Contains(column, "正确答案") {
			columnMap[ColumnAnswer] = index
		} else if strings.Contains(column, "解析") {
			columnMap[ColumnAnswerDetail] = index
		} else if strings.Contains(column, "分值") || strings.Contains(column, "分数") {
			columnMap[ColumnScore] = index
		} else if strings.Contains(column, "选项") {
			// 利用二进制的方式来储存选项位置
			columnMap[ColumnChoice] = columnMap[ColumnChoice] | (1 << index)
		}
	}
	var (
		problems     []*ProblemsetProblem
		total        int
		failedReason []string
	)
	for index, row := range rows[1:] {
		total++
		// TODO ID 不为空则更新
		choiceIndex := columnMap[ColumnChoice]
		choiceArr := make([]string, 0)
		// 处理选项
		for i := 0; choiceIndex > 0; i++ {
			if choiceIndex&1 == 1 && len(row) > i && row[i] != "" {
				choiceArr = append(choiceArr, row[i])
			}
			choiceIndex >>= 1
		}
		choices, _ := json.Marshal(choiceArr)
		var answer string
		answers := make([]string, 0)
		var problemType int
		if strings.Contains(row[columnMap[ColumnType]], ColumnTypeChoice) {
			problemType = ProblemStatementTypeChoice
			// 单选，答案处理
			choice := row[columnMap[ColumnAnswer]]
			for _, v := range choiceArr {
				if len(v) > 0 && strings.Contains(v, choice) {
					answers = append(answers, v)
					break
				}
			}
			if len(answers) == 0 {
				failedReason = append(failedReason, fmt.Sprintf("第%d行，添加失败，未识别答案", index+2))
				continue
			}
		} else if strings.Contains(row[columnMap[ColumnType]], ColumnTypeMultiple) {
			problemType = ProblemStatementTypeMultiple
			// 多选，答案处理
			for _, choice := range row[columnMap[ColumnAnswer]] {
				for _, v := range choiceArr {
					if len(v) > 0 && string(v[0]) == string(choice) {
						answers = append(answers, v)
					}
				}
			}
			if len(answers) == 0 {
				failedReason = append(failedReason, fmt.Sprintf("第%d行，添加失败，未识别答案", index+2))
				continue
			}
		} else if strings.Contains(row[columnMap[ColumnType]], ColumnTypeFillBlank) {
			problemType = ProblemStatementTypeFillBlank
			// 填空，答案处理
			re := regexp.MustCompile(`\{([^{}]*)\}`)
			matches := re.FindAllStringSubmatch(row[columnMap[ColumnContent]], -1)
			for _, match := range matches {
				answers = append(answers, match[1])
			}
			if len(answers) == 0 {
				if row[columnMap[ColumnAnswer]] == "" {
					failedReason = append(failedReason, fmt.Sprintf("第%d行，添加失败，未识别答案", index+2))
					continue
				}
				answers = append(answers, row[columnMap[ColumnAnswer]])
			}
			choices, _ = json.Marshal(make([]string, len(answers)))
		} else {
			// 其它情况尚未支持
			failedReason = append(failedReason, fmt.Sprintf("第%d行，添加失败，尚未支持的题目类型", index+2))
			continue
		}
		answerJson, _ := json.Marshal(answers)
		answer = string(answerJson)
		// 处理名称，截取前100个字符
		var name string
		for index, char := range row[columnMap[ColumnContent]] {
			if index >= 100 {
				break
			}
			name += string(char)
		}
		if strings.Contains(row[columnMap[ColumnType]], ColumnTypeFillBlank) {
			name = uc.ReplaceObjectiveStatementBrackets(name)
		}
		// 处理解析
		note := ""
		if columnMap[ColumnAnswerDetail] < len(row) {
			note = row[columnMap[ColumnAnswerDetail]]
		}
		// 处理分数
		score := float32(0)
		if columnMap[ColumnScore] < len(row) {
			v, _ := strconv.ParseFloat(row[columnMap[ColumnScore]], 32)
			score = float32(v)
		}
		statement := &ProblemStatement{
			Name:   name,
			Legend: row[columnMap[ColumnContent]],
			Input:  string(choices),
			Output: answer,
			Type:   problemType,
			Note:   note,
			UserID: uid,
		}
		problem := &ProblemsetProblem{
			Type:      ProblemTypeObjective,
			Name:      name,
			Score:     score,
			Statement: statement,
		}
		problems = append(problems, problem)
	}
	res, err := uc.previewBatchAddProblemToProblemset(ctx, problems)
	res.FailedReason = failedReason
	res.Total = int32(total)
	return res, err
}

// previewBatchAddProblemToProblemset 返回预览
func (uc *ProblemsetUsecase) previewBatchAddProblemToProblemset(ctx context.Context, problems []*ProblemsetProblem) (*v1.BatchAddProblemToProblemsetPreviewResponse, error) {
	resp := new(v1.BatchAddProblemToProblemsetPreviewResponse)
	for _, v := range problems {
		statement := &v1.ProblemStatement{
			Name:   v.Statement.Name,
			Legend: v.Statement.Legend,
			Input:  v.Statement.Input,
			Output: v.Statement.Output,
			Type:   v1.ProblemStatementType(v.Statement.Type),
			Note:   v.Statement.Note,
		}
		if statement.Type == v1.ProblemStatementType_MULTIPLE {
			// 匹配 {} 及里面的内容替换为下划线
			statement.Legend = uc.ReplaceObjectiveStatementBrackets(statement.Legend)
		}
		problem := &v1.ProblemsetProblem{
			Name:      v.Name,
			Type:      v1.ProblemType(v.Type),
			Statement: statement,
			Score:     v.Score,
		}
		resp.Problems = append(resp.Problems, problem)
	}
	return resp, nil
}

// BatchAddProblemToProblemset 批量添加题目到题单
func (uc *ProblemsetUsecase) BatchAddProblemToProblemset(ctx context.Context, problemset *Problemset, req *v1.BatchAddProblemToProblemsetRequest) (*v1.BatchAddProblemToProblemsetResponse, error) {
	uid, _ := auth.GetUserID(ctx)
	resp := new(v1.BatchAddProblemToProblemsetResponse)
	// 基于题目ID 去添加
	for _, id := range req.ProblemIds {
		// 题目是否存在
		problem, err := uc.problemRepo.GetProblem(ctx, int(id))
		if err != nil {
			resp.FailedReason = append(resp.FailedReason, fmt.Sprintf("题目%d：不存在该题目", id))
			continue
		}
		// 是否有权限访问题目
		if !problem.HasPermission(ctx, ProblemPermissionUpdate) {
			resp.FailedReason = append(resp.FailedReason, fmt.Sprintf("题目%d：无权限访问该题目", id))
			continue
		}
		// 题目是否通过验证
		if problem.Type != ProblemTypeObjective {
			if res, err := uc.problemRepo.GetProblemVerification(ctx, problem.ID); err != nil || res.VerificationStatus != VerificationStatusSuccess {
				resp.FailedReason = append(resp.FailedReason, fmt.Sprintf("题目%d：该题目未通过验证", id))
				continue
			}
		}
		set, _ := uc.repo.GetProblemset(ctx, problemset.ID)
		if set.Type == ProblemsetTypeExam && set.ProblemCount >= 100 {
			resp.FailedReason = append(resp.FailedReason, fmt.Sprintf("题目%d：达到题单上限100", id))
			continue
		}
		uc.repo.AddProblemToProblemset(ctx, &ProblemsetProblem{
			ProblemID:    problem.ID,
			ProblemsetID: problemset.ID,
		})
	}
	// 客观题批量添加
	for _, v := range req.Problems {
		problem, err := uc.problemRepo.CreateProblem(ctx, &Problem{
			Name:   v.Name,
			UserID: uid,
			Type:   ProblemTypeObjective,
			Status: ProblemStatusPrivate,
		})
		if err != nil {
			continue
		}
		uc.problemRepo.CreateProblemStatement(ctx, &ProblemStatement{
			ProblemID: problem.ID,
			Name:      v.Statement.Name,
			Type:      int(v.Statement.Type),
			Legend:    v.Statement.Legend,
			Input:     v.Statement.Input,
			Output:    v.Statement.Output,
			Note:      v.Statement.Note,
			UserID:    uid,
		})
		uc.repo.AddProblemToProblemset(ctx, &ProblemsetProblem{
			ProblemID:    problem.ID,
			ProblemsetID: problemset.ID,
			Score:        v.Score,
		})
	}
	return resp, nil
}

func (uc *ProblemsetUsecase) DeleteProblemFromProblemset(ctx context.Context, sid int, pid int) error {
	return uc.repo.DeleteProblemFromProblemset(ctx, sid, pid)
}

func (uc *ProblemsetUsecase) SortProblemsetProblems(ctx context.Context, req *v1.SortProblemsetProblemsRequest) error {
	return uc.repo.SortProblemsetProblems(ctx, req)
}

func (uc *ProblemsetUsecase) CreateProblemsetAnswer(ctx context.Context, answer *ProblemsetAnswer) (*ProblemsetAnswer, error) {
	return uc.repo.CreateProblemsetAnswer(ctx, answer)
}

func (uc *ProblemsetUsecase) ListProblemsetAnswers(ctx context.Context, req *v1.ListProblemsetAnswersRequest) ([]*ProblemsetAnswer, int64) {
	return uc.repo.ListProblemsetAnswers(ctx, req)
}

func (uc *ProblemsetUsecase) GetProblemsetAnswer(ctx context.Context, pid int, answerid int) (*ProblemsetAnswer, error) {
	answer, err := uc.repo.GetProblemsetAnswer(ctx, pid, answerid)
	if err != nil {
		return answer, err
	}
	if answer.SubmissionIDs != "" {
		var submissionsIds []int32
		for _, v := range strings.Split(answer.SubmissionIDs, ",") {
			id, _ := strconv.Atoi(v)
			submissionsIds = append(submissionsIds, int32(id))
		}
		answer.Submissions, _ = uc.submissionRepo.ListSubmissions(ctx, &v1.ListSubmissionsRequest{
			Id: submissionsIds,
		})
	}
	return answer, err
}

// UpdateProblemsetAnswer 更新答案
func (uc *ProblemsetUsecase) UpdateProblemsetAnswer(ctx context.Context, id int, answer *ProblemsetAnswer) error {
	// 交卷，阅卷
	if answer.SubmittedAt != nil {
		uc.judgeProblemsetAnswer(ctx, id, answer)
	}
	return uc.repo.UpdateProblemsetAnswer(ctx, id, answer)
}

// judgeProblemsetAnswer 判断答案，阅卷
func (uc *ProblemsetUsecase) judgeProblemsetAnswer(ctx context.Context, id int, answer *ProblemsetAnswer) error {
	_, err := uc.repo.GetProblemset(ctx, id)
	if err != nil {
		return nil
	}
	problems, _ := uc.repo.ListProblemsetProblems(ctx, &v1.ListProblemsetProblemsRequest{
		Id:      int32(id),
		PerPage: 100,
	})
	var ids []int
	for _, p := range problems {
		ids = append(ids, p.ProblemID)
	}
	statementMap := uc.repo.ListProblemsetProblemStatements(ctx, ids)
	for k, v := range problems {
		problems[k].Statement = statementMap[v.ProblemID]
	}
	var answerMap = make(map[string]interface{})
	json.Unmarshal([]byte(answer.Answer), &answerMap)
	var (
		answered      []string
		correct       []string
		wrong         []string
		unanswered    []string
		submissionIds []string
		score         float32 // 总得分
	)
	for _, problem := range problems {
		v, ok := answerMap[fmt.Sprintf("problem-%d", problem.ProblemID)]
		if !ok {
			unanswered = append(unanswered, strconv.Itoa(problem.ProblemID))
			continue
		}
		answered = append(answered, strconv.Itoa(problem.ProblemID))
		// 客观题直接比较答案
		if problem.Type == ProblemTypeObjective {
			userAnswer, _ := json.Marshal(v)
			if isAnswerMatched(string(userAnswer), problem.Statement.Output) {
				score += problem.Score
				correct = append(correct, strconv.Itoa(problem.ProblemID))
			} else {
				wrong = append(wrong, strconv.Itoa(problem.ProblemID))
			}
		} else {
			// 编程题提交测评机运行程序
			userAnswer := v.([]interface{})
			if len(userAnswer) != 2 {
				continue
			}
			submission := &Submission{
				ProblemID:  problem.ProblemID,
				UserID:     answer.UserID,
				EntityType: SubmissionEntityTypeProblemset,
				EntityID:   answer.ProblemsetID,
			}
			submission.Language, _ = strconv.Atoi(userAnswer[0].(string))
			submission.Source = userAnswer[1].(string)
			p, err := uc.problemRepo.GetProblem(ctx, problem.ProblemID)
			if err != nil {
				continue
			}
			p.SubmitCount += 1
			uc.problemRepo.UpdateProblem(ctx, p)
			res, err := uc.submissionRepo.CreateSubmission(ctx, submission)
			if err != nil {
				continue
			}
			uc.sandboxClient.RunSubmission(ctx, &sandboxV1.RunSubmissionRequest{
				SubmissionId: int64(res.ID),
			})
			submissionIds = append(submissionIds, strconv.Itoa(res.ID))
		}
	}
	answer.AnsweredProblemIDs = strings.Join(answered, ",")
	answer.CorrectProblemIDs = strings.Join(correct, ",")
	answer.WrongProblemIDs = strings.Join(wrong, ",")
	answer.UnansweredProblemIDs = strings.Join(unanswered, ",")
	answer.SubmissionIDs = strings.Join(submissionIds, ",")
	answer.Score = score
	return nil
}

// ReplaceObjectiveStatementBrackets 替换题目中的 { } 为下划线，用于填空题
// "{床前明月光}，疑似地上霜" => "_______，疑似地上霜"
// 不在 $$ 内，因为此时会属于 katex 语法
// 不在 ```内，因为此时会展示为 html 代码
func (uc *ProblemsetUsecase) ReplaceObjectiveStatementBrackets(str string) string {
	var result strings.Builder
	var inDollar bool
	var inBracket bool
	var inBacktick bool
	for i := 0; i < len(str); i++ {
		if str[i] == '$' {
			inDollar = !inDollar
		}
		if str[i] == '`' && i+1 < len(str) && str[i+1] == '`' && i+2 < len(str) && str[i+2] == '`' {
			inBacktick = !inBacktick
			result.WriteString("```")
			i += 2
			continue
		}
		if !inDollar && !inBacktick {
			if str[i] == '{' {
				inBracket = true
				result.WriteString("\\_\\_\\_\\_\\_\\_\\_")
			} else if str[i] == '}' {
				inBracket = false
				continue
			}
		}
		if !inBracket {
			result.WriteByte(str[i])
		}
	}
	return result.String()
}
