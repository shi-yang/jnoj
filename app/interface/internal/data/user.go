package data

import (
	"context"
	"errors"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"
	"jnoj/pkg/pagination"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

type userRepo struct {
	data *Data
	log  *log.Helper
}

// NewUserRepo .
func NewUserRepo(data *Data, logger log.Logger) biz.UserRepo {
	return &userRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

type User struct {
	ID        int
	Username  string
	Nickname  string
	Realname  string
	Password  string
	Email     string
	Phone     string
	Role      int
	Status    int
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt
}

func (r *userRepo) GetUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	res := User{}
	err := r.data.db.WithContext(ctx).
		Where(&User{
			Username: u.Username,
			Email:    u.Email,
			Phone:    u.Phone,
		}).
		First(&res).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.User{
		ID:       res.ID,
		Username: res.Username,
		Nickname: res.Nickname,
		Realname: res.Realname,
		Email:    res.Email,
		Phone:    res.Phone,
		Role:     res.Role,
		Status:   res.Status,
		Password: res.Password,
	}, nil
}

func (r *userRepo) CreateUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	res := User{
		Username: u.Username,
		Password: u.Password,
		Email:    u.Email,
		Nickname: u.Nickname,
		Phone:    u.Phone,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.User{
		ID: res.ID,
	}, err
}

func (r *userRepo) UpdateUser(ctx context.Context, u *biz.User) (*biz.User, error) {
	update := User{
		ID:       u.ID,
		Nickname: u.Nickname,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&update).Error
	return u, err
}

func (r *userRepo) FindByID(ctx context.Context, id int) (*biz.User, error) {
	var o User
	err := r.data.db.WithContext(ctx).
		First(&o, "id = ?", id).
		Error
	if err != nil {
		return nil, err
	}
	return &biz.User{
		ID:       o.ID,
		Username: o.Username,
		Nickname: o.Nickname,
		Realname: o.Realname,
		Role:     o.Role,
		Status:   o.Status,
		Password: o.Password,
	}, nil
}

func (r *userRepo) GetUserProfileCalendar(ctx context.Context, req *v1.GetUserProfileCalendarRequest) (*v1.GetUserProfileCalendarResponse, error) {
	res := new(v1.GetUserProfileCalendarResponse)
	var (
		start, end time.Time
	)
	if req.Year == 0 {
		end = time.Now()
		start = end.AddDate(-1, 0, 0)
	} else {
		start = time.Date(int(req.Year), 1, 1, 0, 0, 0, 0, time.UTC)
		end = time.Date(int(req.Year)+1, 1, 1, 0, 0, 0, 0, time.UTC)
	}
	res.Start = start.Format("2006/01/02")
	res.End = end.Format("2006/01/02")
	db := r.data.db.WithContext(ctx).
		Select("DATE_FORMAT(created_at, '%Y/%m/%d') as date, count(*)").
		Table("submission").
		Where("user_id = ? and entity_type = ?", req.Id, biz.SubmissionEntityTypeProblemset).
		Where("created_at >= ? and created_at < ?", start, end)
	db.Group("date")
	rows, _ := db.Rows()
	for rows.Next() {
		var r v1.GetUserProfileCalendarResponse_ProfileCalendar
		rows.Scan(&r.Date, &r.Count)
		res.TotalSubmission += r.Count
		res.TotalActiveDays++
		res.SubmissionCalendar = append(res.SubmissionCalendar, &r)
	}
	r.data.db.WithContext(ctx).
		Select("year(created_at) as date").
		Table("submission").
		Where("user_id = ? and entity_type = ?", req.Id, biz.SubmissionEntityTypeProblemset).
		Group("date").
		Scan(&res.ActiveYears)
	r.data.db.WithContext(ctx).
		Select("COUNT(DISTINCT(problem_id))").
		Table("submission").
		Where("user_id = ? and entity_type = ?", req.Id, biz.SubmissionEntityTypeProblemset).
		Where("verdict = ?", biz.SubmissionVerdictAccepted).
		Where("created_at >= ? and created_at < ?", start, end).
		Scan(&res.TotalProblemSolved)
	return res, nil
}

func (r *userRepo) GetUserProfileProblemsetProblemSolved(ctx context.Context, uid int) (*v1.GetUserProfileProblemSolvedResponse, error) {
	res := new(v1.GetUserProfileProblemSolvedResponse)
	// 题目数量统计
	var submissions []struct {
		ProblemID int
		Verdict   int
	}
	r.data.db.WithContext(ctx).
		Select("problem_id, SUM(case when verdict = ? then 1 else 0 end) as verdict", biz.SubmissionVerdictAccepted).
		Table("submission").
		Where("user_id = ? and entity_type = ?", uid, biz.SubmissionEntityTypeProblemset).
		Group("problem_id").
		Scan(&submissions)
	mp := make(map[int]v1.GetUserProfileProblemSolvedResponse_Problem_Status)
	for _, v := range submissions {
		if v.Verdict == 0 {
			mp[v.ProblemID] = v1.GetUserProfileProblemSolvedResponse_Problem_INCORRECT
		} else {
			mp[v.ProblemID] = v1.GetUserProfileProblemSolvedResponse_Problem_CORRECT
		}
	}

	var problemsets []Problemset
	r.data.db.WithContext(ctx).
		Model(&Problemset{}).
		Preload("ProblemsetProblems", func(db *gorm.DB) *gorm.DB {
			return db.Select("`order`, problem_id, problemset_id")
		}).
		Find(&problemsets)
	for _, problemset := range problemsets {
		var s v1.GetUserProfileProblemSolvedResponse_Problemset
		for _, problem := range problemset.ProblemsetProblems {
			if mp[problem.ProblemID] == v1.GetUserProfileProblemSolvedResponse_Problem_CORRECT {
				s.Count++
			}
			s.Problems = append(s.Problems, &v1.GetUserProfileProblemSolvedResponse_Problem{
				Id:     int32(problem.Order),
				Status: mp[problem.ProblemID],
			})
		}
		s.Total = int32(len(problemset.ProblemsetProblems))
		s.Name = problemset.Name
		s.Id = int32(problemset.ID)
		res.Problemsets = append(res.Problemsets, &s)
	}
	return res, nil
}

func (r *userRepo) GetUserProfileContestProblemSolved(ctx context.Context, uid int, page, pageSize int) (*v1.GetUserProfileProblemSolvedResponse, error) {
	res := new(v1.GetUserProfileProblemSolvedResponse)
	// 题目数量统计
	var submissions []struct {
		ProblemID int
		Verdict   int
	}
	r.data.db.WithContext(ctx).
		Select("problem_id, SUM(case when verdict = ? then 1 else 0 end) as verdict", biz.SubmissionVerdictAccepted).
		Table("submission").
		Where("user_id = ? and entity_type = ?", uid, biz.SubmissionEntityTypeContest).
		Group("problem_id").
		Scan(&submissions)
	mp := make(map[int]v1.GetUserProfileProblemSolvedResponse_Problem_Status)
	for _, v := range submissions {
		if v.Verdict == 0 {
			mp[v.ProblemID] = v1.GetUserProfileProblemSolvedResponse_Problem_INCORRECT
		} else {
			mp[v.ProblemID] = v1.GetUserProfileProblemSolvedResponse_Problem_CORRECT
		}
	}

	userContestSubQuery := r.data.db.WithContext(ctx).Select("DISTINCT(contest_id)").Model(&ContestUser{}).Where("user_id = ?", uid)
	var contests []Contest
	pager := pagination.NewPagination(int32(page), int32(pageSize))
	db := r.data.db.WithContext(ctx).
		Model(&Contest{}).
		Where("id in (?)", userContestSubQuery).
		Where("(type = ? and end_time < ?) or type != ?", biz.ContestTypeOI, time.Now(), biz.ContestTypeOI).
		Preload("Group", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, name")
		}).
		Preload("ContestProblems", func(db *gorm.DB) *gorm.DB {
			return db.Select("number, problem_id, contest_id")
		})
	db.Count(&res.Total)
	db.Order("id desc").
		Offset(pager.GetOffset()).
		Limit(pager.GetPageSize()).
		Find(&contests)
	for _, contest := range contests {
		var s v1.GetUserProfileProblemSolvedResponse_Contest
		for _, problem := range contest.ContestProblems {
			if mp[problem.ProblemID] == v1.GetUserProfileProblemSolvedResponse_Problem_CORRECT {
				s.Count++
			}
			s.Problems = append(s.Problems, &v1.GetUserProfileProblemSolvedResponse_Problem{
				Id:     int32(problem.Number),
				Status: mp[problem.ProblemID],
			})
		}
		s.Total = int32(len(contest.ContestProblems))
		s.Name = contest.Name
		s.Id = int32(contest.ID)
		if contest.Group != nil {
			s.GroupId = int32(contest.Group.ID)
			s.GroupName = contest.Group.Name
		}
		res.Contests = append(res.Contests, &s)
	}
	return res, nil
}

