package data

import (
	"context"
	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/biz"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

type adminRepo struct {
	data *Data
	log  *log.Helper
}

// NewAdminRepo .
func NewAdminRepo(data *Data, logger log.Logger) biz.AdminRepo {
	return &adminRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

func (r *adminRepo) AnalyticsUserActivities(ctx context.Context) *v1.AnalyticsUserActivitiesResponse {
	res := new(v1.AnalyticsUserActivitiesResponse)
	var (
		start, end time.Time
	)
	start = time.Now().AddDate(-1, 1, 0)
	end = time.Now()
	db := r.data.db.WithContext(ctx).
		Select("DATE_FORMAT(created_at, '%Y-%m-%d') as date, count(*)").
		Table("submission").
		Where("created_at >= ? and created_at < ?", start, end)
	db.Group("date")
	rows, _ := db.Rows()
	for rows.Next() {
		var r v1.AnalyticsUserActivitiesResponse_Calendar
		rows.Scan(&r.Date, &r.Count)
		res.SubmissionCount = append(res.SubmissionCount, &r)
	}

	db2 := r.data.db.WithContext(ctx).
		Select("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(DISTINCT user_id)").
		Table("submission").
		Where("created_at >= ? and created_at < ?", start, end)
	db2.Group("date")
	rows, _ = db2.Rows()
	for rows.Next() {
		var r v1.AnalyticsUserActivitiesResponse_Calendar
		rows.Scan(&r.Date, &r.Count)
		res.UserCount = append(res.UserCount, &r)
	}
	return res
}
