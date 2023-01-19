package data

import (
	"jnoj/app/interface/internal/conf"
	"os"

	log2 "log"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-redis/redis"
	"github.com/google/wire"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
	logger2 "gorm.io/gorm/logger"
	"gorm.io/gorm/schema"
)

// ProviderSet is data providers.
var ProviderSet = wire.NewSet(
	NewData,
	NewContestRepo,
	NewProblemRepo,
	NewUserRepo,
	NewSubmissionRepo,
	NewProblemsetRepo,
	NewGroupRepo,
)

// Data .
type Data struct {
	db      *gorm.DB
	redisdb *redis.Client
	conf    *conf.Data
}

// NewData .
func NewData(c *conf.Data, logger log.Logger) (*Data, func(), error) {
	db, err := gorm.Open(mysql.Open(c.Database.Source), &gorm.Config{
		NamingStrategy: schema.NamingStrategy{
			SingularTable: true,
		},
		Logger: logger2.New(
			log2.New(os.Stdout, "\r\n", log2.LstdFlags),
			logger2.Config{
				LogLevel: logger2.Info,
			}),
	})
	if err != nil {
		log.Errorf("failed opening connection to mysql: %v", err)
		return nil, nil, err
	}

	redisdb := redis.NewClient(&redis.Options{
		Addr: c.Redis.Addr,
	})

	cleanup := func() {
		log.NewHelper(logger).Info("closing the data resources")
		redisdb.Close()
	}
	return &Data{
		db:      db,
		redisdb: redisdb,
		conf:    c,
	}, cleanup, nil
}