func (r *userRepo) GetUserProfileGroupProblemSolved(ctx context.Context, uid int, page, pageSize int) (*v1.GetUserProfileProblemSolvedResponse, error) {
	res := new(v1.GetUserProfileProblemSolvedResponse)
	// 题目数量统计
	var submissions []struct {
		ProblemID int
		Verdict   int
	}
	r.data.db.WithContext(ctx).
		Select("problem_id, SUM(case when verdict = ? then 1 else 0 end) as verdict", biz.SubmissionVerdictAccepted).
		Table("submission").
		Where("user_id = ? and entity_type = ?", uid, biz.SubmissionEntityTypeContest).
		Group("problem_id").
		Scan(&submissions)
	mp := make(map[int]v1.GetUserProfileProblemSolvedResponse_Problem_Status)
	for _, v := range submissions {
		if v.Verdict == 0 {
			mp[v.ProblemID] = v1.GetUserProfileProblemSolvedResponse_Problem_INCORRECT
		} else {
			mp[v.ProblemID] = v1.GetUserProfileProblemSolvedResponse_Problem_CORRECT
		}
	}

	userGroupSubQuery := r.data.db.WithContext(ctx).Select("DISTINCT(group_id)").Model(&GroupUser{}).Where("user_id = ?", uid)
	var groups []Group
	pager := pagination.NewPagination(int32(page), int32(pageSize))
	db := r.data.db.WithContext(ctx).
		Model(&Group{}).
		Where("id in (?)", userGroupSubQuery).
		Preload("Contests", func(db *gorm.DB) *gorm.DB {
			return db.Select("id, name, group_id").Where("(type = ? and end_time < ?) or type != ?", biz.ContestTypeOI, time.Now(), biz.ContestTypeOI)
		}).
		Preload("Contests.ContestProblems", func(db *gorm.DB) *gorm.DB {
			return db.Select("number, problem_id, contest_id")
		})
	db.Count(&res.Total)
	db.Order("id desc").
		Offset(pager.GetOffset()).
		Limit(pager.GetPageSize()).
		Find(&groups)
	for _, group := range groups {
		g := v1.GetUserProfileProblemSolvedResponse_Group{
			Id:   int32(group.ID),
			Name: group.Name,
		}
		// 统计比赛
		for _, contest := range group.Contests {
			// 统计比赛的题目
			c := v1.GetUserProfileProblemSolvedResponse_Contest{
				Id:    int32(contest.ID),
				Name:  contest.Name,
				Total: int32(len(contest.ContestProblems)),
			}
			for _, problem := range contest.ContestProblems {
				if mp[problem.ProblemID] == v1.GetUserProfileProblemSolvedResponse_Problem_CORRECT {
					c.Count++
				}
				c.Problems = append(c.Problems, &v1.GetUserProfileProblemSolvedResponse_Problem{
					Id:     int32(problem.Number),
					Status: mp[problem.ProblemID],
				})
			}
			g.Total += c.Total
			g.Count += c.Count
			g.Contests = append(g.Contests, &c)
		}
		res.Groups = append(res.Groups, &g)
	}
	return res, nil
}

