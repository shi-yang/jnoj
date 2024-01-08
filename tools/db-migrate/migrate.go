package dbmigrate

import (
	"log"
	"time"

	gormigrate "jnoj/pkg/gormmigrate"
	"jnoj/pkg/password"

	"gorm.io/gorm"
)

func Migrate(db *gorm.DB) {
	m := gormigrate.New(db, gormigrate.DefaultOptions, []*gormigrate.Migration{
		MigrateAddSuperAdminUser20230716(),
		MigrateAddUserBadge20230717(),
		MigrateAddUserProfile20230719(),
		MigrateAddProblemsetType20230727(),
		MigrateCreateProblemsetAnswer20230801(),
		MigrateAddUserAvatar20230802(),
		MigrateAddProblemsetPermission20230803(),
		MigrateAddProblemsetChild20230805(),
		MigrateAddProblemsetExamScore20230809(),
		MigrateAddProblemsetUserScore20230831(),
		MigrateAddContestUserRank20230903(),
		MigrateFixCannotSubmitProblemsetExam20230906(),
		MigrateAddContestEvent20240108(),
	})
	m.InitSchema(MigrateInitDB)
	if err := m.Migrate(); err != nil {
		log.Fatalf("Migration failed: %v", err)
	}
	log.Println("Migration did run successfully")
}

func MigrateAddContestEvent20240108() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddContestEvent20240108",
		Migrate: func(d *gorm.DB) error {
			d.Exec("ALTER TABLE `contest_user` DROP `special_effects`;")
			err := d.Exec("CREATE TABLE `contest_event` (" +
				"`id` INT UNSIGNED NOT NULL AUTO_INCREMENT," +
				"`contest_id` INT UNSIGNED NOT NULL," +
				"`user_id` INT UNSIGNED NOT NULL," +
				"`problem_id` INT UNSIGNED NOT NULL," +
				"`type` TINYINT UNSIGNED NOT NULL," +
				"`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," +
				"PRIMARY KEY (`id`)," +
				"INDEX `idx_contest_id`(`contest_id`)," +
				"INDEX `idx_user_id`(`user_id`)," +
				"INDEX `idx_problem_id`(`problem_id`)" +
				") ENGINE = InnoDB CHARSET = utf8mb4 COLLATE utf8mb4_general_ci;").Error
			return err
		},
		Rollback: func(d *gorm.DB) error {
			return d.Exec("DROP TABLE `contest_event`").Error
		},
	}
}

func MigrateFixCannotSubmitProblemsetExam20230906() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateFixCannotSubmitProblemsetExam20230906",
		Migrate: func(d *gorm.DB) error {
			err := d.Exec("ALTER TABLE `problemset_answer` CHANGE `answered_problem_ids` `answered_problem_ids` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset_answer` CHANGE `unanswered_problem_ids` `unanswered_problem_ids` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset_answer` CHANGE `correct_problem_ids` `correct_problem_ids` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset_answer` CHANGE `wrong_problem_ids` `wrong_problem_ids` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset_answer` CHANGE `wrong_problem_ids` `wrong_problem_ids` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;").Error
			if err != nil {
				return err
			}
			return nil
		},
		Rollback: func(d *gorm.DB) error {
			return nil
		},
	}
}

func MigrateAddContestUserRank20230903() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddContestUserRank20230903",
		Migrate: func(d *gorm.DB) error {
			err := d.Exec("ALTER TABLE `contest_user` ADD `rank` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `user_id`, ADD `score` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `rank`;").Error
			if err != nil {
				return err
			}
			return nil
		},
		Rollback: func(d *gorm.DB) error {
			d.Exec("ALTER TABLE `contest_user` DROP `rank`, DROP `score`;")
			return nil
		},
	}
}

func MigrateAddProblemsetUserScore20230831() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddProblemsetUserScore20230831",
		Migrate: func(d *gorm.DB) error {
			type ProblemsetAnswer struct {
				ID           int
				ProblemsetID int
				UserID       int
				Score        float32 // 得分
				Answer       string
				SubmittedAt  *time.Time
				CreatedAt    time.Time
				UpdatedAt    time.Time
			}
			type ProblemsetUser struct {
				ID            int
				ProblemsetID  int
				UserID        int
				AcceptedCount int     // 过题量
				InitialScore  float32 // 试卷模式：首次分数
				BestScore     float32 // 试卷模式：最好分数
				CreatedAt     time.Time
				UpdatedAt     time.Time
			}
			var answers []ProblemsetAnswer
			d.Model(&ProblemsetAnswer{}).Find(&answers)
			err := d.Exec("ALTER TABLE `problemset_user` ADD `initial_score` FLOAT NOT NULL DEFAULT '-1' AFTER `accepted_count`, ADD `best_score` FLOAT NOT NULL DEFAULT '0' AFTER `initial_score`;").Error
			if err != nil {
				return err
			}
			for _, answer := range answers {
				// 更新已有数据
				if answer.SubmittedAt != nil {
					// 记录用户的分数
					user := ProblemsetUser{}
					err := d.First(&user, "problemset_id = ? and user_id = ?", answer.ProblemsetID, answer.UserID).Error
					if err == nil {
						if user.BestScore < answer.Score {
							user.BestScore = answer.Score
						}
						if user.InitialScore < 0 {
							user.InitialScore = answer.Score
						}
						d.Updates(user)
					}
				}
			}
			return nil
		},
		Rollback: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `problemset_user` DROP `initial_score`, DROP `best_score`;").Error
		},
	}
}

