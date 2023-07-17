package dbmigrate

import (
	"log"

	gormigrate "jnoj/pkg/gormmigrate"
	"jnoj/pkg/password"

	"gorm.io/gorm"
)

func Migrate(db *gorm.DB) {
	m := gormigrate.New(db, gormigrate.DefaultOptions, []*gormigrate.Migration{
		MigrateAddSuperAdminUser20230716(),
		MigrateAddUserBadge20230717(),
	})
	m.InitSchema(MigrateInitDB)
	if err := m.Migrate(); err != nil {
		log.Fatalf("Migration failed: %v", err)
	}
	log.Println("Migration did run successfully")
}

func MigrateAddUserBadge20230717() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddUserBadge20230717",
		Migrate: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `user_user_badge` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `badge_id`").Error
		},
		Rollback: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `user_user_badge` DROP `created_at`").Error
		},
	}
}

func MigrateAddSuperAdminUser20230716() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddSuperAdminUser20230716",
		Migrate: func(tx *gorm.DB) error {
			passwd, _ := password.GeneratePasswordHash("admin")
			return tx.
				Exec("INSERT INTO user (id, username, nickname, realname, role, password) VALUES (10000, 'admin', 'admin', 'admin', 4, ?)",
					passwd).
				Error
		},
		Rollback: func(tx *gorm.DB) error {
			return tx.Exec("DELETE FROM user WHERE username = 'admin'").Error
		},
	}
}
