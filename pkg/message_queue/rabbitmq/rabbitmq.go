// https://github.dev/rabbitmq/amqp091-go/blob/main/_examples/simple-consumer
package rabbitmq

import (
	"context"
	"errors"
	"fmt"
	"time"

	"log"

	messagequeue "jnoj/pkg/message_queue"

	amqp "github.com/rabbitmq/amqp091-go"
)

var (
	_ messagequeue.Queuer = (*Client)(nil)
)

const (
	// When reconnecting to the server after connection failure
	reconnectDelay = 5 * time.Second

	// When setting up the channel after a channel exception
	reInitDelay = 2 * time.Second

	// When resending messages the server didn't confirm
	resendDelay = 5 * time.Second
)

var (
	errNotConnected  = errors.New("not connected to a server")
	errAlreadyClosed = errors.New("already closed: not connected to the server")
	errShutdown      = errors.New("client is shutting down")
)

type Client struct {
	queueName       string
	connection      *amqp.Connection
	channel         *amqp.Channel
	done            chan bool
	notifyConnClose chan *amqp.Error
	notifyChanClose chan *amqp.Error
	notifyConfirm   chan amqp.Confirmation
	isReady         bool
}

func NewClient(addr, queueName string) *Client {
	client := Client{
		queueName: queueName,
		done:      make(chan bool),
	}
	go client.handleReconnect(addr)
	return &client
}

// handleReconnect will wait for a connection error on
// notifyConnClose, and then continuously attempt to reconnect.
func (client *Client) handleReconnect(addr string) {
	for {
		client.isReady = false
		conn, err := client.connect(addr)
		if err != nil {
			log.Println("Failed to connect. Retrying...", err)
			select {
			case <-client.done:
				return
			case <-time.After(reconnectDelay):
			}
			continue
		}
		if done := client.handleReInit(conn); done {
			break
		}
	}
}

// handleReconnect will wait for a channel error
// and then continuously attempt to re-initialize both channels
func (client *Client) handleReInit(conn *amqp.Connection) bool {
	for {
		client.isReady = false
		err := client.init(conn)
		if err != nil {
			log.Println("Failed to initialize channel. Retrying...")
			select {
			case <-client.done:
				return true
			case <-time.After(reInitDelay):
			}
			continue
		}
		select {
		case <-client.done:
			return true
		case <-client.notifyConnClose:
			log.Println("Connection closed. Reconnecting...")
			return false
		case <-client.notifyChanClose:
			log.Println("Channel closed. Re-running init...")
		}
	}
}

// init will initialize channel & declare queue
func (client *Client) init(conn *amqp.Connection) error {
	ch, err := conn.Channel()
	if err != nil {
		return err
	}
	err = ch.Confirm(false)
	if err != nil {
		return err
	}
	_, err = ch.QueueDeclare(
		client.queueName,
		false, // Durable
		false, // Delete when unused
		false, // Exclusive
		false, // No-wait
		nil,   // Arguments
	)
	if err != nil {
		return err
	}
	client.changeChannel(ch)
	client.isReady = true
	return nil
}

// changeChannel takes a new channel to the queue,
// and updates the channel listeners to reflect this.
func (client *Client) changeChannel(channel *amqp.Channel) {
	client.channel = channel
	client.notifyChanClose = make(chan *amqp.Error, 1)
	client.notifyConfirm = make(chan amqp.Confirmation, 1)
	client.channel.NotifyClose(client.notifyChanClose)
	client.channel.NotifyPublish(client.notifyConfirm)
}

// connect will create a new AMQP connection
func (client *Client) connect(addr string) (*amqp.Connection, error) {
	conn, err := amqp.Dial(addr)
	if err != nil {
		return nil, err
	}
	client.changeConnection(conn)
	return conn, nil
}

// changeConnection takes a new connection to the queue,
// and updates the close listener to reflect this.
func (client *Client) changeConnection(connection *amqp.Connection) {
	client.connection = connection
	client.notifyConnClose = make(chan *amqp.Error, 1)
	client.connection.NotifyClose(client.notifyConnClose)
}

