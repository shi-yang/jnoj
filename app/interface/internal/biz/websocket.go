package biz

import (
	"context"
	"jnoj/app/interface/internal/conf"
	"strconv"
	"sync"
	"time"

	queueV1 "jnoj/api/queue/v1"

	"github.com/go-kratos/kratos/v2/encoding"
	_ "github.com/go-kratos/kratos/v2/encoding/json"
	"github.com/go-kratos/kratos/v2/log"
	"github.com/gorilla/websocket"
)

type WebSocketRepo interface {
	HandlerMessageFromQueue(context.Context, func(context.Context, []byte) error)
}

type WebSocketUsecase struct {
	log  *log.Helper
	conn Conn
	repo WebSocketRepo
}

const (
	DataLen = 1000

	// Time allowed to read the next pong message from the peer.
	pongWait = 30 * time.Second

	// Send pings to peer with this period. Must be less than pongWait.
	pingPeriod = (pongWait * 9) / 10
)

// NewWebSocketUsecase new a WebSocket usecase.
func NewWebSocketUsecase(c *conf.Service, repo WebSocketRepo, logger log.Logger) *WebSocketUsecase {
	ws := &WebSocketUsecase{
		log:  log.NewHelper(logger),
		conn: Conn{},
		repo: repo,
	}
	ws.conn.Data = make(map[string]*Connection)
	// 从 message queue 读取消息发到客户端
	ws.repo.HandlerMessageFromQueue(context.TODO(), func(ctx context.Context, b []byte) error {
		var m queueV1.Message
		jsonCodec := encoding.GetCodec("json")
		_ = jsonCodec.Unmarshal(b, &m)
		ws.WriteMessage(strconv.Itoa(int(m.UserId)), b)
		return nil
	})
	return ws
}

type Conn struct {
	Data map[string]*Connection
	sync.RWMutex
}

type Connection struct {
	WsConn    *websocket.Conn
	InChan    chan []byte
	OutChan   chan []byte
	CloseChan chan byte
	Mutex     sync.Mutex
	IsClosed  bool
}

func (c *Connection) Close() {
	//exec once
	c.Mutex.Lock()
	defer c.Mutex.Unlock()
	c.WsConn.Close()
	if !c.IsClosed {
		close(c.CloseChan)
		c.IsClosed = true
	}
}

func (uc *WebSocketUsecase) NewConnection(wsConn *websocket.Conn, userId string) (conn *Connection, err error) {
	conn = &Connection{
		WsConn:    wsConn,
		InChan:    make(chan []byte, DataLen),
		OutChan:   make(chan []byte, DataLen),
		CloseChan: make(chan byte, 1),
	}
	uc.conn.Lock()
	defer uc.conn.Unlock()
	uc.conn.Data[userId] = conn
	return
}

func (uc *WebSocketUsecase) CloseConnection(userId string) {
	uc.conn.Lock()
	defer uc.conn.Unlock()
	if m, ok := uc.conn.Data[userId]; ok {
		m.Close()
	}
	delete(uc.conn.Data, userId)
}

func (c *Connection) ReadLoop() {
	var (
		data []byte
		err  error
	)
	c.WsConn.SetPongHandler(func(string) error {
		c.WsConn.SetReadDeadline(time.Now().Add(pongWait))
		return nil
	})
	for {
		if _, data, err = c.WsConn.ReadMessage(); err != nil {
			c.Close()
			return
		}
		select {
		case c.InChan <- data:
		case <-c.CloseChan:
			c.Close()
		}
	}
}

func (c *Connection) WriteLoop() {
	ticker := time.NewTicker(pingPeriod)
	var (
		data []byte
		err  error
	)
	defer func() {
		ticker.Stop()
	}()
	for {
		select {
		case data = <-c.OutChan:
		case <-ticker.C:
			if err := c.WsConn.WriteMessage(websocket.PingMessage, nil); err != nil {
				return
			}
		case <-c.CloseChan:
			c.Close()
		}
		if err = c.WsConn.WriteMessage(websocket.TextMessage, data); err != nil {
			c.Close()
			return
		}
	}
}

func (uc *WebSocketUsecase) ReadMessage(userId string) (data []byte, err error) {
	if m, ok := uc.conn.Data[userId]; ok {
		data = <-m.InChan
	}
	return
}

func (uc *WebSocketUsecase) WriteMessage(userId string, data []byte) (err error) {
	if m, ok := uc.conn.Data[userId]; ok {
		if m.IsClosed {
			uc.CloseConnection(userId)
			return
		}
		m.OutChan <- data
	}
	return
}
