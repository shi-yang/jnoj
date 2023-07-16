package dbmigrate

import (
	"log"
	"os"
	"testing"

	"gorm.io/driver/mysql"
	"gorm.io/gorm"
	logger2 "gorm.io/gorm/logger"
	"gorm.io/gorm/schema"
)

func TestMigrate(t *testing.T) {
	source := "root:123456@tcp(mysql:3306)/jnoj?charset=utf8mb4&parseTime=True&loc=Local"
	db, err := gorm.Open(mysql.Open(source), &gorm.Config{
		NamingStrategy: schema.NamingStrategy{
			SingularTable: true,
		},
		Logger: logger2.New(
			log.New(os.Stdout, "\r\n", log.LstdFlags),
			logger2.Config{
				LogLevel: logger2.Info,
			}),
	})
	if err != nil {
		t.Fatal(err)
	}
	Migrate(db)
}

func TestMigrateInitDB(t *testing.T) {
	source := "root:123456@tcp(mysql:3306)/jnoj?charset=utf8mb4&parseTime=True&loc=Local"
	db, err := gorm.Open(mysql.Open(source), &gorm.Config{
		NamingStrategy: schema.NamingStrategy{
			SingularTable: true,
		},
		Logger: logger2.New(
			log.New(os.Stdout, "\r\n", log.LstdFlags),
			logger2.Config{
				LogLevel: logger2.Info,
			}),
	})
	if err != nil {
		t.Fatal(err)
	}
	MigrateInitDB(db)
}
