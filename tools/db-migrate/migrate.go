package dbmigrate

import (
	"log"

	"github.com/go-gormigrate/gormigrate/v2"
	"gorm.io/gorm"
)

func Migrate(db *gorm.DB) {
	m := gormigrate.New(db, gormigrate.DefaultOptions, []*gormigrate.Migration{
		// Migrate20230715(),
	})
	m.InitSchema(MigrateInitDB)
	if err := m.Migrate(); err != nil {
		log.Fatalf("Migration failed: %v", err)
	}
	log.Println("Migration did run successfully")
}

// func Migrate20230715() *gormigrate.Migration {
// 	return &gormigrate.Migration{
// 		ID: "202307151845",
// 		Migrate: func(tx *gorm.DB) error {
// 			// type user struct {
// 			// 	ID   uuid.UUID `gorm:"type:uuid;primaryKey;uniqueIndex"`
// 			// 	Name string
// 			// }
// 			// return tx.Migrator().CreateTable(&user{})
// 		},
// 		Rollback: func(tx *gorm.DB) error {
// 			// return tx.Migrator().DropTable("users")
// 		},
// 	}
// }
