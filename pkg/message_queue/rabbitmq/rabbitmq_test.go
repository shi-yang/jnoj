package rabbitmq

import (
	"context"
	"fmt"
	"log"
	"testing"
	"time"
)

func TestPush(t *testing.T) {
	queueName := "test_queue"
	addr := "amqp://admin:admin@localhost:5672/"
	queue := NewClient(addr, queueName)

	ctx, cancel := context.WithDeadline(context.Background(), time.Now().Add(time.Second*20))
	defer cancel()
	for {
		select {
		case <-ctx.Done():
			queue.Close()
			return
		default:
			if err := queue.Push(context.TODO(), []byte(time.Now().String())); err != nil {
				fmt.Printf("Push failed: %s\n", err)
			} else {
				fmt.Println("Push succeeded!")
			}
		}
	}
}

func TestConsume(t *testing.T) {
	queueName := "test_queue"
	addr := "amqp://admin:admin@localhost:5672/"
	queue := NewClient(addr, queueName)

	err := queue.Consume(context.TODO(), func(ctx context.Context, b []byte) error {
		log.Println(string(b))
		return nil
	})
	if err != nil {
		t.Error(err)
	}
}
