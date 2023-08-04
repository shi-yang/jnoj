package data

import (
	"context"
	"errors"
	"fmt"
	"math"
	"strings"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/internal/middleware/auth"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type ProblemsetRepo struct {
	data *Data
	log  *log.Helper
}

type Problemset struct {
	ID                 int
	Name               string
	Type               int
	Description        string
	UserID             int
	Membership         int    // 加入资格
	InvitationCode     string // 邀请码
	ProblemCount       int
	MemberCount        int
	CreatedAt          time.Time
	UpdatedAt          time.Time
	DeletedAt          gorm.DeletedAt
	ProblemsetProblems []*ProblemsetProblem `gorm:"ForeignKey:ProblemsetID"`
	User               *User                `gorm:"ForeighKey:UserID"`
}

type ProblemsetUser struct {
	ID            int
	ProblemsetID  int
	UserID        int
	AcceptedCount int // 过题量
	CreatedAt     time.Time
	User          *User `json:"user" gorm:"foreignKey:UserID"`
}

type ProblemsetAnswer struct {
	ID                   int
	ProblemsetID         int
	UserID               int
	Answer               string
	AnsweredProblemIDs   string // 回答题目题目
	UnansweredProblemIDs string // 未回答题目
	CorrectProblemIDs    string // 正确题目
	WrongProblemIDs      string // 错误题目
	SubmissionIDs        string // 编程题提交ID
	SubmittedAt          *time.Time
	CreatedAt            time.Time
	UpdatedAt            time.Time
}

type ProblemsetProblem struct {
	ID           int
	ProblemID    int
	ProblemsetID int
	Order        int
	Problem      *Problem `gorm:"ForeignKey:ProblemID"`
}

// NewProblemsetRepo .
func NewProblemsetRepo(data *Data, logger log.Logger) biz.ProblemsetRepo {
	return &ProblemsetRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// ListProblemsets .
func (r *ProblemsetRepo) ListProblemsets(ctx context.Context, req *v1.ListProblemsetsRequest) ([]*biz.Problemset, int64) {
	res := []Problemset{}
	count := int64(0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&res).
		Preload("User", func(t *gorm.DB) *gorm.DB {
			return t.Select("ID", "Nickname", "Username")
		})
	if req.Name != "" {
		db.Where("name like ?", "%"+req.Name+"%")
	}
	if len(req.Type) > 0 {
		db.Where("type in (?)", req.Type)
	}
	db.Count(&count)
	db.Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&res)
	rv := make([]*biz.Problemset, 0)
	for _, v := range res {
		rv = append(rv, &biz.Problemset{
			ID:           v.ID,
			Name:         v.Name,
			Type:         v.Type,
			Description:  v.Description,
			CreatedAt:    v.CreatedAt,
			ProblemCount: v.ProblemCount,
			MemberCount:  v.MemberCount,
			UserID:       v.UserID,
			Membership:   v.Membership,
			User: &biz.User{
				ID:       v.User.ID,
				Nickname: v.User.Nickname,
				Username: v.User.Username,
			},
		})
	}
	return rv, count
}

