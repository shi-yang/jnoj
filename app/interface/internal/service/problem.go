package service

import (
	"context"
	"io"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"github.com/go-kratos/kratos/v2/transport/http"
	"google.golang.org/protobuf/types/known/emptypb"
	"google.golang.org/protobuf/types/known/timestamppb"
)

// ProblemService is a problem service.
type ProblemService struct {
	uc *biz.ProblemUsecase
}

// NewProblemService new a problem service.
func NewProblemService(uc *biz.ProblemUsecase) *ProblemService {
	return &ProblemService{uc: uc}
}

// 题目列表
func (s *ProblemService) ListProblems(ctx context.Context, req *v1.ListProblemsRequest) (*v1.ListProblemsResponse, error) {
	data, count := s.uc.ListProblems(ctx, req)
	resp := new(v1.ListProblemsResponse)
	resp.Data = make([]*v1.Problem, 0)
	resp.Count = count
	for _, v := range data {
		resp.Data = append(resp.Data, &v1.Problem{
			Id:            int32(v.ID),
			Name:          v.Name,
			SubmitCount:   int32(v.SubmitCount),
			AcceptedCount: int32(v.AcceptedCount),
			CreatedAt:     timestamppb.New(v.CreatedAt),
			UpdatedAt:     timestamppb.New(v.UpdatedAt),
		})
	}
	return resp, nil
}

// 题目详情
func (s *ProblemService) GetProblem(ctx context.Context, req *v1.GetProblemRequest) (*v1.Problem, error) {
	data, err := s.uc.GetProblem(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	resp := &v1.Problem{
		Id:            int32(data.ID),
		Name:          data.Name,
		MemoryLimit:   int32(data.MemoryLimit),
		TimeLimit:     int32(data.TimeLimit),
		SubmitCount:   int32(data.SubmitCount),
		AcceptedCount: int32(data.AcceptedCount),
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

// 创建题目
func (s *ProblemService) CreateProblem(ctx context.Context, req *v1.CreateProblemRequest) (*v1.CreateProblemResponse, error) {
	data, err := s.uc.CreateProblem(ctx, &biz.Problem{
		Name: req.Name,
	})
	if err != nil {
		return nil, err
	}
	return &v1.CreateProblemResponse{
		Id: int32(data.ID),
	}, nil
}

// 创建题目
func (s *ProblemService) UpdateProblem(ctx context.Context, req *v1.UpdateProblemRequest) (*v1.Problem, error) {
	_, err := s.uc.UpdateProblem(ctx, &biz.Problem{
		ID:          int(req.Id),
		TimeLimit:   req.TimeLimit,
		MemoryLimit: req.MemoryLimit,
	})
	return nil, err
}

// 获取题目描述列表
func (s *ProblemService) ListProblemStatements(ctx context.Context, req *v1.ListProblemStatementsRequest) (*v1.ListProblemStatementsResponse, error) {
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
		})
	}
	return resp, nil
}

// 获取题目描述详情
func (s *ProblemService) GetProblemStatement(ctx context.Context, req *v1.GetProblemStatementRequest) (*v1.ProblemStatement, error) {
	return nil, nil
}

