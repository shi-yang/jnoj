package service

import (
	"context"
	"io"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"
	"strings"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-kratos/kratos/v2/transport/http"
	"google.golang.org/protobuf/types/known/emptypb"
	"google.golang.org/protobuf/types/known/timestamppb"
)

// ProblemService is a problem service.
type ProblemService struct {
	uc           *biz.ProblemUsecase
	problemsetUc *biz.ProblemsetUsecase
	log          *log.Helper
}

// NewProblemService new a problem service.
func NewProblemService(uc *biz.ProblemUsecase,
	problemsetUc *biz.ProblemsetUsecase,
	logger log.Logger) *ProblemService {
	return &ProblemService{
		uc:           uc,
		problemsetUc: problemsetUc,
		log:          log.NewHelper(logger),
	}
}

// ListProblems 题目列表
func (s *ProblemService) ListProblems(ctx context.Context, req *v1.ListProblemsRequest) (*v1.ListProblemsResponse, error) {
	data, count := s.uc.ListProblems(ctx, req)
	resp := new(v1.ListProblemsResponse)
	resp.Data = make([]*v1.Problem, 0)
	resp.Total = count
	for _, v := range data {
		p := &v1.Problem{
			Id:            int32(v.ID),
			Name:          v.Name,
			SubmitCount:   int32(v.SubmitCount),
			AcceptedCount: int32(v.AcceptedCount),
			Status:        int32(v.Status),
			Source:        v.Source,
			Type:          v1.ProblemType(v.Type),
			Tags:          v.Tags,
			AllowDownload: v.AllowDownload,
			CreatedAt:     timestamppb.New(v.CreatedAt),
			UpdatedAt:     timestamppb.New(v.UpdatedAt),
			UserId:        int32(v.UserID),
			Nickname:      v.Nickname,
		}
		for _, s := range v.Statements {
			p.Statements = append(p.Statements, &v1.ProblemStatement{
				Name:     s.Name,
				Language: s.Language,
			})
		}
		resp.Data = append(resp.Data, p)
	}
	return resp, nil
}

// GetProblem 题目详情
func (s *ProblemService) GetProblem(ctx context.Context, req *v1.GetProblemRequest) (*v1.Problem, error) {
	data, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if !data.HasPermission(ctx, biz.ProblemPermissionView) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	resp := &v1.Problem{
		Id:            int32(data.ID),
		Name:          data.Name,
		Type:          v1.ProblemType(data.Type),
		Status:        int32(data.Status),
		MemoryLimit:   int32(data.MemoryLimit),
		TimeLimit:     int32(data.TimeLimit),
		SubmitCount:   int32(data.SubmitCount),
		AcceptedCount: int32(data.AcceptedCount),
		CheckerId:     int32(data.CheckerID),
		Source:        data.Source,
		Tags:          data.Tags,
	}
	resp.Statements = make([]*v1.ProblemStatement, 0)
	resp.SampleTests = make([]*v1.Problem_SampleTest, 0)
	for _, v := range data.Statements {
		resp.Statements = append(resp.Statements, &v1.ProblemStatement{
			Id:       int32(v.ID),
			Name:     v.Name,
			Input:    v.Input,
			Output:   v.Output,
			Note:     v.Note,
			Legend:   v.Legend,
			Language: v.Language,
			Type:     v1.ProblemStatementType(v.Type),
		})
	}
	for _, v := range data.SampleTests {
		resp.SampleTests = append(resp.SampleTests, &v1.Problem_SampleTest{
			Input:  v.Input,
			Output: v.Output,
		})
	}
	return resp, nil
}

// CreateProblem 创建题目
func (s *ProblemService) CreateProblem(ctx context.Context, req *v1.CreateProblemRequest) (*v1.CreateProblemResponse, error) {
	_, role := auth.GetUserID(ctx)
	// 创建题目的权限
	if !biz.CheckAccess(role, biz.ResourceProblem) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	data, err := s.uc.CreateProblem(ctx, &biz.Problem{
		Name: req.Name,
		Type: int(req.Type),
	})
	if err != nil {
		return nil, err
	}
	return &v1.CreateProblemResponse{
		Id: int32(data.ID),
	}, nil
}

