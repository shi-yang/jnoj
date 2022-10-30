package biz

import (
	"context"
	v1 "jnoj/api/interface/v1"

	"github.com/go-kratos/kratos/v2/log"
)

// {{.Name}} is a {{.Name}} model.
type {{.Name}} struct {
	ID   int
	Name string
}

// {{.Name}}Repo is a {{.Name}} repo.
type {{.Name}}Repo interface {
	List{{.Name}}s(context.Context, *v1.List{{.Name}}sRequest) ([]*{{.Name}}, int64)
	Get{{.Name}}(context.Context, int) (*{{.Name}}, error)
	Create{{.Name}}(context.Context, *{{.Name}}) (*{{.Name}}, error)
	Update{{.Name}}(context.Context, *{{.Name}}) (*{{.Name}}, error)
	Delete{{.Name}}(context.Context, int) error
}

// {{.Name}}Usecase is a {{.Name}} usecase.
type {{.Name}}Usecase struct {
	repo {{.Name}}Repo
	log  *log.Helper
}

// New{{.Name}}Usecase new a {{.Name}} usecase.
func New{{.Name}}Usecase(repo {{.Name}}Repo, logger log.Logger) *{{.Name}}Usecase {
	return &{{.Name}}Usecase{repo: repo, log: log.NewHelper(logger)}
}

// List{{.Name}}s list {{.Name}}
func (uc *{{.Name}}Usecase) List{{.Name}}s(ctx context.Context, req *v1.List{{.Name}}sRequest) ([]*{{.Name}}, int64) {
	return uc.repo.List{{.Name}}s(ctx, req)
}

// Get{{.Name}} get a {{.Name}}
func (uc *{{.Name}}Usecase) Get{{.Name}}(ctx context.Context, id int) (*{{.Name}}, error) {
	return uc.repo.Get{{.Name}}(ctx, id)
}

// Create{{.Name}} creates a {{.Name}}, and returns the new {{.Name}}.
func (uc *{{.Name}}Usecase) Create{{.Name}}(ctx context.Context, g *{{.Name}}) (*{{.Name}}, error) {
	return uc.repo.Create{{.Name}}(ctx, g)
}

// Update{{.Name}} update a {{.Name}}
func (uc *{{.Name}}Usecase) Update{{.Name}}(ctx context.Context, p *{{.Name}}) (*{{.Name}}, error) {
	return uc.repo.Update{{.Name}}(ctx, p)
}

// Delete{{.Name}} delete a {{.Name}}
func (uc *{{.Name}}Usecase) Delete{{.Name}}(ctx context.Context, id int) error {
	return uc.repo.Delete{{.Name}}(ctx, id)
}
