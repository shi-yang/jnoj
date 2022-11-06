package data

import (
	"context"

	"jnoj/app/interface/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
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

func (r *userRepo) CreateUser(ctx context.Context, g *biz.User) (*biz.User, error) {
	return g, nil
}

func (r *userRepo) Update(ctx context.Context, g *biz.User) (*biz.User, error) {
	return g, nil
}

func (r *userRepo) FindByID(context.Context, int) (*biz.User, error) {
	return nil, nil
}

func (r *userRepo) FindByUsername(context.Context, string) (*biz.User, error) {
	return nil, nil
}