// UpdateProblem 创建题目
func (s *ProblemService) UpdateProblem(ctx context.Context, req *v1.UpdateProblemRequest) (*v1.Problem, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	// 过滤首尾空格
	if len(req.Tags) != 0 {
		for k, v := range req.Tags {
			req.Tags[k] = strings.TrimSpace(v)
		}
	}
	_, err = s.uc.UpdateProblem(ctx, &biz.Problem{
		ID:          int(req.Id),
		TimeLimit:   req.TimeLimit,
		MemoryLimit: req.MemoryLimit,
		Status:      int(req.Status),
		Source:      req.Source,
		Tags:        req.Tags,
	})
	return nil, err
}

// ListProblemStatements 获取题目描述列表
func (s *ProblemService) ListProblemStatements(ctx context.Context, req *v1.ListProblemStatementsRequest) (*v1.ListProblemStatementsResponse, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, count := s.uc.ListProblemStatements(ctx, req)
	resp := new(v1.ListProblemStatementsResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ProblemStatement{
			Id:       int32(v.ID),
			Name:     v.Name,
			Input:    v.Input,
			Output:   v.Output,
			Note:     v.Note,
			Legend:   v.Legend,
			Language: v.Language,
			Type:     v1.ProblemStatementType(v.Type),
		})
	}
	return resp, nil
}

// GetProblemStatement 获取题目描述详情
func (s *ProblemService) GetProblemStatement(ctx context.Context, req *v1.GetProblemStatementRequest) (*v1.ProblemStatement, error) {
	return nil, nil
}

// CreateProblemStatement 创建题目描述
func (s *ProblemService) CreateProblemStatement(ctx context.Context, req *v1.CreateProblemStatementRequest) (*v1.ProblemStatement, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, err := s.uc.CreateProblemStatement(ctx, &biz.ProblemStatement{
		ProblemID: int(req.Id),
		Language:  req.Language,
	})
	if err != nil {
		return nil, err
	}
	return &v1.ProblemStatement{
		Id: int32(res.ID),
	}, nil
}

// UpdateProblemStatement 更新题目描述
func (s *ProblemService) UpdateProblemStatement(ctx context.Context, req *v1.UpdateProblemStatementRequest) (*v1.ProblemStatement, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, err := s.uc.UpdateProblemStatement(ctx, &biz.ProblemStatement{
		ID:        int(req.Sid),
		ProblemID: int(req.Id),
		Name:      req.Name,
		Input:     req.Input,
		Output:    req.Output,
		Legend:    req.Legend,
		Note:      req.Note,
		Type:      int(req.Type),
	})
	if err != nil {
		return nil, err
	}
	return &v1.ProblemStatement{
		Id: int32(res.ID),
	}, nil
}

// DeleteProblemStatement 删除题目描述
func (s *ProblemService) DeleteProblemStatement(ctx context.Context, req *v1.DeleteProblemStatementRequest) (*emptypb.Empty, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.uc.DeleteProblemStatement(ctx, int(req.Sid))
	if err != nil {
		return nil, err
	}
	return &emptypb.Empty{}, nil
}

// ListProblemTests 获取题目测试点列表
func (s *ProblemService) ListProblemTests(ctx context.Context, req *v1.ListProblemTestsRequest) (*v1.ListProblemTestsResponse, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	data, count, isSampleFirst := s.uc.ListProblemTests(ctx, req)
	resp := new(v1.ListProblemTestsResponse)
	resp.Total = count
	resp.IsSampleFirst = isSampleFirst
	for _, v := range data {
		resp.Data = append(resp.Data, &v1.ProblemTest{
			Id:            int32(v.ID),
			Order:         int32(v.Order),
			Name:          v.Name,
			InputPreview:  v.InputPreview,
			InputSize:     v.InputSize,
			OutputPreview: v.OutputPreview,
			OutputSize:    v.OutputSize,
			Remark:        v.Remark,
			IsExample:     v.IsExample,
			CreatedAt:     timestamppb.New(v.CreatedAt),
			UpdatedAt:     timestamppb.New(v.UpdatedAt),
		})
	}
	return resp, nil
}

// GetProblemTest 获取题目测试点详情
func (s *ProblemService) GetProblemTest(ctx context.Context, req *v1.GetProblemTestRequest) (*v1.ProblemTest, error) {
	return nil, nil
}

