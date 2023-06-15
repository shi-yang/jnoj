package server

import (
	"context"
	v1 "jnoj/api/admin/v1"
	"jnoj/app/admin/internal/conf"
	"jnoj/app/admin/internal/service"
	"jnoj/internal/middleware/auth"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-kratos/kratos/v2/middleware"
	"github.com/go-kratos/kratos/v2/middleware/auth/jwt"
	"github.com/go-kratos/kratos/v2/middleware/recovery"
	"github.com/go-kratos/kratos/v2/transport/http"
	"github.com/gorilla/handlers"
)

// NewHTTPServer new an HTTP server.
func NewHTTPServer(c *conf.Server,
	user *service.UserService,
	submission *service.SubmissionService,
	admin *service.AdminService,
	logger log.Logger,
) *http.Server {
	var opts = []http.ServerOption{
		http.Middleware(
			recovery.Recovery(),
			auth.User(),
			AdminAuth(),
		),
		http.Filter(handlers.CORS(
			handlers.AllowedOrigins([]string{"*"}),
			handlers.AllowedMethods([]string{"GET", "POST", "PUT", "DELETE", "PATCH"}),
			handlers.AllowedHeaders([]string{"Origin", "Content-Length", "Content-Type", "Authorization"}),
		)),
	}
	if c.Http.Network != "" {
		opts = append(opts, http.Network(c.Http.Network))
	}
	if c.Http.Addr != "" {
		opts = append(opts, http.Address(c.Http.Addr))
	}
	if c.Http.Timeout != nil {
		opts = append(opts, http.Timeout(c.Http.Timeout.AsDuration()))
	}
	srv := http.NewServer(opts...)
	v1.RegisterUserServiceHTTPServer(srv, user)
	v1.RegisterSubmissionServiceHTTPServer(srv, submission)
	v1.RegisterAdminServiceHTTPServer(srv, admin)
	return srv
}

func AdminAuth() middleware.Middleware {
	return func(handler middleware.Handler) middleware.Handler {
		return func(ctx context.Context, req interface{}) (interface{}, error) {
			uid, _ := auth.GetUserID(ctx)
			if uid != 1 {
				return nil, jwt.ErrTokenInvalid
			}
			return handler(ctx, req)
		}
	}
}
