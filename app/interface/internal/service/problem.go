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

// 删除题目描述
func (s *ProblemService) DeleteProblemStatement(ctx context.Context, req *v1.DeleteProblemStatementRequest) (*v1.ProblemStatement, error) {
	return nil, nil
}

// 获取题目裁判程序列表
func (s *ProblemService) ListProblemCheckers(ctx context.Context, req *v1.ListProblemCheckersRequest) (*v1.ListProblemCheckersResponse, error) {
	return nil, nil
}

// 获取题目裁判程序
func (s *ProblemService) GetProblemChecker(ctx context.Context, req *v1.GetProblemCheckerRequest) (*v1.ProblemChecker, error) {
	return nil, nil
}

// 创建题目裁判程序
func (s *ProblemService) CreateProblemChecker(ctx context.Context, req *v1.CreateProblemCheckerRequest) (*v1.ProblemChecker, error) {
	return nil, nil
}

// 更新题目裁判程序
func (s *ProblemService) UpdateProblemChecker(ctx context.Context, req *v1.UpdateProblemCheckerRequest) (*v1.ProblemChecker, error) {
	return nil, nil
}

// 获取题目测试点列表
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

// 获取题目解答程序列表
func (s *ProblemService) ListProblemSolutions(ctx context.Context, req *v1.ListProblemSolutionsRequest) (*v1.ListProblemSolutionsResponse, error) {
	res, count := s.uc.ListProblemSolutions(ctx, req)
	resp := new(v1.ListProblemSolutionsResponse)
	resp.Total = count
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.ProblemSolution{
			Id:        int32(v.ID),
			Name:      v.Name,
			Type:      v.Type,
			CreatedAt: timestamppb.New(v.CreatedAt),
			UpdatedAt: timestamppb.New(v.UpdatedAt),
		})
	}
	return resp, nil
}

// 获取题目解答程序详情
func (s *ProblemService) GetProblemSolution(ctx context.Context, req *v1.GetProblemSolutionRequest) (*v1.ProblemSolution, error) {
	res, err := s.uc.GetProblemSolution(ctx, int(req.Sid))
	if err != nil {
		return nil, err
	}
	return &v1.ProblemSolution{
		Id:        int32(res.ID),
		Name:      res.Name,
		Content:   res.Content,
		Type:      res.Type,
		UserId:    int32(res.UserID),
		CreatedAt: timestamppb.New(res.CreatedAt),
		UpdatedAt: timestamppb.New(res.UpdatedAt),
	}, nil
}

// 创建题目解答程序
func (s *ProblemService) CreateProblemSolution(ctx context.Context, req *v1.CreateProblemSolutionRequest) (*v1.ProblemSolution, error) {
	s.uc.CreateProblemSolution(ctx, &biz.ProblemSolution{
		ProblemID: int(req.Id),
		Content:   req.Content,
		Name:      req.Name,
		Type:      req.Type,
	})
	return nil, nil
}

// 更新题目解答程序
func (s *ProblemService) UpdateProblemSolution(ctx context.Context, req *v1.UpdateProblemSolutionRequest) (*v1.ProblemSolution, error) {
	s.uc.UpdateProblemSolution(ctx, &biz.ProblemSolution{
		ID:      int(req.Sid),
		Name:    req.Name,
		Content: req.Content,
		Type:    req.Type,
	})
	return nil, nil
}

// 删除题目解答程序
func (s *ProblemService) DeleteProblemSolution(ctx context.Context, req *v1.DeleteProblemSolutionRequest) (*v1.ProblemSolution, error) {
	s.uc.DeleteProblemSolution(ctx, int(req.Sid))
	return nil, nil
}

func (s *ProblemService) RunProblemSolution(ctx context.Context, req *v1.RunProblemSolutionRequest) (*emptypb.Empty, error) {
	err := s.uc.RunProblemSolution(ctx, int(req.Sid))
	return &emptypb.Empty{}, err
}