// CreateProblemTest 创建题目测试点
func (s *ProblemService) CreateProblemTest(ctx context.Context, req *v1.CreateProblemTestRequest) (*v1.ProblemTest, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, err := s.uc.CreateProblemTest(ctx, &biz.ProblemTest{
		ProblemID:        int(req.Id),
		InputFileContent: req.FileContent,
		Name:             req.Filename,
	})
	if err != nil {
		return nil, err
	}
	return &v1.ProblemTest{Id: int32(res.ID)}, nil
}

// UploadProblemTest 上传题目测试点
func (s *ProblemService) UploadProblemTest(ctx http.Context) error {
	var in v1.CreateProblemTestRequest
	file, fileheader, err := ctx.Request().FormFile("file")
	if err != nil {
		return err
	}
	fileContent, err := io.ReadAll(file)
	if err != nil {
		return err
	}
	defer file.Close()
	if err := ctx.BindVars(&in); err != nil {
		return err
	}
	in.FileContent = fileContent
	in.Filename = fileheader.Filename
	http.SetOperation(ctx, v1.OperationProblemServiceCreateProblemTest)
	h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
		return s.CreateProblemTest(ctx, req.(*v1.CreateProblemTestRequest))
	})
	out, err := h(ctx, &in)
	if err != nil {
		return err
	}
	reply := out.(*v1.ProblemTest)
	return ctx.Result(200, reply)
}

// UpdateProblemTest 更新题目测试点
func (s *ProblemService) UpdateProblemTest(ctx context.Context, req *v1.UpdateProblemTestRequest) (*v1.ProblemTest, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	test, err := s.uc.GetProblemTest(ctx, int(req.Tid))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if test.InputSize > 1024 || test.OutputSize > 1024 {
		return nil, v1.ErrorProblemTestSampleNotAllowed("Size limit 1024")
	}

	s.uc.UpdateProblemTest(ctx, &biz.ProblemTest{
		ID:        int(req.Tid),
		ProblemID: int(req.Id),
		Remark:    req.Remark,
		IsExample: req.IsExample,
	})
	return nil, nil
}

// DeleteProblemTest 删除题目测试点
func (s *ProblemService) DeleteProblemTest(ctx context.Context, req *v1.DeleteProblemTestRequest) (*emptypb.Empty, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.uc.DeleteProblemTest(ctx, int64(req.Id), int(req.Tid))
	return &emptypb.Empty{}, err
}

// SortProblemTests 对题目测试点进行排序
func (s *ProblemService) SortProblemTests(ctx context.Context, req *v1.SortProblemTestsRequest) (*emptypb.Empty, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	s.uc.SortProblemTests(ctx, req)
	return &emptypb.Empty{}, nil
}

// ListProblemFiles 获取题目文件列表
func (s *ProblemService) ListProblemFiles(ctx context.Context, req *v1.ListProblemFilesRequest) (*v1.ListProblemFilesResponse, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, count := s.uc.ListProblemFiles(ctx, req)
	resp := new(v1.ListProblemFilesResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ProblemFile{
			Id:        int32(v.ID),
			Name:      v.Name,
			Language:  int32(v.Language),
			Type:      v.Type,
			FileType:  v.FileType,
			FileSize:  v.FileSize,
			Content:   v.Content,
			CreatedAt: timestamppb.New(v.CreatedAt),
			UpdatedAt: timestamppb.New(v.UpdatedAt),
		})
	}
	return resp, nil
}

// GetProblemFile 获取题目文件详情
func (s *ProblemService) GetProblemFile(ctx context.Context, req *v1.GetProblemFileRequest) (*v1.ProblemFile, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, err := s.uc.GetProblemFile(ctx, int(req.Sid))
	if err != nil {
		return nil, err
	}
	return &v1.ProblemFile{
		Id:        int32(res.ID),
		Name:      res.Name,
		Content:   res.Content,
		Type:      res.Type,
		UserId:    int32(res.UserID),
		CreatedAt: timestamppb.New(res.CreatedAt),
		UpdatedAt: timestamppb.New(res.UpdatedAt),
	}, nil
}

