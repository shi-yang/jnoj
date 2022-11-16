package data

import (
	"context"
	"jnoj/app/sandbox/internal/conf"
	"os"

	log2 "log"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-redis/redis"
	"github.com/google/wire"
	"go.mongodb.org/mongo-driver/mongo"
	"go.mongodb.org/mongo-driver/mongo/options"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
	"gorm.io/gorm/schema"

	logger2 "gorm.io/gorm/logger"
)

// ProviderSet is data providers.
var ProviderSet = wire.NewSet(NewData, NewSandboxRepo, NewSubmissionRepo)

// Data .
type Data struct {
	db      *gorm.DB
	redisdb *redis.Client
	mongodb *mongo.Database
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

	mongoClient, err := mongo.Connect(context.TODO(), options.Client().ApplyURI(c.Mongodb.Uri))
	if err != nil {
		log.Fatalf("failed opening connection to mongodb: %v", err)
	}

	cleanup := func() {
		log.NewHelper(logger).Info("closing the data resources")
		redisdb.Close()
		mongoClient.Disconnect(context.TODO())
	}
	return &Data{
		db:      db,
		redisdb: redisdb,
		mongodb: mongoClient.Database(c.Mongodb.Database),
	}, cleanup, nil
}
