package redis

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"math/rand/v2"
	"sync"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-kratos/kratos/v2/registry"
	"github.com/redis/go-redis/v9"
)

const (
	// 服务注册的 key 前缀
	serviceKeyPrefix = "registry:service:"
	// 服务实例的 key 前缀
	instanceKeyPrefix = "registry:instance:"
)

// Client is redis registry client
type Client struct {
	client *redis.Client

	// healthcheck time interval in seconds
	healthcheckInterval int
	// heartbeat enable heartbeat
	heartbeat bool
	// deregisterCriticalServiceAfter time interval in seconds
	deregisterCriticalServiceAfter int
	// tags is service tags
	tags []string

	// used to control heartbeat
	lock      sync.RWMutex
	cancelers map[string]*canceler
}

type canceler struct {
	ctx    context.Context
	cancel context.CancelFunc
	done   chan struct{}
}

// serviceInstance 服务实例的存储结构
type serviceInstance struct {
	ID        string            `json:"id"`
	Name      string            `json:"name"`
	Version   string            `json:"version"`
	Metadata  map[string]string `json:"metadata"`
	Endpoints []string          `json:"endpoints"`
	Timestamp int64             `json:"timestamp"`
}

// Register register service instance to redis
func (c *Client) Register(ctx context.Context, svc *registry.ServiceInstance, enableHealthCheck bool) error {
	instance := &serviceInstance{
		ID:        svc.ID,
		Name:      svc.Name,
		Version:   svc.Version,
		Metadata:  svc.Metadata,
		Endpoints: svc.Endpoints,
		Timestamp: time.Now().Unix(),
	}

	data, err := json.Marshal(instance)
	if err != nil {
		return fmt.Errorf("marshal service instance failed: %w", err)
	}

	// 服务名对应的 hash key
	serviceKey := serviceKeyPrefix + svc.Name
	// 实例 ID
	instanceID := svc.ID

	// 计算过期时间（秒）
	expireSeconds := c.healthcheckInterval * 3
	if c.deregisterCriticalServiceAfter > 0 {
		expireSeconds = c.deregisterCriticalServiceAfter
	}

	// 使用 Lua 脚本原子性地注册服务
	script := redis.NewScript(`
		local serviceKey = KEYS[1]
		local instanceID = ARGV[1]
		local instanceData = ARGV[2]
		local expireTime = tonumber(ARGV[3])
		
		redis.call('HSET', serviceKey, instanceID, instanceData)
		redis.call('EXPIRE', serviceKey, expireTime)
		
		return 1
	`)

	err = script.Run(ctx, c.client, []string{serviceKey}, instanceID, string(data), expireSeconds).Err()
	if err != nil {
		return fmt.Errorf("register service to redis failed: %w", err)
	}

	// 启动心跳
	c.lock.Lock()
	if cc, ok := c.cancelers[svc.ID]; ok {
		cc.cancel()
		<-cc.done
	}
	var cc *canceler
	if c.heartbeat {
		cancelCtx, cancel := context.WithCancel(context.Background())
		cc = &canceler{
			ctx:    cancelCtx,
			cancel: cancel,
			done:   make(chan struct{}),
		}
		c.cancelers[svc.ID] = cc
		go func() {
			<-cc.done
			cc.cancel()
			c.lock.Lock()
			if c.cancelers[svc.ID] == cc {
				delete(c.cancelers, svc.ID)
			}
			c.lock.Unlock()
		}()
	}
	c.lock.Unlock()

	if c.heartbeat {
		go c.heartbeatLoop(cc, svc, expireSeconds)
	}

	return nil
}