// CreateProblemFile 创建题目文件
func (s *ProblemService) CreateProblemFile(ctx context.Context, req *v1.CreateProblemFileRequest) (*v1.ProblemFile, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	f := &biz.ProblemFile{
		ProblemID: int(req.Id),
		Content:   req.Content,
		Name:      req.Name,
		Language:  int(req.Language),
		Type:      req.Type,
		FileType:  req.FileType,
		FileSize:  int64(len(req.Content)),
	}
	if req.Filename != "" {
		f.Name = req.Filename
		f.FileContent = req.FileContent
		f.FileSize = int64(len(req.FileContent))
	}
	res, err := s.uc.CreateProblemFile(ctx, f)
	if err != nil {
		return nil, err
	}
	return &v1.ProblemFile{
		Id:      int32(res.ID),
		Content: res.Content,
	}, nil
}

// UploadProblemFile 上传题目文件
func (s *ProblemService) UploadProblemFile(ctx http.Context) error {
	var in v1.CreateProblemFileRequest
	file, fileheader, err := ctx.Request().FormFile("file")
	if err != nil {
		return err
	}
	fileContent, err := io.ReadAll(file)
	if err != nil {
		return err
	}
	defer file.Close()
	if err := ctx.BindVars(&in); err != nil {
		return err
	}
	in.FileContent = fileContent
	in.Filename = fileheader.Filename
	in.FileType = ctx.Request().PostForm.Get("fileType")
	http.SetOperation(ctx, v1.OperationProblemServiceCreateProblemFile)
	h := ctx.Middleware(func(ctx context.Context, req interface{}) (interface{}, error) {
		return s.CreateProblemFile(ctx, req.(*v1.CreateProblemFileRequest))
	})
	out, err := h(ctx, &in)
	if err != nil {
		return err
	}
	reply := out.(*v1.ProblemFile)
	return ctx.Result(200, reply)
}

// UpdateProblemFile 更新题目文件
func (s *ProblemService) UpdateProblemFile(ctx context.Context, req *v1.UpdateProblemFileRequest) (*v1.ProblemFile, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	pf, err := s.uc.GetProblemFile(ctx, int(req.Sid))
	if err != nil || pf.ProblemID != p.ID {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	_, err = s.uc.UpdateProblemFile(ctx, &biz.ProblemFile{
		ID:       int(req.Sid),
		Name:     req.Name,
		Content:  req.Content,
		Type:     req.Type,
		FileType: pf.FileType,
	})
	return &v1.ProblemFile{
		Id: int32(pf.ID),
	}, err
}

// DeleteProblemFile 删除题目文件
func (s *ProblemService) DeleteProblemFile(ctx context.Context, req *v1.DeleteProblemFileRequest) (*emptypb.Empty, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	s.uc.DeleteProblemFile(ctx, int(req.Sid))
	return &emptypb.Empty{}, nil
}

// ListProblemLanguages 获取题目语言列表
func (s *ProblemService) ListProblemLanguages(ctx context.Context, req *v1.ListProblemLanguagesRequest) (*v1.ListProblemLanguagesResponse, error) {
	p, err := s.uc.GetProblem(ctx, int(req.ProblemId))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	res, count := s.uc.ListProblemLanguages(ctx, p)
	resp := new(v1.ListProblemLanguagesResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ProblemLanguage{
			Id:           v.Id,
			LanguageCode: v.LanguageCode,
			LanguageName: v.LanguageName,
		})
	}
	return resp, nil
}

// GetProblemLanguage 获取题目语言详情
func (s *ProblemService) GetProblemLanguage(ctx context.Context, req *v1.GetProblemLanguageRequest) (*v1.ProblemLanguage, error) {
	p, err := s.uc.GetProblem(ctx, int(req.ProblemId))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	res, err := s.uc.GetProblemLanguage(ctx, int(req.ProblemId), int(req.Id))
	if err != nil {
		return nil, err
	}
	if !p.HasPermission(ctx, biz.ProblemPermissionUpdate) {
		res.MainContent = ""
	}
	return res, nil
}

// CreateProblemLanguage 创建题目语言
func (s *ProblemService) CreateProblemLanguage(ctx context.Context, req *v1.CreateProblemLanguageRequest) (*v1.ProblemLanguage, error) {
	p, err := s.uc.GetProblem(ctx, int(req.ProblemId))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	l := &biz.ProblemLanguage{
		UserContent: req.UserContent,
		MainContent: req.MainContent,
	}
	_, err = s.uc.CreateProblemLanguage(ctx, p.ID, int(req.Language), l)
	if err != nil {
		return nil, err
	}
	return &v1.ProblemLanguage{}, nil
}

