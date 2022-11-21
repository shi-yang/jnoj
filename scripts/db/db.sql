SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `contest` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `frozen_time` datetime DEFAULT NULL,
  `type` tinyint UNSIGNED NOT NULL,
  `group_id` int UNSIGNED NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint UNSIGNED NOT NULL,
  `participant_count` mediumint UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contest_problem` (
  `id` int UNSIGNED NOT NULL,
  `number` int NOT NULL,
  `contest_id` int UNSIGNED NOT NULL,
  `problem_id` int UNSIGNED NOT NULL,
  `score` smallint UNSIGNED NOT NULL DEFAULT '0',
  `submit_count` mediumint UNSIGNED NOT NULL DEFAULT '0',
  `accepted_count` mediumint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contest_user` (
  `id` int UNSIGNED NOT NULL,
  `contest_id` int NOT NULL,
  `user_id` int NOT NULL,
  `is_ban` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problem` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `time_limit` int UNSIGNED NOT NULL DEFAULT '1000',
  `memory_limit` int UNSIGNED NOT NULL DEFAULT '1000',
  `accepted_count` int UNSIGNED NOT NULL DEFAULT '0',
  `submit_count` int UNSIGNED NOT NULL DEFAULT '0',
  `checker_id` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `user_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problem_file` (
  `id` int NOT NULL,
  `problem_id` int UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `file_type` varchar(16) NOT NULL,
  `type` varchar(64) NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problem_statement` (
  `id` int UNSIGNED NOT NULL,
  `problem_id` int NOT NULL,
  `language` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `legend` text NOT NULL,
  `input` text NOT NULL,
  `output` text NOT NULL,
  `note` text NOT NULL,
  `source` varchar(255) NOT NULL DEFAULT '',
  `user_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problem_tag` (
  `id` int UNSIGNED NOT NULL,
  `problem_id` int UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problem_test` (
  `id` bigint NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `size` bigint UNSIGNED NOT NULL,
  `remark` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `is_example` tinyint NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problem_user_status` (
  `id` int UNSIGNED NOT NULL,
  `problem_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `contest_id` int UNSIGNED NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problem_verification` (
  `id` int NOT NULL,
  `problem_id` int NOT NULL,
  `verification_status` int NOT NULL,
  `verification_info` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `submission` (
  `id` int UNSIGNED NOT NULL,
  `problem_id` int UNSIGNED NOT NULL,
  `source` text NOT NULL,
  `time` int UNSIGNED NOT NULL DEFAULT '0',
  `memory` int UNSIGNED NOT NULL DEFAULT '0',
  `verdict` tinyint NOT NULL DEFAULT '0',
  `language` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  `contest_id` int UNSIGNED NOT NULL,
  `score` smallint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `submission_info` (
  `submission_id` int UNSIGNED NOT NULL,
  `run_info` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(32) NOT NULL,
  `nickname` varchar(32) NOT NULL,
  `password` char(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` char(11) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `contest`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contest_problem`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contest_user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `problem`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `problem_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_polygon_id` (`problem_id`);

ALTER TABLE `problem_statement`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `problem_tag`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_problem_id` (`problem_id`);

ALTER TABLE `problem_test`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `problem_user_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_contest_id` (`contest_id`,`user_id`,`problem_id`) USING BTREE;

ALTER TABLE `problem_verification`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `submission`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `submission_info`
  ADD PRIMARY KEY (`submission_id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `contest`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `contest_problem`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `contest_user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `problem`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `problem_file`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `problem_statement`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `problem_tag`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `problem_test`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

ALTER TABLE `problem_user_status`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `problem_verification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `submission`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

ALTER TABLE contest AUTO_INCREMENT=1000;
ALTER TABLE contest_problem AUTO_INCREMENT=1000;
ALTER TABLE contest_user AUTO_INCREMENT=1000;
ALTER TABLE problem AUTO_INCREMENT=1000;
ALTER TABLE problem_file AUTO_INCREMENT=1000;
ALTER TABLE problem_statement AUTO_INCREMENT=1000;
ALTER TABLE problem_test AUTO_INCREMENT=1000;
ALTER TABLE problem_verification AUTO_INCREMENT=1000;
ALTER TABLE submission AUTO_INCREMENT=1000;
ALTER TABLE submission_info AUTO_INCREMENT=1000;
ALTER TABLE `user` AUTO_INCREMENT=10000;