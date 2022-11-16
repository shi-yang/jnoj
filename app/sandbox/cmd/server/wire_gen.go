// Code generated by Wire. DO NOT EDIT.

//go:generate go run github.com/google/wire/cmd/wire
//go:build !wireinject
// +build !wireinject

package main

import (
	"github.com/go-kratos/kratos/v2"
	"github.com/go-kratos/kratos/v2/log"
	"jnoj/app/sandbox/internal/biz"
	"jnoj/app/sandbox/internal/conf"
	"jnoj/app/sandbox/internal/data"
	"jnoj/app/sandbox/internal/server"
	"jnoj/app/sandbox/internal/service"
)

import (
	_ "go.uber.org/automaxprocs"
)

// Injectors from wire.go:

// wireApp init kratos application.
func wireApp(confServer *conf.Server, confData *conf.Data, sandbox *conf.Sandbox, registry *conf.Registry, logger log.Logger) (*kratos.App, func(), error) {
	dataData, cleanup, err := data.NewData(confData, logger)
	if err != nil {
		return nil, nil, err
	}
	sandboxRepo := data.NewSandboxRepo(dataData, logger)
	sandboxUsecase := biz.NewSandboxUsecase(sandbox, sandboxRepo, logger)
	submissionRepo := data.NewSubmissionRepo(dataData, logger)
	submissionUsecase := biz.NewSubmissionUsecase(submissionRepo, logger)
	sandboxService := service.NewSandboxService(sandboxUsecase, submissionUsecase)
	grpcServer := server.NewGRPCServer(confServer, sandboxService, logger)
	registrar := server.NewRegistrar(registry)
	app := newApp(logger, grpcServer, registrar)
	return app, func() {
		cleanup()
	}, nil
}
