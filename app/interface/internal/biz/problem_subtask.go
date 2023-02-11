package biz

import (
	"encoding/json"
	"errors"
	"log"
)

type Subtask struct {
	Score       int   // 分数
	TimeLimit   int64 // 时间限制
	MemoryLimit int64 // 内存限制
	Tests       []int // 测试点
}

// GetProblemSubtaskContent 获取测评子任务
func (uc *ProblemUsecase) GetProblemSubtaskContent(content string) ([]Subtask, error) {
	var res []Subtask
	err := json.Unmarshal([]byte(content), &res)
	if err != nil {
		return nil, err
	}
	var score int
	for _, v := range res {
		if v.Score <= 0 || v.Score > 100 {
			return nil, errors.New("score must be between 0, 100")
		}
		if v.MemoryLimit != 0 && (v.MemoryLimit < 4 || v.MemoryLimit > 1024) {
			return nil, errors.New("memory limit must be between 4, 1024")
		}
		if v.TimeLimit != 0 && (v.TimeLimit < 250 || v.TimeLimit > 15000) {
			return nil, errors.New("time limit must be between 250, 15000")
		}
		score += v.Score
	}
	if score != 100 {
		return nil, errors.New("score must equal 100")
	}
	log.Println("score", score)
	log.Println("score", score)
	log.Println("score", score)
	log.Println("score", score)
	return res, nil
}
