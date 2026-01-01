package queue

import "context"

// Publisher 发布者接口
type Publisher interface {
	// Publish 发布消息到队列
	Publish(ctx context.Context, queueName string, data []byte) error
	// PublishToChannel 发布消息到Pub/Sub频道（用于实时推送场景）
	PublishToChannel(ctx context.Context, channelName string, data []byte) error
	// Close 关闭发布者
	Close() error
}

// Consumer 消费者接口
type Consumer interface {
	// Consume 消费队列消息
	Consume(ctx context.Context, queueName string, handler func([]byte) error) error
	// ConsumeWithConcurrency 并发消费队列消息
	ConsumeWithConcurrency(ctx context.Context, queueName string, concurrency int, handler func([]byte) error) error
	// Subscribe 订阅Pub/Sub频道（用于实时推送场景）
	Subscribe(ctx context.Context, channelName string, handler func([]byte) error) error
	// Close 关闭消费者
	Close() error
}

// QueueKey 生成队列键名
func QueueKey(queueName string) string {
	return "queue:" + queueName
}

// ChannelKey 生成频道键名
func ChannelKey(channelName string) string {
	return "channel:" + channelName
}

