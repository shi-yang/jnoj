package queue

import (
	"context"
	"fmt"
	"sync"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/redis/go-redis/v9"
)

// RedisPublisher Redis发布者实现（使用 Streams）
type RedisPublisher struct {
	client *redis.Client
	logger *log.Helper
}

// NewRedisPublisher 创建Redis发布者
func NewRedisPublisher(client *redis.Client, logger log.Logger) *RedisPublisher {
	return &RedisPublisher{
		client: client,
		logger: log.NewHelper(logger),
	}
}

// Publish 发布消息到队列（使用 Redis Streams XADD）
func (p *RedisPublisher) Publish(ctx context.Context, queueName string, data []byte) error {
	// 使用 XADD 写入 Stream
	_, err := p.client.XAdd(ctx, &redis.XAddArgs{
		Stream: queueName,
		Values: map[string]interface{}{
			"data": string(data), // Redis Streams 需要 string 类型
		},
	}).Result()
	if err != nil {
		p.logger.Errorf("failed to publish message to queue %s: %v", queueName, err)
		return err
	}
	return nil
}

// PublishToChannel 发布消息到Redis Pub/Sub频道
func (p *RedisPublisher) PublishToChannel(ctx context.Context, channelName string, data []byte) error {
	err := p.client.Publish(ctx, channelName, data).Err()
	if err != nil {
		p.logger.Errorf("failed to publish message to channel %s: %v", channelName, err)
		return err
	}
	return nil
}

// Close 关闭发布者
func (p *RedisPublisher) Close() error {
	return nil
}

// RedisConsumer Redis消费者实现（使用 Streams）
type RedisConsumer struct {
	client    *redis.Client
	logger    *log.Helper
	ctx       context.Context
	cancel    context.CancelFunc
	wg        sync.WaitGroup
	groupName string // 消费者组名称
}

// NewRedisConsumer 创建Redis消费者
func NewRedisConsumer(client *redis.Client, logger log.Logger) *RedisConsumer {
	ctx, cancel := context.WithCancel(context.Background())
	return &RedisConsumer{
		client:    client,
		logger:    log.NewHelper(logger),
		ctx:       ctx,
		cancel:    cancel,
		groupName: "default", // 默认消费者组
	}
}

// Consume 消费队列消息（使用 Redis Streams XREADGROUP）
func (c *RedisConsumer) Consume(ctx context.Context, queueName string, handler func([]byte) error) error {
	// 确保消费者组存在
	err := c.ensureConsumerGroup(ctx, queueName)
	if err != nil {
		c.logger.Errorf("failed to ensure consumer group for queue %s: %v", queueName, err)
		return err
	}

	consumerName := fmt.Sprintf("consumer-%d", time.Now().UnixNano())
	c.wg.Add(1)
	go func() {
		defer c.wg.Done()
		for {
			select {
			case <-c.ctx.Done():
				c.logger.Infof("stopping consumer for queue %s", queueName)
				return
			case <-ctx.Done():
				c.logger.Infof("context cancelled for queue %s", queueName)
				return
			default:
				// 使用 XREADGROUP 读取消息
				streams, err := c.client.XReadGroup(ctx, &redis.XReadGroupArgs{
					Group:    c.groupName,
					Consumer: consumerName,
					Streams:  []string{queueName, ">"},
					Count:    1,
					Block:    5 * time.Second,
				}).Result()

				if err != nil {
					if err == redis.Nil {
						continue
					}
					c.logger.Errorf("failed to consume from queue %s: %v", queueName, err)
					time.Sleep(time.Second)
					continue
				}

				// 处理消息
				for _, stream := range streams {
					for _, msg := range stream.Messages {
						data, ok := msg.Values["data"].(string)
						if !ok {
							c.logger.Warnf("invalid message format in queue %s", queueName)
							// 仍然 ACK，避免重复处理
							c.client.XAck(ctx, queueName, c.groupName, msg.ID)
							continue
						}

						// 处理消息
						if err := handler([]byte(data)); err != nil {
							c.logger.Errorf("handler error for queue %s: %v", queueName, err)
							// 处理失败，不 ACK，消息会进入 pending 状态，可以重试
							continue
						}

						// 处理成功，ACK 消息
						if err := c.client.XAck(ctx, queueName, c.groupName, msg.ID).Err(); err != nil {
							c.logger.Errorf("failed to ack message %s in queue %s: %v", msg.ID, queueName, err)
						}
					}
				}
			}
		}
	}()
	return nil
}