// GetCaptcha 获取验证码
func (r *userRepo) GetCaptcha(ctx context.Context, key string) (string, error) {
	val, err := r.data.redisdb.Get(ctx, key).Result()
	if err != nil {
		return "", err
	}
	return val, nil
}

// SaveCaptcha 保存验证码，5分钟
func (r *userRepo) SaveCaptcha(ctx context.Context, key string, value string) error {
	return r.data.redisdb.Set(ctx, key, value, time.Minute*5).Err()
}

// GetUserProfileCount 用户主页-统计
func (r *userRepo) GetUserProfileCount(ctx context.Context, uid int) (*v1.GetUserProfileCountResponse, error) {
	res := new(v1.GetUserProfileCountResponse)
	// 查询用户竞赛等级分
	var contestUser ContestUser
	err := r.data.db.WithContext(ctx).
		Select("new_rating").
		Model(&ContestUser{}).
		Where("user_id = ?", uid).
		Where("rated_at is not null").
		Order("rated_at desc").
		First(&contestUser).Error
	if errors.Is(err, gorm.ErrRecordNotFound) {
		res.ContestRating = 0
	} else {
		res.ContestRating = int32(contestUser.NewRating)
	}
	// 查询用户竞赛等级分历史记录
	if res.ContestRating != 0 {
		var contestUsers []*ContestUser
		r.data.db.WithContext(ctx).
			Model(&ContestUser{}).
			Preload("Contest", func(db *gorm.DB) *gorm.DB {
				return db.Select("id, name")
			}).
			Where("user_id = ?", uid).
			Where("rated_at is not null").
			Order("rated_at").
			Find(&contestUsers)
		for _, v := range contestUsers {
			res.ContestRankingHistory = append(res.ContestRankingHistory, &v1.GetUserProfileCountResponse_ContestRanking{
				ContestId: int32(v.Contest.ID),
				Name:      v.Contest.Name,
				Rating:    int32(v.NewRating),
			})
		}
	}

	// 查询用户解答数，用户解答数不包含未结束竞赛
	r.data.db.WithContext(ctx).
		Select("COUNT(DISTINCT submission.problem_id)").
		Model(&Submission{}).
		Joins("LEFT JOIN contest ON contest.id = submission.entity_id").
		Where("submission.user_id = ?", uid).
		Where("entity_type = ? or (entity_type = ? and contest.end_time < ?)", biz.SubmissionEntityTypeProblemset, biz.SubmissionEntityTypeContest, time.Now()).
		Scan(&res.ProblemSolved)
	return res, nil
}
