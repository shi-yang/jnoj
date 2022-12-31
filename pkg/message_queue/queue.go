package messagequeue

import "context"

type Handler func(context.Context, []byte) error

type Queuer interface {
	Consume(ctx context.Context, handler Handler) error
	Push(ctx context.Context, message []byte) error
	Close() error
}