// ConsumeWithConcurrency 并发消费队列消息（使用 Redis Streams）
func (c *RedisConsumer) ConsumeWithConcurrency(ctx context.Context, queueName string, concurrency int, handler func([]byte) error) error {
	if concurrency <= 0 {
		concurrency = 1
	}

	// 确保消费者组存在
	err := c.ensureConsumerGroup(ctx, queueName)
	if err != nil {
		c.logger.Errorf("failed to ensure consumer group for queue %s: %v", queueName, err)
		return err
	}

	for i := 0; i < concurrency; i++ {
		workerID := i
		consumerName := fmt.Sprintf("consumer-%d-%d", time.Now().UnixNano(), workerID)
		c.wg.Add(1)
		go func() {
			defer c.wg.Done()
			for {
				select {
				case <-c.ctx.Done():
					c.logger.Infof("stopping consumer worker %d for queue %s", workerID, queueName)
					return
				case <-ctx.Done():
					c.logger.Infof("context cancelled for worker %d queue %s", workerID, queueName)
					return
				default:
					// 使用 XREADGROUP 读取消息
					streams, err := c.client.XReadGroup(ctx, &redis.XReadGroupArgs{
						Group:    c.groupName,
						Consumer: consumerName,
						Streams:  []string{queueName, ">"},
						Count:    1,
						Block:    5 * time.Second,
					}).Result()

					if err != nil {
						if err == redis.Nil {
							continue
						}
						c.logger.Errorf("worker %d failed to consume from queue %s: %v", workerID, queueName, err)
						time.Sleep(time.Second)
						continue
					}

					// 处理消息
					for _, stream := range streams {
						for _, msg := range stream.Messages {
							data, ok := msg.Values["data"].(string)
							if !ok {
								c.logger.Warnf("worker %d: invalid message format in queue %s", workerID, queueName)
								c.client.XAck(ctx, queueName, c.groupName, msg.ID)
								continue
							}

							// 处理消息
							if err := handler([]byte(data)); err != nil {
								c.logger.Errorf("worker %d handler error for queue %s: %v", workerID, queueName, err)
								// 处理失败，不 ACK，消息会进入 pending 状态
								continue
							}

							// 处理成功，ACK 消息
							if err := c.client.XAck(ctx, queueName, c.groupName, msg.ID).Err(); err != nil {
								c.logger.Errorf("worker %d failed to ack message %s in queue %s: %v", workerID, msg.ID, queueName, err)
							}
						}
					}
				}
			}
		}()
	}
	return nil
}

// ensureConsumerGroup 确保消费者组存在
func (c *RedisConsumer) ensureConsumerGroup(ctx context.Context, streamName string) error {
	// 先检查 Stream 是否存在
	exists, err := c.client.Exists(ctx, streamName).Result()
	if err != nil {
		return err
	}

	// 如果 Stream 不存在，先创建一个空消息来创建 Stream
	if exists == 0 {
		_, err := c.client.XAdd(ctx, &redis.XAddArgs{
			Stream: streamName,
			Values: map[string]interface{}{
				"_init": "1", // 初始化消息
			},
			MaxLen: 1, // 只保留一条消息
			Approx: true,
		}).Result()
		if err != nil {
			return fmt.Errorf("failed to create stream %s: %w", streamName, err)
		}
		c.logger.Infof("created stream: %s", streamName)
	}

	// 尝试创建消费者组，如果已存在则忽略错误
	err = c.client.XGroupCreate(ctx, streamName, c.groupName, "0").Err()
	if err != nil {
		// 如果错误是 BUSYGROUP，说明组已存在，这是正常的
		errStr := err.Error()
		if errStr == "BUSYGROUP Consumer Group name already exists" ||
			errStr == "BUSYGROUP" {
			return nil
		}
		return err
	}
	c.logger.Infof("created consumer group %s for stream %s", c.groupName, streamName)
	return nil
}

// Subscribe 订阅Redis Pub/Sub频道（保持 Pub/Sub 实现，因为 Streams 不适合广播场景）
func (c *RedisConsumer) Subscribe(ctx context.Context, channelName string, handler func([]byte) error) error {
	pubsub := c.client.Subscribe(ctx, channelName)
	c.logger.Infof("subscribing to channel: %s", channelName)

	ch := pubsub.Channel()
	c.wg.Add(1)
	go func() {
		defer c.wg.Done()
		defer pubsub.Close()
		c.logger.Infof("subscriber goroutine started for channel: %s", channelName)
		for {
			select {
			case <-c.ctx.Done():
				c.logger.Infof("stopping subscriber for channel %s", channelName)
				return
			case <-ctx.Done():
				c.logger.Infof("context cancelled for channel %s", channelName)
				return
			case msg := <-ch:
				if msg == nil {
					c.logger.Warnf("received nil message from channel %s", channelName)
					continue
				}
				c.logger.Infof("received message from channel %s, payload length: %d", channelName, len(msg.Payload))
				if err := handler([]byte(msg.Payload)); err != nil {
					c.logger.Errorf("handler error for channel %s: %v", channelName, err)
				}
			}
		}
	}()
	return nil
}

// Close 关闭消费者（使用 context.CancelFunc，可以安全地多次调用）
func (c *RedisConsumer) Close() error {
	// cancel 可以安全地多次调用，不会 panic
	c.cancel()
	c.wg.Wait()
	return nil
}
