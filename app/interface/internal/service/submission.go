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
	if err != nil {
		return nil, err
	}
	var infoResp *v1.SubmissionInfo
	if res.SubmissionInfo != nil {
		info := res.SubmissionInfo
		infoResp = &v1.SubmissionInfo{
			Score:             float32(res.Score),
			CompileMsg:        info.CompileMsg,
			Memory:            info.Memory,
			Time:              info.Time,
			HasSubtask:        info.HasSubtask,
			TotalTestCount:    int32(info.TotalTestCount),
			AcceptedTestCount: int32(info.AcceptedTestCount),
		}
		infoResp.Subtasks = make([]*v1.SubmissionInfo_SubmissionSubtaskResult, 0)
		for _, subtask := range info.Subtasks {
			subtaskResult := &v1.SubmissionInfo_SubmissionSubtaskResult{
				Verdict: int32(subtask.Verdict),
				Memory:  subtask.Memory,
				Time:    subtask.Time,
				Score:   float32(subtask.Score),
			}
			for _, v := range subtask.Tests {
				subtaskResult.Tests = append(subtaskResult.Tests, &v1.SubmissionInfo_SubmissionTest{
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
			infoResp.Subtasks = append(infoResp.Subtasks, subtaskResult)
		}
	}
	return &v1.Submission{
		Id:        int64(res.ID),
		Source:    res.Source,
		Memory:    int64(res.Memory),
		Time:      int64(res.Time),
		Verdict:   int32(res.Verdict),
		Language:  int32(res.Language),
		CreatedAt: timestamppb.New(res.CreatedAt),
		Info:      infoResp,
	}, nil
}

// CreateSubmission .
func (s *SubmissionService) CreateSubmission(ctx context.Context, req *v1.CreateSubmissionRequest) (*v1.Submission, error) {
	submission := &biz.Submission{
		ProblemID:     int(req.ProblemId),
		Source:        req.Source,
		Language:      int(req.Language),
		ProblemNumber: int(req.ProblemNumber),
	}
	if req.ContestId != 0 {
		submission.EntityID = int(req.ContestId)
		submission.EntityType = biz.SubmissionEntityTypeContest
	}
	res, err := s.uc.CreateSubmission(ctx, submission)
	if err != nil {
		return nil, err
	}
	return &v1.Submission{
		Id: int64(res.ID),
	}, nil
}

// GetLastSubmission 获取最后提交
func (s *SubmissionService) GetLastSubmission(ctx context.Context, req *v1.GetLastSubmissionRequest) (*v1.Submission, error) {
	res, err := s.uc.GetLastSubmission(ctx, int(req.EntityType), int(req.EntityId), int(req.ProblemId))
	if err != nil {
		return nil, v1.ErrorNotFound(err.Error())
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
