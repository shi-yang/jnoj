package data

import (
	"context"
	"time"

	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
	"gorm.io/gorm/clause"
)

type {{.Name | tolower }}Repo struct {
	data *Data
	log  *log.Helper
}

type {{.Name}} struct {
	ID        int
	Name      string
	UserID    int
	CreatedAt time.Time
}

// New{{.Name}}Repo .
func New{{.Name}}Repo(data *Data, logger log.Logger) biz.{{.Name}}Repo {
	return &{{.Name | tolower }}Repo{
		data: data,
		log:  log.NewHelper(logger),
	}
}

// List{{.Name}}s .
func (r *{{.Name | tolower }}Repo) List{{.Name}}s(ctx context.Context, req *v1.List{{.Name}}sRequest) ([]*biz.{{.Name}}, int64) {
	res := []{{.Name}}{}
	count := int64(0)
	r.data.db.WithContext(ctx).
		Find(&res).
		Count(&count)
	rv := make([]*biz.{{.Name}}, 0)
	for _, v := range res {
		rv = append(rv, &biz.{{.Name}}{
			ID: v.ID,
		})
	}
	return rv, count
}

// Get{{.Name}} .
func (r *{{.Name | tolower }}Repo) Get{{.Name}}(ctx context.Context, id int) (*biz.{{.Name}}, error) {
	var res {{.Name}}
	err := r.data.db.Model({{.Name}}{}).
		First(&res, "id = ?", id).Error
	if err != nil {
		return nil, err
	}
	return &biz.{{.Name}}{}, err
}

// Create{{.Name}} .
func (r *{{.Name | tolower }}Repo) Create{{.Name}}(ctx context.Context, b *biz.{{.Name}}) (*biz.{{.Name}}, error) {
	res := {{.Name}}{Name: b.Name}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Create(&res).Error
	return &biz.{{.Name}}{
		ID: res.ID,
	}, err
}

// Update{{.Name}} .
func (r *{{.Name | tolower }}Repo) Update{{.Name}}(ctx context.Context, b *biz.{{.Name}}) (*biz.{{.Name}}, error) {
	res := {{.Name}}{
		ID: b.ID,
	}
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Updates(&res).Error
	return nil, err
}

// Delete{{.Name}} .
func (r *{{.Name | tolower }}Repo) Delete{{.Name}}(ctx context.Context, id int) error {
	err := r.data.db.WithContext(ctx).
		Omit(clause.Associations).
		Delete({{.Name}}{ID: id}).
		Error
	return err
}
