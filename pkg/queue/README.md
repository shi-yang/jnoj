# Queue 队列包

这是一个通用的消息队列接口包，定义了 `Publisher` 和 `Consumer` 接口，支持多种实现方式（如 Redis、内存队列等）。

## 核心概念

### 两种消息传递模式

#### 1. Queue（队列模式）- `Publish` / `Consume`

**特点**：
- ✅ **点对点（Point-to-Point）**：一条消息只能被一个消费者处理
- ✅ **任务分发**：适合需要负载均衡的任务处理
- ✅ **消息持久化**：消息存储在队列中，即使没有消费者也会保留
- ✅ **顺序保证**：FIFO（先进先出）

**实现方式**：使用 Redis Streams 的 `XADD` + `XREADGROUP` + `XACK`

**使用场景**：
- 提交测评任务分发
- 异步任务处理
- 需要避免重复处理的任务

**示例**：
```go
// 发布：提交ID = 123 到测评队列
publisher.Publish(ctx, queue.QueueKey("submission"), []byte("123"))

// 消费：多个 worker 并发消费，但每个任务只被一个 worker 处理
consumer.ConsumeWithConcurrency(ctx, queue.QueueKey("submission"), concurrency, func(data []byte) error {
    submissionId, _ := strconv.Atoi(string(data))
    // 处理测评任务
    return nil
})
```

**示意图**：
```
Producer → [Queue: submission] → Worker1 (处理任务1)
                              → Worker2 (处理任务2)  
                              → Worker3 (处理任务3)
```
每个任务只被一个 worker 处理，实现负载均衡。

---

#### 2. Channel（发布订阅模式）- `PublishToChannel` / `Subscribe`

**特点**：
- ✅ **广播（Broadcast）**：一条消息可以被多个订阅者同时接收
- ✅ **实时推送**：适合需要实时通知多个客户端的场景
- ✅ **消息不持久化**：如果没有订阅者，消息会丢失
- ✅ **解耦**：发布者不需要知道有多少订阅者

**实现方式**：使用 Redis Pub/Sub 的 `PUBLISH` + `SUBSCRIBE`

**使用场景**：
- WebSocket 实时消息推送
- 系统通知广播
- 事件发布订阅

**示例**：
```go
// 发布：用户ID = 456 的测评结果
publisher.PublishToChannel(ctx, queue.ChannelKey("websocket"), resultData)

// 订阅：用户456的所有WebSocket连接都在监听
consumer.Subscribe(ctx, queue.ChannelKey("websocket"), func(data []byte) error {
    // 推送给所有订阅的WebSocket连接
    return nil
})
```

**示意图**：
```
Producer → [Channel: websocket] → WebSocket连接1 (收到消息)
                               → WebSocket连接2 (收到消息)
                               → WebSocket连接3 (收到消息)
                               → WebSocket连接N (收到消息)
```
一条消息会被所有订阅者接收。

---

## 接口定义

### Publisher 接口

```go
type Publisher interface {
    // Publish 发布消息到队列
    Publish(ctx context.Context, queueName string, data []byte) error
    // PublishToChannel 发布消息到Pub/Sub频道（用于实时推送场景）
    PublishToChannel(ctx context.Context, channelName string, data []byte) error
    // Close 关闭发布者
    Close() error
}
```

### Consumer 接口

```go
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
```

## 实现

### Redis 实现（使用 Streams）

当前提供基于 `github.com/redis/go-redis/v9` 的实现，使用 **Redis Streams**：

**优势**：
- ✅ **消费者组支持**：天然支持多实例、多 worker
- ✅ **消息确认机制**：使用 `XACK` 确认，失败消息进入 pending 状态
- ✅ **可观测性**：可以查询 pending 消息，支持重试
- ✅ **优雅关闭**：使用 `context.CancelFunc`，避免重复关闭 panic

```go
// 创建 Redis Publisher
publisher := queue.NewRedisPublisher(redisClient, logger)

// 创建 Redis Consumer
consumer := queue.NewRedisConsumer(redisClient, logger)
```

**技术细节**：
- `Publish`: 使用 `XADD` 写入 Stream
- `Consume`: 使用 `XREADGROUP` 读取消息，支持消费者组
- `XAck`: 处理成功后确认消息，失败消息保留在 pending 状态
- `Close`: 使用 `context.CancelFunc`，可以安全地多次调用

## 工具函数

### QueueKey

生成队列键名，统一队列命名规范：

```go
queueName := queue.QueueKey("submission")  // 返回: "queue:submission"
```

### ChannelKey

生成频道键名，统一频道命名规范：

```go
channelName := queue.ChannelKey("websocket")  // 返回: "channel:websocket"
```

## 使用示例

### 任务队列示例（Queue）

```go
// 发布任务
publisher := queue.NewRedisPublisher(redisClient, logger)
err := publisher.Publish(ctx, queue.QueueKey("submission"), []byte("123"))

// 消费任务（单线程）
consumer := queue.NewRedisConsumer(redisClient, logger)
consumer.Consume(ctx, queue.QueueKey("submission"), func(data []byte) error {
    // 处理任务
    return nil
})

// 消费任务（并发）
consumer.ConsumeWithConcurrency(ctx, queue.QueueKey("submission"), 4, func(data []byte) error {
    // 并发处理任务
    return nil
})
```

### 实时推送示例（Channel）

```go
// 发布消息
publisher := queue.NewRedisPublisher(redisClient, logger)
err := publisher.PublishToChannel(ctx, queue.ChannelKey("websocket"), messageData)

// 订阅消息
consumer := queue.NewRedisConsumer(redisClient, logger)
consumer.Subscribe(ctx, queue.ChannelKey("websocket"), func(data []byte) error {
    // 推送给所有订阅者
    return nil
})
```

## 模式选择指南

| 场景 | 使用模式 | 原因 |
|------|---------|------|
| 提交测评任务 | Queue (`Publish`) | 一个提交只需要被一个 worker 处理，避免重复测评 |
| WebSocket 推送 | Channel (`PublishToChannel`) | 多个用户可能同时在线，都需要收到测评结果通知 |
| 异步任务处理 | Queue (`Publish`) | 需要负载均衡，避免重复处理 |
| 系统通知广播 | Channel (`PublishToChannel`) | 需要通知多个客户端 |
| 事件发布订阅 | Channel (`PublishToChannel`) | 解耦发布者和订阅者 |

## 扩展实现

如果需要添加其他实现（如内存队列、Kafka 等），只需：

1. 实现 `Publisher` 和 `Consumer` 接口
2. 提供构造函数
3. 在业务代码中切换使用的实现

示例：
```go
// pkg/queue/memory.go
type MemoryPublisher struct { ... }
func NewMemoryPublisher() *MemoryPublisher { ... }
```

## 注意事项

1. **Queue 模式**：消息会持久化，适合重要任务
2. **Channel 模式**：消息不持久化，订阅者离线会丢失消息
3. **并发消费**：使用 `ConsumeWithConcurrency` 时注意 handler 的线程安全
4. **错误处理**：handler 返回错误时，根据实现不同可能有不同的处理策略

