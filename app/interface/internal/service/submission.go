package service

import (
	"context"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"google.golang.org/protobuf/types/known/timestamppb"
)

// SubmissionService is a contest service.
type SubmissionService struct {
	uc *biz.SubmissionUsecase
}

// NewSubmissionService new a contest service.
func NewSubmissionService(uc *biz.SubmissionUsecase) *SubmissionService {
	return &SubmissionService{uc: uc}
}

// ListSubmissions 比赛列表
func (s *SubmissionService) ListSubmissions(ctx context.Context, req *v1.ListSubmissionsRequest) (*v1.ListSubmissionsResponse, error) {
	res, count := s.uc.ListSubmissions(ctx, req)
	resp := new(v1.ListSubmissionsResponse)
	resp.Total = count
	resp.Data = make([]*v1.Submission, 0)
	for _, v := range res {
		resp.Data = append(resp.Data, &v1.Submission{
			Id:        int64(v.ID),
			Time:      int64(v.Time),
			Memory:    int64(v.Memory),
			Language:  int32(v.Language),
			Verdict:   int32(v.Verdict),
			Score:     int32(v.Score),
			CreatedAt: timestamppb.New(v.CreatedAt),
		})
	}
	return resp, nil
}

// GetSubmission .
func (s *SubmissionService) GetSubmission(ctx context.Context, req *v1.GetSubmissionRequest) (*v1.Submission, error) {
	res, err := s.uc.GetSubmission(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	return &v1.Submission{
		Id:        int64(res.ID),
		Source:    res.Source,
		Memory:    int64(res.Memory),
		Time:      int64(res.Time),
		Verdict:   int32(res.Verdict),
		Language:  int32(res.Language),
		CreatedAt: timestamppb.New(res.CreatedAt),
	}, nil
}

// CreateSubmission .
func (s *SubmissionService) CreateSubmission(ctx context.Context, req *v1.CreateSubmissionRequest) (*v1.Submission, error) {
	s.uc.CreateSubmission(ctx, &biz.Submission{
		ProblemID:     int(req.ProblemId),
		Source:        req.Source,
		Language:      int(req.Language),
		ContestID:     int(req.ContestId),
		ProblemNumber: int(req.ProblemNumber),
	})
	return nil, nil
}

// GetSubmissionInfo .
func (s *SubmissionService) GetSubmissionInfo(ctx context.Context, req *v1.GetSubmissionInfoRequest) (*v1.SubmissionInfo, error) {
	res, err := s.uc.GetSubmissionInfo(ctx, int(req.Id))
	if err != nil {
		return nil, err
	}
	resp := &v1.SubmissionInfo{
		CompileMsg: res.CompileMsg,
		Memory:     res.Memory,
		Time:       res.Time,
	}
	resp.Tests = make([]*v1.SubmissionInfo_SubmissionTest, 0)
	for _, v := range res.Tests {
		resp.Tests = append(resp.Tests, &v1.SubmissionInfo_SubmissionTest{
			Memory:          v.Memory,
			Time:            v.Time,
			Stdin:           v.Stdin,
			Stdout:          v.Stdout,
			Answer:          v.Answer,
			Stderr:          v.Stderr,
			Verdict:         int32(v.Verdict),
			ExitCode:        int32(v.ExitCode),
			Score:           int32(v.Score),
			CheckerExitCode: int32(v.CheckerExitCode),
			CheckerStdout:   v.CheckerStdout,
		})
	}
	return resp, nil
}