// UpdateProblemLanguage 更新题目语言
func (s *ProblemService) UpdateProblemLanguage(ctx context.Context, req *v1.UpdateProblemLanguageRequest) (*v1.ProblemLanguage, error) {
	p, err := s.uc.GetProblem(ctx, int(req.ProblemId))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	_, err = s.uc.UpdateProblemLanguage(ctx, p.ID, int(req.Id), &biz.ProblemLanguage{
		UserContent: req.UserContent,
		MainContent: req.MainContent,
	})
	if err != nil {
		return nil, err
	}
	return &v1.ProblemLanguage{}, err
}

// DeleteProblemLanguage 删除题目语言
func (s *ProblemService) DeleteProblemLanguage(ctx context.Context, req *v1.DeleteProblemLanguageRequest) (*emptypb.Empty, error) {
	p, err := s.uc.GetProblem(ctx, int(req.ProblemId))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	s.uc.DeleteProblemLanguage(ctx, int(req.ProblemId), int(req.Id))
	return &emptypb.Empty{}, nil
}

// RunProblemFile 运行文件
func (s *ProblemService) RunProblemFile(ctx context.Context, req *v1.RunProblemFileRequest) (*emptypb.Empty, error) {
	err := s.uc.RunProblemFile(ctx, int(req.Sid))
	return &emptypb.Empty{}, err
}

// ListProblemStdCheckers 获取题目文件列表
func (s *ProblemService) ListProblemStdCheckers(ctx context.Context, req *v1.ListProblemStdCheckersRequest) (*v1.ListProblemStdCheckersResponse, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, _ := s.uc.ListProblemFiles(ctx, &v1.ListProblemFilesRequest{
		FileType: "checker",
	})
	resp := new(v1.ListProblemStdCheckersResponse)
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ProblemFile{
			Id:        int32(v.ID),
			Name:      v.Name,
			Type:      v.Type,
			CreatedAt: timestamppb.New(v.CreatedAt),
			UpdatedAt: timestamppb.New(v.UpdatedAt),
		})
	}
	return resp, nil
}

