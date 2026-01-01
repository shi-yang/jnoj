package data

import (
	"context"
	"jnoj/app/interface/internal/biz"
	"jnoj/pkg/queue"

	"github.com/go-kratos/kratos/v2/log"
)

type websocketRepo struct {
	data     *Data
	consumer queue.Consumer
	log      *log.Helper
}

func NewWebSocketRepo(data *Data, logger log.Logger) biz.WebSocketRepo {
	consumer := queue.NewRedisConsumer(data.redisdb, logger)
	return &websocketRepo{
		data:     data,
		consumer: consumer,
		log:      log.NewHelper(logger),
	}
}

func (r *websocketRepo) HandlerMessageFromQueue(ctx context.Context, handler func(context.Context, []byte) error) {
	channelName := queue.ChannelKey("websocket")
	r.log.Infof("starting to subscribe channel: %s", channelName)
	err := r.consumer.Subscribe(ctx, channelName, func(data []byte) error {
		// r.log.Infof("receive message from queue, length: %d, content: %s", len(data), string(data))
		return handler(context.TODO(), data)
	})
	if err != nil {
		r.log.Errorf("failed to subscribe channel %s: %v", channelName, err)
		log.Fatal(err)
	}
	r.log.Infof("subscribed to channel: %s successfully", channelName)
}
