package main

import (
	"github.com/asim/go-micro/plugins/registry/consul/v3"
	"micro-test/handler"
	pb "micro-test/proto"

	//"github.com/micro/micro/v3/service"
	//"github.com/micro/micro/v3/service/logger"

	service "github.com/asim/go-micro/v3"
	"github.com/asim/go-micro/v3/logger"
)

func main() {
	consul.NewRegistry()
	// Create service
	srv := service.NewService(
		service.Name("micro-test"),
		service.Version("latest"),
		service.Registry(consul.NewRegistry()),
	)

	// Register handler
	pb.RegisterMicroTestHandler(srv.Server(), new(handler.MicroTest))

	// Run service
	if err := srv.Run(); err != nil {
		logger.Fatal(err)
	}
}