// GetProblemset .
func (r *ProblemsetRepo) GetProblemset(ctx context.Context, id int) (*biz.Problemset, error) {
	var res Problemset
	err := r.data.db.Model(Problemset{}).
		Preload("User", func(t *gorm.DB) *gorm.DB {
			return t.Select("ID", "Nickname", "Username", "Avatar")
		}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	set := &biz.Problemset{
		ID:             res.ID,
		Name:           res.Name,
		Type:           res.Type,
		Description:    res.Description,
		ProblemCount:   res.ProblemCount,
		MemberCount:    res.MemberCount,
		Membership:     res.Membership,
		InvitationCode: res.InvitationCode,
		CreatedAt:      res.CreatedAt,
		UserID:         res.UserID,
		User: &biz.User{
			ID:       res.User.ID,
			Nickname: res.User.Nickname,
			Username: res.User.Username,
			Avatar:   res.User.Avatar,
		},
	}
	// 查询登录用户的角色
	set.Role = biz.ProblemsetRoleGuest
	if uid, role := auth.GetUserID(ctx); uid != 0 {
		problemsetUser := r.GetProblemsetUser(ctx, res.ID, uid)
		if problemsetUser != nil {
			set.Role = biz.ProblemsetRolePlayer
		}
		if uid == res.UserID || biz.CheckAccess(role, biz.ResourceProblem) {
			set.Role = biz.ProblemsetRoleAdmin
		}
	}
	return set, err
}

// CreateProblemset .
func (r *ProblemsetRepo) CreateProblemset(ctx context.Context, b *biz.Problemset) (*biz.Problemset, error) {
	res := Problemset{
		Name:        b.Name,
		UserID:      b.UserID,
		Type:        b.Type,
		Description: b.Description,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.Problemset{
		ID: res.ID,
	}, err
}

// UpdateProblemset .
func (r *ProblemsetRepo) UpdateProblemset(ctx context.Context, b *biz.Problemset) (*biz.Problemset, error) {
	err := r.data.db.WithContext(ctx).
		Model(&Problemset{ID: b.ID}).
		Updates(map[string]interface{}{
			"name":            b.Name,
			"description":     b.Description,
			"membership":      b.Membership,
			"invitation_code": b.InvitationCode,
		}).Error
	return &biz.Problemset{ID: b.ID}, err
}

// DeleteProblemset .
func (r *ProblemsetRepo) DeleteProblemset(ctx context.Context, id int) error {
	tx := r.data.db.WithContext(ctx).Begin()
	err := tx.Omit(clause.Associations).
		Delete(&Problemset{ID: id}).
		Error
	if err != nil {
		tx.Rollback()
		return err
	}
	err = tx.Delete(&ProblemsetProblem{}, "problemset_id = ?", id).
		Error
	if err != nil {
		tx.Rollback()
		return err
	}
	tx.Commit()
	return nil
}

// ListProblemsetUsers 获取题单的用户
func (r *ProblemsetRepo) ListProblemsetUsers(ctx context.Context, req *v1.ListProblemsetUsersRequest) ([]*biz.ProblemsetUser, int64) {
	res := []ProblemsetUser{}
	count := int64(0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	db := r.data.db.WithContext(ctx).
		Model(&ProblemsetUser{}).
		Preload("User", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, nickname, avatar")
		})
	db.Where("problemset_id = ?", req.Id)
	db.Count(&count)
	db.Offset(page.GetOffset()).
		Limit(page.GetPageSize()).
		Find(&res)
	rv := make([]*biz.ProblemsetUser, 0)
	for _, v := range res {
		g := &biz.ProblemsetUser{
			ID:            v.ID,
			UserID:        v.UserID,
			UserNickname:  v.User.Nickname,
			UserAvatar:    v.User.Avatar,
			AcceptedCount: v.AcceptedCount,
			CreatedAt:     v.CreatedAt,
		}
		rv = append(rv, g)
	}
	return rv, count
}

// GetProblemsetUser 查询题单用户信息
func (r *ProblemsetRepo) GetProblemsetUser(ctx context.Context, sid int, uid int) *biz.ProblemsetUser {
	var res ProblemsetUser
	err := r.data.db.WithContext(ctx).
		Model(&ProblemsetUser{}).
		Where("problemset_id = ? and user_id = ?", sid, uid).
		First(&res).
		Error
	if errors.Is(err, gorm.ErrRecordNotFound) {
		return nil
	}
	return &biz.ProblemsetUser{
		ID:            res.ID,
		UserID:        res.UserID,
		AcceptedCount: res.AcceptedCount,
		CreatedAt:     res.CreatedAt,
	}
}

// CreateProblemsetUser 添加用户到题单
func (r *ProblemsetRepo) CreateProblemsetUser(ctx context.Context, u *biz.ProblemsetUser) (*biz.ProblemsetUser, error) {
	var create = ProblemsetUser{
		ProblemsetID: u.ProblemsetID,
		UserID:       u.UserID,
	}
	err := r.data.db.WithContext(ctx).
		Create(&create).Error
	u.ID = create.ID
	r.UpdateProblemsetUserAccepted(ctx, u.ProblemsetID, u.UserID)
	r.UpdateProblemsetMemberCount(ctx, u.ProblemsetID)
	return u, err
}

// DeleteProblemsetUser 删除题单用户
func (r *ProblemsetRepo) DeleteProblemsetUser(ctx context.Context, sid int, uid int) error {
	err := r.data.db.WithContext(ctx).Delete(&ProblemsetUser{}, "problemset_id = ? and user_id = ?", sid, uid).Error
	r.UpdateProblemsetMemberCount(ctx, sid)
	return err
}

// UpdateProblemsetUserAccepted 更新用户本题单过提数
func (r *ProblemsetRepo) UpdateProblemsetUserAccepted(ctx context.Context, sid int, uid int) {
	var count int
	problemIds := r.data.db.WithContext(ctx).
		Select("problem_id").
		Model(&ProblemsetProblem{}).
		Where("problemset_id = ?", sid)
	r.data.db.WithContext(ctx).
		Model(&Submission{}).
		Select("COUNT(DISTINCT problem_id) AS accepted_count").
		Where("user_id = ?", uid).
		Where("verdict = ?", biz.SubmissionVerdictAccepted).
		Where("problem_id in (?)", problemIds).
		Scan(&count)
	r.data.db.WithContext(ctx).
		Model(&ProblemsetUser{}).
		Where("problemset_id = ? and user_id = ?", sid, uid).
		UpdateColumn("accepted_count", count)
}

// UpdateProblemsetMemberCount 更新题单用户数
func (r *ProblemsetRepo) UpdateProblemsetMemberCount(ctx context.Context, sid int) {
	countQuery := r.data.db.WithContext(ctx).
		Select("COUNT(*)").
		Model(&ProblemsetUser{}).
		Where("problemset_id = ?", sid)
	r.data.db.WithContext(ctx).
		Model(&Problemset{}).
		Where("id = ?", sid).
		UpdateColumn("member_count", countQuery)
}

func (r *ProblemsetRepo) ListProblemsetProblems(ctx context.Context, req *v1.ListProblemsetProblemsRequest) ([]*biz.ProblemsetProblem, int64) {
	rv := make([]*biz.ProblemsetProblem, 0)
	page := pagination.NewPagination(req.Page, req.PerPage)
	count := int64(0)
	db := r.data.db.WithContext(ctx).
		Select(`
		pp.id,
		pp.order,
		ps.name,
		pp.problem_id,
		problem.type,
		problem.accepted_count,
		problem.submit_count,
		problem.source, GROUP_CONCAT(pt.name) AS tags`).
		Table("problemset_problem AS pp").
		Joins("LEFT JOIN problem ON problem.id = pp.problem_id").
		Joins(`LEFT JOIN(
			SELECT problem_id, name
			FROM problem_statement
			WHERE id IN(SELECT MIN(id) FROM problem_statement WHERE deleted_at is null GROUP BY problem_id)
		) AS ps
		ON ps.problem_id = pp.problem_id`).
		Joins(`LEFT JOIN(
			SELECT
				problem_tag.name,
				problem_tag_problem.problem_id
			FROM
				problem_tag
			INNER JOIN problem_tag_problem ON problem_tag_problem.problem_tag_id = problem_tag.id
		) AS pt
		ON
			pt.problem_id = pp.problem_id`)
	db.Where("problemset_id = ?", req.Id)
	if req.Keyword != "" {
		db.Where("problem.name like ? or problem.source like ? or pt.name like ?",
			fmt.Sprintf("%s%%", req.Keyword),
			fmt.Sprintf("%s%%", req.Keyword),
			fmt.Sprintf("%%%s%%", req.Keyword),
		)
	}
	db.Group("pp.id, ps.name").
		Order("pp.order").
		Count(&count)
	db.Offset(page.GetOffset()).
		Limit(page.GetPageSize())

	rows, _ := db.Rows()
	for rows.Next() {
		p := &biz.ProblemsetProblem{}
		var tags string
		rows.Scan(&p.ID, &p.Order, &p.Name, &p.ProblemID, &p.Type, &p.AcceptedCount, &p.SubmitCount, &p.Source, &tags)
		if len(tags) != 0 {
			p.Tags = strings.Split(tags, ",")
		}
		rv = append(rv, p)
	}
	return rv, count
}

// ListProblemsetProblemStatements .
func (r *ProblemsetRepo) ListProblemsetProblemStatements(ctx context.Context, ids []int) map[int]*biz.ProblemStatement {
	var statements []*ProblemStatement
	res := make(map[int]*biz.ProblemStatement)
	r.data.db.WithContext(ctx).
		Where("problem_id in (?)", ids).
		Find(&statements)
	for _, v := range statements {
		res[v.ProblemID] = &biz.ProblemStatement{
			ProblemID: v.ProblemID,
			Name:      v.Name,
			Legend:    v.Legend,
			Input:     v.Input,
			Output:    v.Output,
			Note:      v.Note,
			Type:      v.Type,
		}
	}
	return res
}

// GetProblemsetProblem .
func (r *ProblemsetRepo) GetProblemsetProblem(ctx context.Context, sid int, order int) (*biz.ProblemsetProblem, error) {
	var p ProblemsetProblem
	err := r.data.db.Model(&ProblemsetProblem{}).
		First(&p, "problemset_id = ? and `order` = ?", sid, order).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.ProblemsetProblem{
		ID:           p.ID,
		ProblemID:    p.ProblemID,
		ProblemsetID: p.ProblemsetID,
		Order:        p.Order,
	}, nil
}

// GetProblemsetLateralProblem .
func (r *ProblemsetRepo) GetProblemsetLateralProblem(ctx context.Context, id int, pid int) (int, int) {
	var previous, next int
	db := r.data.db.Model(&ProblemsetProblem{}).
		Select("`order`").
		Where("problemset_id = ?", id)
	db.Session(&gorm.Session{}).Where("`order` < ?", pid).Order("`order` desc").Limit(1).Scan(&previous)
	db.Session(&gorm.Session{}).Where("`order` > ?", pid).Order("`order`").Limit(1).Scan(&next)
	return previous, next
}

// AddProblemToProblemset .
func (r *ProblemsetRepo) AddProblemToProblemset(ctx context.Context, problem *biz.ProblemsetProblem) error {
	// 判断是否已经存在
	var count int64
	r.data.db.WithContext(ctx).
		Model(&ProblemsetProblem{}).
		Where("problemset_id = ? and problem_id = ?", problem.ProblemsetID, problem.ProblemID).
		Count(&count)
	if count > 0 {
		return errors.New("已经存在")
	}
	db := r.data.db.WithContext(ctx).Begin()
	if problem.Order == 0 {
		var maxOrder int
		db.Select("max(`order`)").Model(&ProblemsetProblem{}).Where("problemset_id = ?", problem.ProblemsetID).Scan(&maxOrder)
		err := db.Create(&ProblemsetProblem{
			ProblemID:    problem.ProblemID,
			ProblemsetID: problem.ProblemsetID,
			Order:        maxOrder + 1,
		}).Error
		if err != nil {
			db.Rollback()
			return err
		}
	}
	db.Model(&Problemset{ID: problem.ProblemsetID}).
		UpdateColumn("problem_count", gorm.Expr("problem_count + 1"))
	db.Commit()
	return nil
}

// DeleteProblemFromProblemset .
func (r *ProblemsetRepo) DeleteProblemFromProblemset(ctx context.Context, sid int, order int) error {
	tx := r.data.db.WithContext(ctx).Begin()
	err := tx.Delete(&ProblemsetProblem{}, "problemset_id = ? and `order` = ?", sid, order).Error
	if err != nil {
		tx.Rollback()
		return err
	}
	tx.Model(&Problemset{ID: sid}).
		UpdateColumn("problem_count", gorm.Expr("problem_count - 1"))
	// 调整移除后的顺序
	var ids []int
	tx.Select("id").Model(&ProblemsetProblem{}).Where("problemset_id = ? and `order` > ?", sid, order).Scan(&ids)
	for index, id := range ids {
		err := tx.Model(&ProblemsetProblem{ID: id}).
			Update("`order`", order+index).
			Error
		if err != nil {
			tx.Rollback()
			return err
		}
	}
	tx.Commit()
	return nil
}

// SortProblemsetProblems .
func (r *ProblemsetRepo) SortProblemsetProblems(ctx context.Context, req *v1.SortProblemsetProblemsRequest) error {
	min := math.MaxInt
	for _, v := range req.Ids {
		if min > int(v.Order) {
			min = int(v.Order)
		}
	}
	tx := r.data.db.WithContext(ctx).Begin()
	for index, item := range req.Ids {
		// 没有变化的不用调整
		if min+index == int(item.Order) {
			continue
		}
		err := tx.Model(&ProblemsetProblem{ID: int(item.Id)}).
			Update("`order`", min+index).
			Error
		if err != nil {
			tx.Rollback()
			return err
		}
	}
	tx.Commit()
	return nil
}

// CreateProblemsetAnswer 创建题单回答
func (r *ProblemsetRepo) CreateProblemsetAnswer(ctx context.Context, answer *biz.ProblemsetAnswer) (*biz.ProblemsetAnswer, error) {
	create := &ProblemsetAnswer{
		ProblemsetID: answer.ProblemsetID,
		UserID:       answer.UserID,
	}
	err := r.data.db.WithContext(ctx).
		Create(create).Error
	answer.ID = create.ID
	answer.CreatedAt = create.CreatedAt
	return answer, err
}

// ListProblemsetAnswers .
func (r *ProblemsetRepo) ListProblemsetAnswers(ctx context.Context, req *v1.ListProblemsetAnswersRequest) ([]*biz.ProblemsetAnswer, int64) {
	var rv []*ProblemsetAnswer
	var count int64
	r.data.db.WithContext(ctx).
		Model(&ProblemsetAnswer{}).
		Where("problemset_id = ?", req.Id).
		Count(&count).
		Order("id desc").
		Find(&rv)
	var res []*biz.ProblemsetAnswer
	for _, v := range rv {
		res = append(res, &biz.ProblemsetAnswer{
			ID:                   v.ID,
			ProblemsetID:         v.ProblemsetID,
			UserID:               v.UserID,
			Answer:               v.Answer,
			AnsweredProblemIDs:   v.AnsweredProblemIDs,
			UnansweredProblemIDs: v.UnansweredProblemIDs,
			CorrectProblemIDs:    v.CorrectProblemIDs,
			WrongProblemIDs:      v.WrongProblemIDs,
			SubmittedAt:          v.SubmittedAt,
			CreatedAt:            v.CreatedAt,
		})
	}
	return res, count
}

// GetProblemsetAnswer .
func (r *ProblemsetRepo) GetProblemsetAnswer(ctx context.Context, pid int, answerid int) (*biz.ProblemsetAnswer, error) {
	var v ProblemsetAnswer
	err := r.data.db.WithContext(ctx).Model(&ProblemsetAnswer{}).
		First(&v, "id = ?", answerid).
		Error
	if err != nil {
		return nil, err
	}
	answer := &biz.ProblemsetAnswer{
		ID:                   v.ID,
		ProblemsetID:         v.ProblemsetID,
		UserID:               v.UserID,
		Answer:               v.Answer,
		AnsweredProblemIDs:   v.AnsweredProblemIDs,
		UnansweredProblemIDs: v.UnansweredProblemIDs,
		CorrectProblemIDs:    v.CorrectProblemIDs,
		WrongProblemIDs:      v.WrongProblemIDs,
		SubmissionIDs:        v.SubmissionIDs,
		SubmittedAt:          v.SubmittedAt,
		CreatedAt:            v.CreatedAt,
	}
	return answer, nil
}

// UpdateProblemsetAnswer .
func (r *ProblemsetRepo) UpdateProblemsetAnswer(ctx context.Context, id int, answer *biz.ProblemsetAnswer) error {
	update := ProblemsetAnswer{
		ID:                   answer.ID,
		Answer:               answer.Answer,
		AnsweredProblemIDs:   answer.AnsweredProblemIDs,
		UnansweredProblemIDs: answer.UnansweredProblemIDs,
		CorrectProblemIDs:    answer.CorrectProblemIDs,
		WrongProblemIDs:      answer.WrongProblemIDs,
		SubmissionIDs:        answer.SubmissionIDs,
		SubmittedAt:          answer.SubmittedAt,
	}
	return r.data.db.WithContext(ctx).
		Updates(update).Error
}