// 创建题目描述
func (s *ProblemService) CreateProblemStatement(ctx context.Context, req *v1.CreateProblemStatementRequest) (*v1.ProblemStatement, error) {
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

// 更新题目描述
func (s *ProblemService) UpdateProblemStatement(ctx context.Context, req *v1.UpdateProblemStatementRequest) (*v1.ProblemStatement, error) {
	res, err := s.uc.UpdateProblemStatement(ctx, &biz.ProblemStatement{
		ID:        int(req.Sid),
		ProblemID: int(req.Id),
		Name:      req.Name,
		Input:     req.Input,
		Output:    req.Output,
		Legend:    req.Legend,
		Note:      req.Note,
	})
	if err != nil {
		return nil, err
	}
	return &v1.ProblemStatement{
		Id: int32(res.ID),
	}, nil
}

// DeleteProblemStatement 删除题目描述
func (s *ProblemService) DeleteProblemStatement(ctx context.Context, req *v1.DeleteProblemStatementRequest) (*v1.ProblemStatement, error) {
	err := s.uc.DeleteProblemStatement(ctx, int(req.Sid))
	if err != nil {
		return nil, err
	}
	return &v1.ProblemStatement{}, nil
}

// ListProblemTests 获取题目测试点列表
func (s *ProblemService) ListProblemTests(ctx context.Context, req *v1.ListProblemTestsRequest) (*v1.ListProblemTestsResponse, error) {
	data, count := s.uc.ListProblemTests(ctx, req)
	resp := new(v1.ListProblemTestsResponse)
	resp.Count = count
	for _, v := range data {
		resp.Data = append(resp.Data, &v1.ProblemTest{
			Id:        v.ID,
			InputSize: v.InputSize,
			Content:   v.Content,
			Remark:    v.Remark,
			IsExample: v.IsExample,
			CreatedAt: timestamppb.New(v.CreatedAt),
			UpdatedAt: timestamppb.New(v.UpdatedAt),
		})
	}
	return resp, nil
}

// 获取题目测试点详情
func (s *ProblemService) GetProblemTest(ctx context.Context, req *v1.GetProblemTestRequest) (*v1.ProblemTest, error) {
	return nil, nil
}

// 创建题目测试点
func (s *ProblemService) CreateProblemTest(ctx context.Context, req *v1.CreateProblemTestRequest) (*v1.ProblemTest, error) {
	res, err := s.uc.CreateProblemTest(ctx, &biz.ProblemTest{
		ProblemID:        int(req.Id),
		InputFileContent: req.InputFileContent,
	})
	if err != nil {
		return nil, err
	}
	return &v1.ProblemTest{Id: res.ID}, nil
}

// 上传题目测试点
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
	in.InputFileContent = fileContent
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

// 更新题目测试点
func (s *ProblemService) UpdateProblemTest(ctx context.Context, req *v1.UpdateProblemTestRequest) (*v1.ProblemTest, error) {
	s.uc.UpdateProblemTest(ctx, &biz.ProblemTest{
		ID:        req.Tid,
		ProblemID: int(req.Id),
		Remark:    req.Remark,
		IsExample: req.IsExample,
	})
	return nil, nil
}

// 删除题目测试点
func (s *ProblemService) DeleteProblemTest(ctx context.Context, req *v1.DeleteProblemTestRequest) (*emptypb.Empty, error) {
	err := s.uc.DeleteProblemTest(ctx, int64(req.Id), req.Tid)
	return &emptypb.Empty{}, err
}

// 获取题目文件列表
func (s *ProblemService) ListProblemFiles(ctx context.Context, req *v1.ListProblemFilesRequest) (*v1.ListProblemFilesResponse, error) {
	res, count := s.uc.ListProblemFiles(ctx, req)
	resp := new(v1.ListProblemFilesResponse)
	resp.Total = count
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

// 获取题目文件详情
func (s *ProblemService) GetProblemFile(ctx context.Context, req *v1.GetProblemFileRequest) (*v1.ProblemFile, error) {
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

// 创建题目文件
func (s *ProblemService) CreateProblemFile(ctx context.Context, req *v1.CreateProblemFileRequest) (*v1.ProblemFile, error) {
	res, err := s.uc.CreateProblemFile(ctx, &biz.ProblemFile{
		ProblemID: int(req.Id),
		Content:   req.Content,
		Name:      req.Name,
		Type:      req.Type,
		FileType:  req.FileType,
	})
	if err != nil {
		return nil, err
	}
	return &v1.ProblemFile{
		Id: int32(res.ID),
	}, nil
}

// 更新题目文件
func (s *ProblemService) UpdateProblemFile(ctx context.Context, req *v1.UpdateProblemFileRequest) (*v1.ProblemFile, error) {
	s.uc.UpdateProblemFile(ctx, &biz.ProblemFile{
		ID:      int(req.Sid),
		Name:    req.Name,
		Content: req.Content,
		Type:    req.Type,
	})
	return nil, nil
}

// 删除题目文件
func (s *ProblemService) DeleteProblemFile(ctx context.Context, req *v1.DeleteProblemFileRequest) (*v1.ProblemFile, error) {
	s.uc.DeleteProblemFile(ctx, int(req.Sid))
	return nil, nil
}

func (s *ProblemService) RunProblemFile(ctx context.Context, req *v1.RunProblemFileRequest) (*emptypb.Empty, error) {
	err := s.uc.RunProblemFile(ctx, int(req.Sid))
	return &emptypb.Empty{}, err
}

// 获取题目文件列表
func (s *ProblemService) ListProblemStdCheckers(ctx context.Context, req *v1.ListProblemStdCheckersRequest) (*v1.ListProblemStdCheckersResponse, error) {
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

func (s *ProblemService) VerifyProblem(ctx context.Context, req *v1.VerifyProblemRequest) (*emptypb.Empty, error) {
	err := s.uc.VerifyProblem(ctx, int(req.Id))
	return &emptypb.Empty{}, err
}

func (s *ProblemService) UpdateProblemChecker(ctx context.Context, req *v1.UpdateProblemCheckerRequest) (*emptypb.Empty, error) {
	err := s.uc.UpdateProblemChecker(ctx, int(req.Id), int(req.CheckerId))
	return &emptypb.Empty{}, err
}

func (s *ProblemService) GetProblemVerification(ctx context.Context, req *v1.GetProblemVerificationRequest) (*v1.ProblemVerification, error) {
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