// VerifyProblem 验证题目完整性
func (s *ProblemService) VerifyProblem(ctx context.Context, req *v1.VerifyProblemRequest) (*emptypb.Empty, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if !p.HasPermission(ctx, biz.ProblemPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.uc.VerifyProblem(ctx, int(req.Id))
	return &emptypb.Empty{}, err
}

// PackProblem 打包题目
func (s *ProblemService) PackProblem(ctx context.Context, req *v1.PackProblemRequest) (*emptypb.Empty, error) {
	problem, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if !problem.HasPermission(ctx, biz.ProblemPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	// 题目是否通过验证
	if res, err := s.uc.GetProblemVerification(ctx, problem.ID); err != nil || res.VerificationStatus != biz.VerificationStatusSuccess {
		return nil, v1.ErrorProblemNotVerification("题目未通过验证")
	}
	err = s.uc.PackProblem(ctx, problem.ID)
	return &emptypb.Empty{}, err
}

func (s *ProblemService) UpdateProblemChecker(ctx context.Context, req *v1.UpdateProblemCheckerRequest) (*emptypb.Empty, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.uc.UpdateProblemChecker(ctx, int(req.Id), int(req.CheckerId))
	return &emptypb.Empty{}, err
}

func (s *ProblemService) GetProblemVerification(ctx context.Context, req *v1.GetProblemVerificationRequest) (*v1.ProblemVerification, error) {
	p, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if ok := p.HasPermission(ctx, biz.ProblemPermissionUpdate); !ok {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	resp := new(v1.ProblemVerification)
	res, err := s.uc.GetProblemVerification(ctx, int(req.Id))
	if err == nil {
		resp.Id = int32(res.ID)
		resp.VerificationStatus = int32(res.VerificationStatus)
		resp.ProblemId = int32(res.ProblemID)
		resp.VerificaitonInfo = make([]*v1.ProblemVerification_VerificaitionInfo, 0)
		for _, v := range res.VerificationInfo {
			resp.VerificaitonInfo = append(resp.VerificaitonInfo, &v1.ProblemVerification_VerificaitionInfo{
				Action:       v.Action,
				ErrorMessage: v.ErrorMessage,
			})
		}
	}
	return resp, nil
}

// ListProblemsets 题单列表
func (s *ProblemService) ListProblemsets(ctx context.Context, req *v1.ListProblemsetsRequest) (*v1.ListProblemsetsResponse, error) {
	res, count := s.problemsetUc.ListProblemsets(ctx, req)
	resp := new(v1.ListProblemsetsResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.Problemset{
			Id:           int32(v.ID),
			Name:         v.Name,
			Description:  v.Description,
			ProblemCount: int32(v.ProblemCount),
			UserId:       int32(v.UserID),
			CreatedAt:    timestamppb.New(v.CreatedAt),
		})
	}
	return resp, nil
}

// GetProblemset 获取题单
func (s *ProblemService) GetProblemset(ctx context.Context, req *v1.GetProblemsetRequest) (*v1.Problemset, error) {
	res, err := s.problemsetUc.GetProblemset(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	return &v1.Problemset{
		Id:           int32(res.ID),
		Name:         res.Name,
		Description:  res.Description,
		ProblemCount: int32(res.ProblemCount),
		UserId:       int32(res.UserID),
		CreatedAt:    timestamppb.New(res.CreatedAt),
	}, nil
}

// CreateProblemset 创建题单
func (s *ProblemService) CreateProblemset(ctx context.Context, req *v1.CreateProblemsetRequest) (*v1.Problemset, error) {
	uid, _ := auth.GetUserID(ctx)
	res, err := s.problemsetUc.CreateProblemset(ctx, &biz.Problemset{
		Name:        req.Name,
		UserID:      uid,
		Description: req.Description,
	})
	if err != nil {
		return nil, err
	}
	return &v1.Problemset{
		Id: int32(res.ID),
	}, nil
}

// DeleteProblemset 删除题单
func (s *ProblemService) DeleteProblemset(ctx context.Context, req *v1.DeleteProblemsetRequest) (*emptypb.Empty, error) {
	set, err := s.problemsetUc.GetProblemset(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if !set.HasPermission(ctx) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.problemsetUc.DeleteProblemset(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	return &emptypb.Empty{}, nil
}

// UpdateProblemset 修改题单
func (s *ProblemService) UpdateProblemset(ctx context.Context, req *v1.UpdateProblemsetRequest) (*v1.Problemset, error) {
	set, err := s.problemsetUc.GetProblemset(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	if !set.HasPermission(ctx) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	res, err := s.problemsetUc.UpdateProblemset(ctx, &biz.Problemset{
		ID:          int(req.Id),
		Name:        req.Name,
		Description: req.Description,
	})
	if err != nil {
		return nil, err
	}
	return &v1.Problemset{
		Id: int32(res.ID),
	}, nil
}

// ListProblemsetProblems 获取题单的题目
func (s *ProblemService) ListProblemsetProblems(ctx context.Context, req *v1.ListProblemsetProblemsRequest) (*v1.ListProblemsetProblemsResponse, error) {
	res, count := s.problemsetUc.ListProblemsetProblems(ctx, req)
	resp := new(v1.ListProblemsetProblemsResponse)
	resp.Total = count
	for _, v := range res {
		p := &v1.ProblemsetProblem{
			Id:            int32(v.ID),
			Name:          v.Name,
			Order:         int32(v.Order),
			SubmitCount:   int32(v.SubmitCount),
			AcceptedCount: int32(v.AcceptedCount),
			ProblemsetId:  req.Id,
			ProblemId:     int32(v.ProblemID),
			Source:        v.Source,
			Tags:          v.Tags,
			Status:        v1.ProblemsetProblem_Status(v.Status),
		}
		resp.Data = append(resp.Data, p)
	}
	return resp, nil
}

// GetProblemsetProblem 获取题单的题目
func (s *ProblemService) GetProblemsetProblem(ctx context.Context, req *v1.GetProblemsetProblemRequest) (*v1.Problem, error) {
	problemsetProblem, err := s.problemsetUc.GetProblemsetProblem(ctx, int(req.Id), int(req.Pid))
	if err != nil {
		return nil, err
	}
	data, err := s.uc.GetProblem(ctx, problemsetProblem.ProblemID)
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	resp := &v1.Problem{
		Id:            int32(data.ID),
		Name:          data.Name,
		Type:          v1.ProblemType(data.Type),
		Status:        int32(data.Status),
		MemoryLimit:   int32(data.MemoryLimit),
		TimeLimit:     int32(data.TimeLimit),
		SubmitCount:   int32(data.SubmitCount),
		AcceptedCount: int32(data.AcceptedCount),
		CheckerId:     int32(data.CheckerID),
		Source:        data.Source,
	}
	resp.Statements = make([]*v1.ProblemStatement, 0)
	resp.SampleTests = make([]*v1.Problem_SampleTest, 0)
	for _, v := range data.Statements {
		resp.Statements = append(resp.Statements, &v1.ProblemStatement{
			Id:       int32(v.ID),
			Name:     v.Name,
			Input:    v.Input,
			Output:   v.Output,
			Note:     v.Note,
			Legend:   v.Legend,
			Language: v.Language,
			Type:     v1.ProblemStatementType(v.Type),
		})
	}
	for _, v := range data.SampleTests {
		resp.SampleTests = append(resp.SampleTests, &v1.Problem_SampleTest{
			Input:  v.Input,
			Output: v.Output,
		})
	}
	return resp, nil
}

// GetProblemsetProblem 获取题单的题目
func (s *ProblemService) GetProblemsetLateralProblem(ctx context.Context, req *v1.GetProblemsetLateralProblemRequest) (*v1.GetProblemsetLateralProblemResponse, error) {
	previous, next := s.problemsetUc.GetProblemsetLateralProblem(ctx, int(req.Id), int(req.Pid))
	return &v1.GetProblemsetLateralProblemResponse{
		Previous: int32(previous),
		Next:     int32(next),
	}, nil
}

// CreateProblemset 添加题目到题单
func (s *ProblemService) AddProblemToProblemset(ctx context.Context, req *v1.AddProblemToProblemsetRequest) (*emptypb.Empty, error) {
	// 题单是否存在
	set, err := s.problemsetUc.GetProblemset(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	// 是否有权限访问题单
	if !set.HasPermission(ctx) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	// 题目是否存在
	problem, err := s.uc.GetProblem(ctx, int(req.ProblemId))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	// 是否有权限访问题目
	if !problem.HasPermission(ctx, biz.ProblemPermissionUpdate) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	// 题目是否通过验证
	if res, err := s.uc.GetProblemVerification(ctx, problem.ID); err != nil || res.VerificationStatus != biz.VerificationStatusSuccess {
		return nil, v1.ErrorProblemNotVerification("题目未通过验证")
	}
	err = s.problemsetUc.AddProblemToProblemset(ctx, int(req.Id), int(req.ProblemId))
	return &emptypb.Empty{}, err
}

// CreateProblemset 从题单中删除题目
func (s *ProblemService) DeleteProblemFromProblemset(ctx context.Context, req *v1.DeleteProblemFromProblemsetRequest) (*emptypb.Empty, error) {
	// 题单是否存在
	set, err := s.problemsetUc.GetProblemset(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	// 是否有权限访问题单
	if !set.HasPermission(ctx) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.problemsetUc.DeleteProblemFromProblemset(ctx, int(req.Id), int(req.ProblemId))
	return &emptypb.Empty{}, err
}

// SortProblemsetProblems 对题单的题目进行排序
func (s *ProblemService) SortProblemsetProblems(ctx context.Context, req *v1.SortProblemsetProblemsRequest) (*emptypb.Empty, error) {
	// 题单是否存在
	set, err := s.problemsetUc.GetProblemset(ctx, int(req.Id))
	if err != nil {
		return nil, v1.ErrorProblemNotFound(err.Error())
	}
	// 是否有权限访问题单
	if !set.HasPermission(ctx) {
		return nil, v1.ErrorPermissionDenied("permission denied")
	}
	err = s.problemsetUc.SortProblemsetProblems(ctx, req)
	return &emptypb.Empty{}, err
}

// DownloadProblems 下载题目
func (s *ProblemService) DownloadProblems(ctx context.Context, req *v1.DownloadProblemsRequest) (*v1.DownloadProblemsResponse, error) {
	url, err := s.uc.DownloadProblems(ctx, req.Ids)
	if err != nil {
		return nil, err
	}
	if url == "" {
		return nil, v1.ErrorProblemPackageNotFound("题目尚未打包，暂不支持下载")
	}
	return &v1.DownloadProblemsResponse{
		Url: url,
	}, nil
}