// heartbeatLoop 心跳循环
func (c *Client) heartbeatLoop(cc *canceler, svc *registry.ServiceInstance, expireSeconds int) {
	defer close(cc.done)

	instance := &serviceInstance{
		ID:        svc.ID,
		Name:      svc.Name,
		Version:   svc.Version,
		Metadata:  svc.Metadata,
		Endpoints: svc.Endpoints,
		Timestamp: time.Now().Unix(),
	}

	data, err := json.Marshal(instance)
	if err != nil {
		log.Errorf("[Redis] marshal service instance failed: %v", err)
		return
	}

	serviceKey := serviceKeyPrefix + svc.Name
	instanceID := svc.ID

	// 使用 Lua 脚本更新心跳
	script := redis.NewScript(`
		local serviceKey = KEYS[1]
		local instanceID = ARGV[1]
		local instanceData = ARGV[2]
		local expireTime = tonumber(ARGV[3])
		
		if redis.call('HEXISTS', serviceKey, instanceID) == 1 then
			redis.call('HSET', serviceKey, instanceID, instanceData)
			redis.call('EXPIRE', serviceKey, expireTime)
			return 1
		end
		return 0
	`)

	ticker := time.NewTicker(time.Second * time.Duration(c.healthcheckInterval))
	defer ticker.Stop()

	for {
		select {
		case <-cc.ctx.Done():
			// 注销服务
			c.deregisterService(cc.ctx, svc.Name, svc.ID)
			return
		case <-ticker.C:
			instance.Timestamp = time.Now().Unix()
			data, err = json.Marshal(instance)
			if err != nil {
				log.Errorf("[Redis] marshal service instance failed: %v", err)
				continue
			}

			result := script.Run(cc.ctx, c.client, []string{serviceKey}, instanceID, string(data), expireSeconds)
			if errors.Is(result.Err(), context.Canceled) || errors.Is(result.Err(), context.DeadlineExceeded) {
				c.deregisterService(cc.ctx, svc.Name, svc.ID)
				return
			}
			if result.Err() != nil {
				log.Errorf("[Redis] update heartbeat failed: %v", result.Err())
				// 心跳失败时尝试重新注册
				if err := sleepCtx(cc.ctx, time.Duration(rand.IntN(5))*time.Second); err != nil {
					c.deregisterService(cc.ctx, svc.Name, svc.ID)
					return
				}
				// 重新注册服务
				if err := c.Register(cc.ctx, svc, true); err != nil {
					log.Errorf("[Redis] re-register service failed: %v", err)
				} else {
					log.Warn("[Redis] re-register service success")
				}
			}
		}
	}
}

// deregisterService 注销服务
func (c *Client) deregisterService(ctx context.Context, serviceName, instanceID string) {
	serviceKey := serviceKeyPrefix + serviceName
	script := redis.NewScript(`
		local serviceKey = KEYS[1]
		local instanceID = ARGV[1]
		
		redis.call('HDEL', serviceKey, instanceID)
		
		-- 如果 hash 为空，删除 key
		if redis.call('HLEN', serviceKey) == 0 then
			redis.call('DEL', serviceKey)
		end
		
		return 1
	`)
	_ = script.Run(ctx, c.client, []string{serviceKey}, instanceID).Err()
}

// Deregister service by service ID
func (c *Client) Deregister(ctx context.Context, serviceName, serviceID string) error {
	c.lock.RLock()
	cc, ok := c.cancelers[serviceID]
	c.lock.RUnlock()
	if ok {
		cc.cancel()
		<-cc.done
	}

	c.deregisterService(ctx, serviceName, serviceID)
	return nil
}

// Service get services from redis
func (c *Client) Service(ctx context.Context, service string, index uint64, passingOnly bool) ([]*registry.ServiceInstance, uint64, error) {
	serviceKey := serviceKeyPrefix + service

	// 获取所有实例
	instancesMap, err := c.client.HGetAll(ctx, serviceKey).Result()
	if err != nil {
		return nil, 0, fmt.Errorf("get services from redis failed: %w", err)
	}

	var instances []*registry.ServiceInstance
	now := time.Now().Unix()

	for instanceID, instanceData := range instancesMap {
		var si serviceInstance
		if err := json.Unmarshal([]byte(instanceData), &si); err != nil {
			log.Warnf("[Redis] unmarshal service instance failed: %v, instanceID: %s", err, instanceID)
			continue
		}

		// 检查实例是否过期（如果超过过期时间，认为实例已失效）
		expireTime := int64(c.deregisterCriticalServiceAfter)
		if expireTime == 0 {
			expireTime = int64(c.healthcheckInterval * 3)
		}
		if now-si.Timestamp > expireTime {
			// 实例已过期，跳过
			continue
		}

		instances = append(instances, &registry.ServiceInstance{
			ID:        si.ID,
			Name:      si.Name,
			Version:   si.Version,
			Metadata:  si.Metadata,
			Endpoints: si.Endpoints,
		})
	}

	// 返回新的 index（使用时间戳）
	newIndex := uint64(time.Now().UnixNano())

	return instances, newIndex, nil
}

func sleepCtx(ctx context.Context, d time.Duration) error {
	t := time.NewTimer(d)
	defer t.Stop()
	select {
	case <-ctx.Done():
		return ctx.Err()
	case <-t.C:
		return nil
	}
}
