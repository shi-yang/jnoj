package data

import (
	"context"
	"encoding/json"
	"jnoj/app/interface/internal/biz"
	"time"
)

type ProblemVerification struct {
	ID                 int
	ProblemID          int
	VerificationStatus int // 题目完整性
	VerificationInfo   string
	CreatedAt          time.Time
}

func (r *problemRepo) CreateOrUpdateProblemVerification(ctx context.Context, p *biz.ProblemVerification) error {
	r.data.db.WithContext(ctx).
		Delete(&ProblemVerification{}, "problem_id = ?", p.ProblemID)
	v := ProblemVerification{
		ProblemID:          p.ProblemID,
		VerificationStatus: p.VerificationStatus,
	}
	j, _ := json.Marshal(p.VerificationInfo)
	v.VerificationInfo = string(j)
	return r.data.db.WithContext(ctx).
		Create(&v).Error
}

func (r *problemRepo) GetProblemVerification(ctx context.Context, id int) (*biz.ProblemVerification, error) {
	var v ProblemVerification
	err := r.data.db.WithContext(ctx).
		First(&v, "problem_id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	res := &biz.ProblemVerification{
		ID:                 v.ID,
		VerificationStatus: v.VerificationStatus,
	}
	json.Unmarshal([]byte(v.VerificationInfo), &res.VerificationInfo)
	return res, nil
}
