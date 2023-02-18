package data

import (
	"context"
	"jnoj/app/interface/internal/biz"
	"log"

	"github.com/wagslane/go-rabbitmq"
)

type websocketRepo struct {
	data *Data
}

func NewWebSocketRepo(data *Data) biz.WebSocketRepo {
	return &websocketRepo{
		data: data,
	}
}

func (r *websocketRepo) HandlerMessageFromQueue(ctx context.Context, handler func(context.Context, []byte) error) {
	_, err := rabbitmq.NewConsumer(
		r.data.mqConn,
		func(d rabbitmq.Delivery) rabbitmq.Action {
			handler(context.TODO(), d.Body)
			return rabbitmq.Ack
		},
		"websocket",
		rabbitmq.WithConsumerOptionsRoutingKey("websocket"),
		rabbitmq.WithConsumerOptionsExchangeName("websocket"),
		rabbitmq.WithConsumerOptionsExchangeDeclare,
	)
	if err != nil {
		log.Fatal(err)
	}
}
