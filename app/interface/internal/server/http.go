package server

import (
	v1 "jnoj/api/interface/v1"
	"jnoj/app/interface/internal/conf"
	"jnoj/app/interface/internal/service"
	"jnoj/internal/middleware/auth"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-kratos/kratos/v2/middleware/recovery"
	"github.com/go-kratos/kratos/v2/middleware/validate"
	"github.com/go-kratos/kratos/v2/transport/http"
	"github.com/gorilla/handlers"
)

// NewHTTPServer new a HTTP server.
func NewHTTPServer(c *conf.Server,
	contest *service.ContestService,
	user *service.UserService,
	problem *service.ProblemService,
	submission *service.SubmissionService,
	sandbox *service.SandboxService,
	group *service.GroupService,
	websocket *service.WebSocketService,
	ranking *service.RankingService,
	post *service.PostService,
	logger log.Logger,
) *http.Server {
	var opts = []http.ServerOption{
		http.Middleware(
			recovery.Recovery(),
			validate.Validator(),
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

	// 处理上传文件
	route := srv.Route("/")
	// TODO websocket auth
	srv.HandleFunc("/ws", websocket.WsHandler)
	route.POST("/problems/{id}/upload_test", problem.UploadProblemTest)
	route.POST("/problems/{id}/upload_file", problem.UploadProblemFile)

	// 上传图片
	route.POST("/post/upload_image", post.UploadPostImage)
	srv.Use("uploadPostImage", auth.User())

	// 下载测试点
	route.GET("/problems/{id}/test/download", problem.DownloadProblemTests)
	srv.Use("downloadProblemTests", auth.User())

	v1.RegisterContestServiceHTTPServer(srv, contest)
	v1.RegisterProblemServiceHTTPServer(srv, problem)
	v1.RegisterUserServiceHTTPServer(srv, user)
	v1.RegisterSubmissionServiceHTTPServer(srv, submission)
	v1.RegisterSandboxsServiceHTTPServer(srv, sandbox)
	v1.RegisterGroupServiceHTTPServer(srv, group)
	v1.RegisterRankingServiceHTTPServer(srv, ranking)
	v1.RegisterPostServiceHTTPServer(srv, post)
	return srv
}