func MigrateAddProblemsetExamScore20230809() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddProblemsetExamScore20230809",
		Migrate: func(d *gorm.DB) error {
			err := d.Exec("ALTER TABLE `problemset_user` ADD `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset_problem` ADD `score` FLOAT UNSIGNED NOT NULL DEFAULT '0' AFTER `order`;").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset_answer` ADD `score` FLOAT UNSIGNED NOT NULL DEFAULT '0' AFTER `user_id`;").Error
			if err != nil {
				return err
			}
			return nil
		},
		Rollback: func(d *gorm.DB) error {
			d.Exec("ALTER TABLE `problemset_user` DROP `updated_at`;")
			d.Exec("ALTER TABLE `problemset_problem` DROP `score`;")
			d.Exec("ALTER TABLE `problemset_answer` DROP `score`;")
			return nil
		},
	}
}

func MigrateAddProblemsetChild20230805() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddProblemsetChild20230805",
		Migrate: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `problemset` ADD `parent_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `id`, ADD `child_order` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `parent_id`;").Error
		},
		Rollback: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `problemset` DROP `parent_id`, DROP `child_order`;").Error
		},
	}
}

func MigrateAddProblemsetPermission20230803() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddProblemsetPermission20230803",
		Migrate: func(d *gorm.DB) error {
			err := d.Exec("CREATE TABLE `problemset_user` (" +
				"`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, " +
				"`problemset_id` INT UNSIGNED NOT NULL," +
				"`user_id` INT UNSIGNED NOT NULL," +
				"`accepted_count` INT UNSIGNED NOT NULL DEFAULT '0'," +
				"`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," +
				"PRIMARY KEY (`id`)" +
				") ENGINE = InnoDB;").Error
			if err != nil {
				return err
			}
			// 补充 problemset_user 数据
			rows, _ := d.Select("DISTINCT user_id, entity_id").
				Table("submission").
				Where("entity_type = 0").Rows()
			for rows.Next() {
				var userId, entityId, count int
				rows.Scan(&userId, &entityId)
				problemIds := d.Select("problem_id").
					Table("problemset_problem").
					Where("problemset_id = ?", entityId)
				d.Table("submission").Select("COUNT(DISTINCT problem_id) AS accepted_count").
					Where("user_id = ?", userId).
					Where("verdict = ?", 4).
					Where("problem_id in (?)", problemIds).
					Scan(&count)
				d.Exec("INSERT INTO `problemset_user` (`problemset_id`, `user_id`, `accepted_count`) VALUES (?, ?, ?)", entityId, userId, count)
			}
			err = d.Exec("ALTER TABLE `problemset` ADD `member_count` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `problem_count`;").Error
			if err != nil {
				return err
			}
			// 修正刚刚添加的 member_count 字段
			type Problemset struct {
				ID          int
				MemberCount int
			}
			var sets []Problemset
			d.Find(&sets)
			for _, set := range sets {
				countQuery := d.Select("COUNT(*)").
					Table("problemset_user").
					Where("problemset_id = ?", set.ID)
				d.Model(&Problemset{}).Where("id = ?", set.ID).Update("member_count", countQuery)
			}
			err = d.Exec("ALTER TABLE `problemset` ADD `membership` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `description`;").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset` ADD `invitation_code` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' AFTER `membership`;").Error
			if err != nil {
				return err
			}
			return nil
		},
		Rollback: func(d *gorm.DB) error {
			err := d.Exec("DROP TABLE `problemset_user`").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset` DROP `member_count`;").Error
			if err != nil {
				return err
			}
			err = d.Exec("ALTER TABLE `problemset` DROP `membership`;").Error
			if err != nil {
				return err
			}
			return d.Exec("ALTER TABLE `problemset` DROP `invitation_code`;").Error
		},
	}
}

func MigrateAddUserAvatar20230802() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddUserAvatar20230802",
		Migrate: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `user` ADD `avatar` VARCHAR(255) NOT NULL DEFAULT '' AFTER `nickname`;").Error
		},
		Rollback: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `user` DROP `avatar`;").Error
		},
	}
}

func MigrateCreateProblemsetAnswer20230801() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateCreateProblemsetAnswer20230801",
		Migrate: func(d *gorm.DB) error {
			return d.Exec("CREATE TABLE `problemset_answer` (" +
				"`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, " +
				"`problemset_id` INT UNSIGNED NOT NULL," +
				"`user_id` INT UNSIGNED NOT NULL," +
				"`answer` TEXT NOT NULL," +
				"`answered_problem_ids` VARCHAR(255) NOT NULL DEFAULT ''," +
				"`unanswered_problem_ids` VARCHAR(255) NOT NULL DEFAULT ''," +
				"`correct_problem_ids` VARCHAR(255) NOT NULL DEFAULT ''," +
				"`wrong_problem_ids` VARCHAR(255) NOT NULL DEFAULT ''," +
				"`submission_ids` VARCHAR(255) NOT NULL DEFAULT ''," +
				"`submitted_at` DATETIME NULL DEFAULT NULL," +
				"`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," +
				"`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP," +
				"PRIMARY KEY (`id`)" +
				") ENGINE = InnoDB;").Error
		},
		Rollback: func(d *gorm.DB) error {
			return d.Exec("DROP TABLE `problemset_answer").Error
		},
	}
}

func MigrateAddProblemsetType20230727() *gormigrate.Migration {
	return &gormigrate.Migration{
		ID: "MigrateAddProblemsetType20230727",
		Migrate: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `problemset` ADD `type` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `name`;").Error
		},
		Rollback: func(d *gorm.DB) error {
			return d.Exec("ALTER TABLE `problemset` DROP `type`;").Error
		},
	}
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
