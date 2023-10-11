package service

import (
	"context"
	"strconv"

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
		s := &v1.Submission{
			Id:            int64(v.ID),
			ProblemId:     int32(v.ProblemID),
			ProblemName:   v.ProblemName,
			ProblemNumber: strconv.Itoa(v.ProblemNumber),
			UserId:        int32(v.UserID),
			Nickname:      v.Nickname,
			Time:          int64(v.Time),
			Memory:        int64(v.Memory),
			Language:      int32(v.Language),
			Verdict:       int32(v.Verdict),
			Score:         int32(v.Score),
			EntityId:      int32(v.EntityID),
			EntityType:    v1.SubmissionEntityType(v.EntityType),
			CreatedAt:     timestamppb.New(v.CreatedAt),
		}
		// 比赛的题目序号转化为 A,B,C,D的形式
		if v.EntityType == biz.SubmissionEntityTypeContest {
			letter := rune(v.ProblemNumber + 65)
			s.ProblemNumber = string(letter)
		}
		resp.Data = append(resp.Data, s)
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
				Score:   subtask.Score,
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
					Score:           v.Score,
					CheckerExitCode: int32(v.CheckerExitCode),
					CheckerStdout:   v.CheckerStdout,
				})
			}
			infoResp.Subtasks = append(infoResp.Subtasks, subtaskResult)
		}
	}
	submission := &v1.Submission{
		Id:            int64(res.ID),
		EntityId:      int32(res.EntityID),
		EntityType:    v1.SubmissionEntityType(res.EntityType),
		ProblemName:   res.ProblemName,
		ProblemNumber: strconv.Itoa(res.ProblemNumber),
		Score:         int32(res.Score),
		Source:        res.Source,
		Memory:        int64(res.Memory),
		Time:          int64(res.Time),
		Verdict:       int32(res.Verdict),
		Language:      int32(res.Language),
		CreatedAt:     timestamppb.New(res.CreatedAt),
		Info:          infoResp,
		UserId:        int32(res.UserID),
		Nickname:      res.Nickname,
	}
	// 比赛的题目序号转化为 A,B,C,D的形式
	if res.EntityType == biz.SubmissionEntityTypeContest {
		letter := rune(res.ProblemNumber + 65)
		submission.ProblemNumber = string(letter)
	}
	return submission, nil
}

// CreateSubmission .
func (s *SubmissionService) CreateSubmission(ctx context.Context, req *v1.CreateSubmissionRequest) (*v1.Submission, error) {
	submission := &biz.Submission{
		Source:        req.Source,
		Language:      int(req.Language),
		ProblemNumber: int(req.ProblemNumber),
		EntityID:      int(req.EntityId),
		EntityType:    int(req.EntityType),
	}
	res, err := s.uc.CreateSubmission(ctx, submission)
	if err != nil {
		return nil, err
	}
	return &v1.Submission{
		Id:        int64(res.ID),
		CreatedAt: timestamppb.New(res.CreatedAt),
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