// Push will push data onto the queue, and wait for a confirm.
// This will block until the server sends a confirm. Errors are
// only returned if the push action itself fails, see UnsafePush.
func (client *Client) Push(ctx context.Context, data []byte) error {
	if !client.isReady {
		return errors.New("failed to push: not connected")
	}
	for {
		err := client.UnsafePush(ctx, data)
		if err != nil {
			log.Println("Push failed. Retrying...")
			select {
			case <-client.done:
				return errShutdown
			case <-time.After(resendDelay):
			}
			continue
		}
		confirm := <-client.notifyConfirm
		if confirm.Ack {
			log.Printf("Push confirmed [%d]!", confirm.DeliveryTag)
			return nil
		}
	}
}

// UnsafePush will push to the queue without checking for
// confirmation. It returns an error if it fails to connect.
// No guarantees are provided for whether the server will
// receive the message.
func (client *Client) UnsafePush(ctx context.Context, data []byte) error {
	if !client.isReady {
		return errNotConnected
	}
	return client.channel.PublishWithContext(
		ctx,
		"",               // Exchange
		client.queueName, // Routing key
		false,            // Mandatory
		false,            // Immediate
		amqp.Publishing{
			ContentType: "text/plain",
			Body:        data,
		},
	)
}

// Consume will continuously put queue items on the channel.
// It is required to call delivery.Ack when it has been
// successfully processed, or delivery.Nack when it fails.
// Ignoring this will cause data to build up on the server.
func (client *Client) Consume(ctx context.Context, handler messagequeue.Handler) error {
	if !client.isReady {
		// Give the connection sometime to setup
		<-time.After(5 * time.Second)
	}
	deliveries, err := client.consume()
	if err != nil {
		return fmt.Errorf("could not start consuming: %s\n", err)
	}
	// This channel will receive a notification when a channel closed event
	// happens. This must be different from Client.notifyChanClose because the
	// library sends only one notification and Client.notifyChanClose already has
	// a receiver in handleReconnect().
	// Recommended to make it buffered to avoid deadlocks
	chClosedCh := make(chan *amqp.Error, 1)
	client.channel.NotifyClose(chClosedCh)
	for {
		select {
		case <-ctx.Done():
			client.Close()
			return errShutdown
		case amqErr := <-chClosedCh:
			// This case handles the event of closed channel e.g. abnormal shutdown
			log.Printf("AMQP Channel closed due to: %s\n", amqErr)
			deliveries, err = client.consume()
			if err != nil {
				// If the AMQP channel is not ready, it will continue the loop. Next
				// iteration will enter this case because chClosedCh is closed by the
				// library
				log.Println("Error trying to consume, will try again")
				continue
			}
			// Re-set channel to receive notifications
			// The library closes this channel after abnormal shutdown
			chClosedCh = make(chan *amqp.Error, 1)
			client.channel.NotifyClose(chClosedCh)
		case delivery := <-deliveries:
			if err := handler(ctx, delivery.Body); err != nil {
				log.Println(err)
			}
			if err := delivery.Ack(false); err != nil {
				log.Printf("Error acknowledging message: %s\n", err)
			}
		}
	}
}

// Consume will continuously put queue items on the channel.
// It is required to call delivery.Ack when it has been
// successfully processed, or delivery.Nack when it fails.
// Ignoring this will cause data to build up on the server.
func (client *Client) consume() (<-chan amqp.Delivery, error) {
	if !client.isReady {
		return nil, errNotConnected
	}

	if err := client.channel.Qos(
		1,     // prefetchCount
		0,     // prefrechSize
		false, // global
	); err != nil {
		return nil, err
	}

	return client.channel.Consume(
		client.queueName,
		"",    // Consumer
		false, // Auto-Ack
		false, // Exclusive
		false, // No-local
		false, // No-Wait
		nil,   // Args
	)
}

// Close will cleanly shut down the channel and connection.
func (client *Client) Close() error {
	if !client.isReady {
		return errAlreadyClosed
	}
	close(client.done)
	err := client.channel.Close()
	if err != nil {
		return err
	}
	err = client.connection.Close()
	if err != nil {
		return err
	}

	client.isReady = false
	return nil
}
