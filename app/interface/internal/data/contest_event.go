package data

import (
	"context"
	"jnoj/app/interface/internal/biz"
	"time"

	"gorm.io/gorm/clause"
)

type ContestEvent struct {
	Id        int
	ContestId int
	UserId    int
	ProblemId int
	Type      int
	CreatedAt time.Time

	ContestUser *ContestUser `gorm:"foreignKey:UserId"`
}

func (r *contestRepo) GetContestEvent(ctx context.Context, id int) (*biz.ContestEvent, error) {
	db := r.data.db.WithContext(ctx).
		Select("ce.id, ce.contest_id, ce.user_id, ce.problem_id, ce.type, ce.created_at, IFNULL(cu.name, ''), u.nickname, u.avatar").
		Table("contest_event AS `ce`").
		Joins("LEFT JOIN contest_user AS `cu` ON cu.user_id=ce.user_id AND cu.contest_id=ce.contest_id").
		Joins("LEFT JOIN user AS `u` ON u.id=ce.user_id").
		Where("ce.id = ?", id)

	var ce = &biz.ContestEvent{
		ContestUser: &biz.ContestUser{},
	}
	err := db.Row().Scan(&ce.Id, &ce.ContestId, &ce.UserId, &ce.ProblemId, &ce.Type, &ce.CreatedAt, &ce.ContestUser.Name,
		&ce.ContestUser.UserNickname, &ce.ContestUser.UserAvatar)
	if err != nil {
		return nil, err
	}
	if ce.ContestUser.Name == "" {
		ce.ContestUser.Name = ce.ContestUser.UserNickname
	}
	return ce, nil
}

func (r *contestRepo) ListContestEvents(ctx context.Context, contestId int, userId int) ([]*biz.ContestEvent, int64) {
	count := int64(0)
	db := r.data.db.WithContext(ctx).
		Select("ce.id, ce.contest_id, ce.user_id, ce.problem_id, ce.type, ce.created_at, IFNULL(cu.name, ''), u.nickname, u.avatar").
		Table("contest_event AS `ce`").
		Joins("LEFT JOIN contest_user AS `cu` ON cu.user_id=ce.user_id AND cu.contest_id=ce.contest_id").
		Joins("LEFT JOIN user AS `u` ON u.id=ce.user_id").
		Where("ce.contest_id = ?", contestId)
	if userId != 0 {
		db = db.Where("ce.user_id = ?", userId)
	}
	db.Count(&count)
	rows, _ := db.Rows()
	rv := make([]*biz.ContestEvent, 0)
	for rows.Next() {
		var ce = &biz.ContestEvent{
			ContestUser: &biz.ContestUser{},
		}
		rows.Scan(&ce.Id, &ce.ContestId, &ce.UserId, &ce.ProblemId, &ce.Type, &ce.CreatedAt, &ce.ContestUser.Name,
			&ce.ContestUser.UserNickname, &ce.ContestUser.UserAvatar)
		if ce.ContestUser.Name == "" {
			ce.ContestUser.Name = ce.ContestUser.UserNickname
		}
		ce.ContestUser.UserID = ce.UserId
		rv = append(rv, ce)
	}
	return rv, count
}

// CreateContestEvent .
func (r *contestRepo) CreateContestEvent(ctx context.Context, b *biz.ContestEvent, submissionTime time.Time) error {
	// 检查是否满足一血条件
	var userId int
	r.data.db.WithContext(ctx).
		Select("user_id").
		Model(&Submission{}).
		Where("entity_type = ? and entity_id = ?", biz.SubmissionEntityTypeContest, b.ContestId).
		Where("verdict = ?", biz.SubmissionVerdictAccepted).
		Limit(1).
		Scan(&userId)
	if userId == b.UserId {
		res := ContestEvent{
			ContestId: b.ContestId,
			UserId:    b.UserId,
			ProblemId: b.ProblemId,
			Type:      biz.ContestEventTypeFirstSolve,
			CreatedAt: submissionTime,
		}
		r.data.db.WithContext(ctx).
			Omit(clause.Associations).
			FirstOrCreate(&res,
				"contest_id = ? and user_id = ? and type = ? and problem_id = ?",
				res.ContestId, res.UserId, res.Type, res.ProblemId,
			)
	}
	// 检查是否满足AK的条件
	var acProblemCount, problemCount int
	r.data.db.WithContext(ctx).
		Select("COUNT(DISTINCT (problem_id))").
		Model(&Submission{}).
		Where("user_id = ?", b.UserId).
		Where("entity_type = ? and entity_id = ?", biz.SubmissionEntityTypeContest, b.ContestId).
		Where("verdict = ?", biz.SubmissionVerdictAccepted).
		Scan(&acProblemCount)
	r.data.db.WithContext(ctx).
		Select("count(*)").
		Model(&ContestProblem{}).
		Where("contest_id = ?", b.ContestId).
		Scan(&problemCount)
	if acProblemCount == problemCount {
		res := ContestEvent{
			ContestId: b.ContestId,
			UserId:    b.UserId,
			ProblemId: b.ProblemId,
			Type:      biz.ContestEventTypeAK,
			CreatedAt: submissionTime,
		}
		r.data.db.WithContext(ctx).
			Omit(clause.Associations).
			FirstOrCreate(&res, "contest_id = ? and user_id = ? and type = ?", res.ContestId, res.UserId, res.Type)
	}
	return nil
}
