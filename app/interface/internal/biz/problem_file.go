package biz

import (
	"context"
	"encoding/json"
	v1 "jnoj/api/interface/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/internal/middleware/auth"
	"jnoj/pkg/sandbox"
	"time"
)

// ProblemFile is a ProblemFile model.
// 题目对应的各种文件
type ProblemFile struct {
	ID          int
	Name        string
	Language    int // 语言
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

// ProblemLanguage 语言文件
type ProblemLanguage struct {
	UserContent string
	MainContent string
}

// ProblemFileRepo is a ProblemFile repo.
type ProblemFileRepo interface {
	ListProblemFiles(context.Context, *v1.ListProblemFilesRequest) ([]*ProblemFile, int64)
	GetProblemFile(context.Context, *ProblemFile) (*ProblemFile, error)
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
	return uc.repo.GetProblemFile(ctx, &ProblemFile{ID: id})
}

// CreateProblemFile creates a ProblemFile, and returns the new ProblemFile.
func (uc *ProblemUsecase) CreateProblemFile(ctx context.Context, p *ProblemFile) (*ProblemFile, error) {
	p.UserID, _ = auth.GetUserID(ctx)
	if p.FileType == string(ProblemFileFileTypeSubtask) {
		if _, err := uc.GetProblemSubtaskContent(p.Content); err != nil {
			return nil, v1.ErrorBadRequest(err.Error())
		}
	}
	return uc.repo.CreateProblemFile(ctx, p)
}

// UpdateProblemFile update a ProblemFile
func (uc *ProblemUsecase) UpdateProblemFile(ctx context.Context, p *ProblemFile) (*ProblemFile, error) {
	if p.FileType == string(ProblemFileFileTypeSubtask) {
		if _, err := uc.GetProblemSubtaskContent(p.Content); err != nil {
			return nil, v1.ErrorBadRequest(err.Error())
		}
	}
	return uc.repo.UpdateProblemFile(ctx, p)
}

// DeleteProblemFile delete a ProblemFile
func (uc *ProblemUsecase) DeleteProblemFile(ctx context.Context, id int) error {
	return uc.repo.DeleteProblemFile(ctx, id)
}

// RunProblemFile .
func (uc *ProblemUsecase) RunProblemFile(ctx context.Context, id int) error {
	uid, _ := auth.GetUserID(ctx)
	file, _ := uc.repo.GetProblemFile(ctx, &ProblemFile{ID: id})
	problem, err := uc.repo.GetProblem(ctx, file.ProblemID)
	if err != nil {
		return err
	}
	// create a submission
	submission, _ := uc.submissionRepo.CreateSubmission(ctx, &Submission{
		ProblemID:  problem.ID,
		Source:     file.Content,
		UserID:     uid,
		Language:   file.Language,
		EntityID:   file.ID,
		EntityType: SubmissionEntityTypeProblemFile,
	})

	uc.sandboxClient.RunSubmission(ctx, &sandboxV1.RunSubmissionRequest{
		SubmissionId: int64(submission.ID),
	})
	return nil
}

// ListProblemLanguages list ProblemLanguage
func (uc *ProblemUsecase) ListProblemLanguages(ctx context.Context, p *Problem) ([]*v1.ProblemLanguage, int64) {
	var res []*v1.ProblemLanguage
	var files []*ProblemFile
	var count int64
	// 常规题目
	if p.Type == ProblemTypeDefault {
		// 常规题目返回所有可支持的语言列表
		for _, v := range sandbox.Languages {
			res = append(res, &v1.ProblemLanguage{
				LanguageCode: int32(v.Code),
				LanguageName: v.Name,
			})
		}
		count = int64(len(res))
	} else {
		// 函数题，返回可支持的语言列表
		files, count = uc.repo.ListProblemFiles(ctx, &v1.ListProblemFilesRequest{
			Id:       int32(p.ID),
			FileType: string(ProblemFileFileTypeLanguage),
			Page:     1,
			PerPage:  200,
		})
		for _, v := range files {
			res = append(res, &v1.ProblemLanguage{
				Id:           int32(v.ID),
				LanguageCode: int32(v.Language),
				LanguageName: sandbox.LanguageText(v.Language),
			})
		}
	}
	return res, count
}

// GetProblemLanguage get a ProblemLanguage
func (uc *ProblemUsecase) GetProblemLanguage(ctx context.Context, problemId, id int) (*v1.ProblemLanguage, error) {
	file, err := uc.repo.GetProblemFile(ctx, &ProblemFile{
		ID:        id,
		ProblemID: problemId,
	})
	if err != nil {
		return nil, err
	}
	var lang ProblemLanguage
	_ = json.Unmarshal([]byte(file.Content), &lang)
	res := &v1.ProblemLanguage{
		Id:           int32(file.ID),
		LanguageCode: int32(file.Language),
		LanguageName: sandbox.LanguageText(file.Language),
		UserContent:  lang.UserContent,
		MainContent:  lang.MainContent,
	}
	return res, nil
}

// CreateProblemLanguage creates a ProblemLanguage, and returns the new ProblemLanguage.
func (uc *ProblemUsecase) CreateProblemLanguage(
	ctx context.Context,
	problmeId, languageCode int,
	p *ProblemLanguage,
) (*ProblemLanguage, error) {
	uid, _ := auth.GetUserID(ctx)
	content, _ := json.Marshal(&p)
	file := ProblemFile{
		Name:      sandbox.LanguageText(languageCode),
		ProblemID: problmeId,
		FileType:  string(ProblemFileFileTypeLanguage),
		Language:  languageCode,
		Content:   string(content),
		FileSize:  int64(len(content)),
		UserID:    uid,
	}
	_, err := uc.repo.CreateProblemFile(ctx, &file)
	return nil, err
}

// UpdateProblemLanguage update a ProblemLanguage
func (uc *ProblemUsecase) UpdateProblemLanguage(ctx context.Context, problemId, id int, p *ProblemLanguage) (*ProblemLanguage, error) {
	file, err := uc.repo.GetProblemFile(ctx, &ProblemFile{
		ID:        id,
		ProblemID: problemId,
	})
	content, _ := json.Marshal(&p)
	if err != nil {
		return nil, err
	}
	file.Content = string(content)
	_, err = uc.repo.UpdateProblemFile(ctx, file)
	return nil, err
}

// DeleteProblemLanguage delete a ProblemLanguage
func (uc *ProblemUsecase) DeleteProblemLanguage(ctx context.Context, problmeId, id int) error {
	return uc.repo.DeleteProblemFile(ctx, id)
}
