package service

import (
	"context"
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

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
			Id: int32(v.ID),
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
	return &v1.Problem{
		Id: int32(data.ID),
	}, nil
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
	return nil, nil
}

// 获取题目描述列表
func (s *ProblemService) ListProblemStatements(ctx context.Context, req *v1.ListProblemStatementsRequest) (*v1.ListProblemStatementsResponse, error) {
	return nil, nil
}

// 获取题目描述详情
func (s *ProblemService) GetProblemStatement(ctx context.Context, req *v1.GetProblemStatementRequest) (*v1.ProblemStatement, error) {
	return nil, nil
}

// 创建题目描述
func (s *ProblemService) CreateProblemStatement(ctx context.Context, req *v1.CreateProblemStatementRequest) (*v1.ProblemStatement, error) {
	return nil, nil
}

// 更新题目描述
func (s *ProblemService) UpdateProblemStatement(ctx context.Context, req *v1.UpdateProblemStatementRequest) (*v1.ProblemStatement, error) {
	return nil, nil
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
			Size:      v.Size,
			Content:   v.Content,
			Remark:    v.Remark,
			UserId:    v.UserID,
			IsExample: v.IsExample,
			CreatedAt: timestamppb.New(v.CreatedAt),
			UpdatedAt: timestamppb.New(v.UpdatedAt),
		})
	}
	return nil, nil
}

// 获取题目测试点详情
func (s *ProblemService) GetProblemTest(ctx context.Context, req *v1.GetProblemTestRequest) (*v1.ProblemTest, error) {
	return nil, nil
}

// 创建题目测试点
func (s *ProblemService) CreateProblemTest(ctx context.Context, req *v1.CreateProblemTestRequest) (*v1.ProblemTest, error) {
	return nil, nil
}

// 更新题目测试点
func (s *ProblemService) UpdateProblemTest(ctx context.Context, req *v1.UpdateProblemTestRequest) (*v1.ProblemTest, error) {
	return nil, nil
}

// 删除题目测试点
func (s *ProblemService) DeleteProblemTest(ctx context.Context, req *v1.DeleteProblemTestRequest) (*v1.ProblemTest, error) {
	return nil, nil
}

// 获取题目解答程序列表
func (s *ProblemService) ListProblemSolutions(ctx context.Context, req *v1.ListProblemSolutionsRequest) (*v1.ListProblemSolutionsResponse, error) {
	return nil, nil
}

// 获取题目解答程序详情
func (s *ProblemService) GetProblemSolution(ctx context.Context, req *v1.GetProblemSolutionRequest) (*v1.ProblemSolution, error) {
	return nil, nil
}

// 创建题目解答程序
func (s *ProblemService) CreateProblemSolution(ctx context.Context, req *v1.CreateProblemSolutionRequest) (*v1.ProblemSolution, error) {
	return nil, nil
}

// 更新题目解答程序
func (s *ProblemService) UpdateProblemSolution(ctx context.Context, req *v1.UpdateProblemSolutionRequest) (*v1.ProblemSolution, error) {
	return nil, nil
}

// 删除题目解答程序
func (s *ProblemService) DeleteProblemSolution(ctx context.Context, req *v1.DeleteProblemSolutionRequest) (*v1.ProblemSolution, error) {
	return nil, nil
}
