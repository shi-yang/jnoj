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
		MigrateAddUserProfile20230719(),
	})
	m.InitSchema(MigrateInitDB)
	if err := m.Migrate(); err != nil {
		log.Fatalf("Migration failed: %v", err)
	}
	log.Println("Migration did run successfully")
}

func MigrateAddUserProfile20230719() *gormigrate.Migration {
	sql := "CREATE TABLE `user_profile`(" +
		"`user_id` INT NOT NULL," +
		"`realname` VARCHAR(64) NOT NULL DEFAULT ''," +
		"`location` VARCHAR(128) NOT NULL DEFAULT ''," +
		"`bio` VARCHAR(512) NOT NULL DEFAULT ''," +
		"`gender` TINYINT UNSIGNED NOT NULL DEFAULT '0'," +
		"`school` VARCHAR(255) NOT NULL DEFAULT ''," +
		"`birthday` DATETIME NULL DEFAULT NULL," +
		"`company` VARCHAR(255) NOT NULL DEFAULT ''," +
		"`job` VARCHAR(255) NOT NULL DEFAULT ''," +
		"`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," +
		"`updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP," +
		"PRIMARY KEY(`user_id`)" +
		") ENGINE = InnoDB CHARSET = utf8mb4 COLLATE utf8mb4_general_ci;"
	return &gormigrate.Migration{
		ID: "MigrateAddUserProfile20230719",
		Migrate: func(d *gorm.DB) error {
			err := d.Exec(sql).Error
			if err != nil {
				return err
			}
			// 迁移旧数据
			var users []struct {
				ID       int
				Realname string
			}
			d.Select("id, realname").Table("user").Find(&users)
			for _, v := range users {
				err := d.Exec("INSERT INTO user_profile (user_id, realname) VALUES (?, ?)", v.ID, v.Realname).Error
				if err != nil {
					return err
				}
			}
			return d.Exec("ALTER TABLE `user` DROP `realname`").Error
		},
		Rollback: func(d *gorm.DB) error {
			return d.Exec("DROP TABLE `user_profile").Error
		},
	}
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
