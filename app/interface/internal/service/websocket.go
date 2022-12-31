package service

import (
	"net/http"

	"jnoj/app/interface/internal/biz"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/gorilla/websocket"
)

// WebSocketService is a WebSocket service.
type WebSocketService struct {
	uc       *biz.WebSocketUsecase
	log      *log.Helper
	upgrader websocket.Upgrader
}

// NewWebSocketService new a WebSocket service.
func NewWebSocketService(uc *biz.WebSocketUsecase, logger log.Logger) *WebSocketService {
	return &WebSocketService{
		uc:  uc,
		log: log.NewHelper(logger),
		upgrader: websocket.Upgrader{
			// Allow cross domain
			CheckOrigin: func(r *http.Request) bool {
				return true
			},
		},
	}
}

func (s *WebSocketService) WsHandler(w http.ResponseWriter, r *http.Request) {
	var (
		data []byte
	)
	userID := r.URL.Query().Get("uid")
	c, err := s.upgrader.Upgrade(w, r, nil)
	if err != nil {
		return
	}
	defer c.Close()
	conn, err := s.uc.NewConnection(c, userID)
	if err != nil {
		s.log.Error("init conn fail:", err)
		return
	}
	go conn.WriteLoop()
	go conn.ReadLoop()
	for {
		if data, err = s.uc.ReadMessage(userID); err != nil {
			s.log.Info("Receive fail:", err)
			s.uc.CloseConnection(userID)
			break
		}
		s.log.Info("Receive:", string(data))
	}
}
