package data

import (
	"context"
	"fmt"
	"jnoj/app/interface/internal/biz"
	"time"

	"github.com/go-kratos/kratos/v2/log"
)

type sandboxRepo struct {
	data *Data
	log  *log.Helper
}

func NewSandboxRepo(data *Data, logger log.Logger) biz.SandboxRepo {
	return &sandboxRepo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// GetUserRunPerMinute 获取用户每分钟提交次数
func (r *sandboxRepo) GetUserRunPerMinute(ctx context.Context, userID int) int {
	key := fmt.Sprintf("run_sandbox_per_minute_count:%d", userID)
	count, _ := r.data.redisdb.Incr(ctx, key).Result()
	// 设置过期时间为1分钟（如果键不存在）
	if count == 1 {
		_ = r.data.redisdb.Expire(ctx, key, time.Minute).Err()
	}
	return int(count)
}
