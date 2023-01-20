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

CREATE TABLE `group` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `member_count` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `group_user` (
  `id` int NOT NULL,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` tinyint NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `group`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `group_user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `group`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10000;

ALTER TABLE `group_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10000;
COMMIT;


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
  `source` varchar(255) DEFAULT '' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problemset` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `problem_count` int UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `problemset_problem` (
  `id` int NOT NULL,
  `problemset_id` int NOT NULL,
  `problem_id` int NOT NULL,
  `order` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `problemset`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `problemset_problem`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `problemset`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `problemset_problem`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

CREATE TABLE `problem_file` (
  `id` int NOT NULL,
  `problem_id` int UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `file_type` varchar(16) NOT NULL,
  `file_size` BIGINT NOT NULL DEFAULT '0',
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
  `id` int UNSIGNED NOT NULL,
  `problem_id` int UNSIGNED NOT NULL,
  `order` smallint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `input_size` int NOT NULL DEFAULT '0',
  `input_preview` varchar(255) NOT NULL DEFAULT '',
  `output_size` int NOT NULL DEFAULT '0',
  `output_preview` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `user_id` int NOT NULL,
  `is_example` tinyint NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
  `entity_id` int UNSIGNED NOT NULL,
  `entity_type` tinyint NOT NULL,
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


INSERT INTO `problem_file` (`problem_id`, `name`, `content`, `file_type`, `type`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(0, 'fcmp.cpp', '#include \"testlib.h\"\n#include <string>\n#include <vector>\n#include <sstream>\n \nusing namespace std;\n \nint main(int argc, char * argv[])\n{\n    setName(\"compare files as sequence of lines\");\n    registerTestlibCmd(argc, argv);\n \n    std::string strAnswer;\n \n    int n = 0;\n    while (!ans.eof()) \n    {\n        std::string j = ans.readString();\n \n        if (j == \"\" && ans.eof())\n          break;\n \n        strAnswer = j;\n        std::string p = ouf.readString();\n \n        n++;\n \n        if (j != p)\n            quitf(_wa, \"%d%s lines differ - expected: \'%s\', found: \'%s\'\", n, englishEnding(n).c_str(), compress(j).c_str(), compress(p).c_str());\n    }\n    \n    if (n == 1)\n        quitf(_ok, \"single line: \'%s\'\", compress(strAnswer).c_str());\n    \n    quitf(_ok, \"%d lines\", n);\n}\n', 'checker', '', 0, '2022-11-13 03:50:37', '2022-11-13 11:57:11', NULL),
(0, 'hcmp.cpp', '#include \"testlib.h\"\n \n#include <string>\n \nusing namespace std;\n \nstring part(const string& s)\n{\n    if (s.length() <= 128)\n        return s;\n    else\n        return s.substr(0, 64) + \"...\" + s.substr(s.length() - 64, 64);\n}\n \nbool isNumeric(string p)\n{\n    bool minus = false;\n \n    if (p[0] == \'-\')\n        minus = true,\n        p = p.substr(1);\n \n    for (int i = 0; i < p.length(); i++)\n        if (p[i] < \'0\' || p[i] > \'9\')\n            return false;\n \n    if (minus)\n        return (p.length() > 0 && (p.length() == 1 || p[0] != \'0\')) && (p.length() > 1 || p[0] != \'0\');\n    else\n        return p.length() > 0 && (p.length() == 1 || p[0] != \'0\');\n}\n \nint main(int argc, char * argv[])\n{\n    setName(\"compare two signed huge integers\");\n    registerTestlibCmd(argc, argv);\n    \n    string ja = ans.readWord();\n    string pa = ouf.readWord();\n \n    if (!isNumeric(ja))\n        quitf(_fail, \"%s is not valid integer\", part(ja).c_str());\n \n    if (!ans.seekEof())\n        quitf(_fail, \"expected exactly one token in the answer file\");\n    \n    if (!isNumeric(pa))\n        quitf(_pe, \"%s is not valid integer\", part(pa).c_str());\n \n    if (ja != pa)\n        quitf(_wa, \"expected %s, found %s\", part(ja).c_str(), part(pa).c_str());\n    \n    quitf(_ok, \"answer is %s\", part(ja).c_str());\n}\n', 'checker', '', 0, '2022-11-13 03:51:37', '2022-11-13 11:57:11', NULL),
(0, 'lcmp.cpp', '#include \"testlib.h\"\n#include <string>\n#include <vector>\n#include <sstream>\n \nusing namespace std;\n \nstring ending(int x)\n{\n    x %= 100;\n    if (x / 10 == 1)\n        return \"th\";\n    if (x % 10 == 1)\n        return \"st\";\n    if (x % 10 == 2)\n        return \"nd\";\n    if (x % 10 == 3)\n        return \"rd\";\n    return \"th\";\n}\n \nbool compareWords(string a, string b)\n{\n    vector<string> va, vb;\n    stringstream sa;\n    \n    sa << a;\n    string cur;\n    while (sa >> cur)\n        va.push_back(cur);\n \n    stringstream sb;\n    sb << b;\n    while (sb >> cur)\n        vb.push_back(cur);\n \n    return (va == vb);\n}\n \nint main(int argc, char * argv[])\n{\n    setName(\"compare files as sequence of tokens in lines\");\n    registerTestlibCmd(argc, argv);\n \n    std::string strAnswer;\n \n    int n = 0;\n    while (!ans.eof()) \n    {\n      std::string j = ans.readString();\n \n      if (j == \"\" && ans.eof())\n        break;\n      \n      std::string p = ouf.readString();\n      strAnswer = p;\n \n      n++;\n \n      if (!compareWords(j, p))\n        quitf(_wa, \"%d%s lines differ - expected: \'%s\', found: \'%s\'\", n, ending(n).c_str(), j.c_str(), p.c_str());\n    }\n    \n    if (n == 1 && strAnswer.length() <= 128)\n        quitf(_ok, \"%s\", strAnswer.c_str());\n    \n    quitf(_ok, \"%d lines\", n);\n}\n', 'checker', '', 0, '2022-11-13 03:52:33', '2022-11-13 11:57:11', NULL),
(0, 'ncmp.cpp', '#include \"testlib.h\"\n#include <sstream>\n \nusing namespace std;\n \nstring ending(long long x)\n{\n    x %= 100;\n    if (x / 10 == 1)\n        return \"th\";\n    if (x % 10 == 1)\n        return \"st\";\n    if (x % 10 == 2)\n        return \"nd\";\n    if (x % 10 == 3)\n        return \"rd\";\n    return \"th\";\n}\n \nstring ltoa(long long n)\n{\n    stringstream ss;\n    ss << n;\n    string result;\n    ss >> result;\n    return result;\n}\n \nint main(int argc, char * argv[])\n{\n    setName(\"compare ordered sequences of signed int%d numbers\", 8 * sizeof(long long));\n \n    registerTestlibCmd(argc, argv);\n \n    int n = 0;\n    \n    string firstElems;\n \n    while (!ans.seekEof() && !ouf.seekEof())\n    {\n      n++;\n      long long j = ans.readLong();\n      long long p = ouf.readLong();\n      if (j != p)\n        quitf(_wa, \"%d%s numbers differ - expected: \'%s\', found: \'%s\'\", n, ending(n).c_str(), ltoa(j).c_str(), ltoa(p).c_str());\n      else\n        if (n <= 5)\n        {\n            if (firstElems.length() > 0)\n                firstElems += \" \";\n            firstElems += ltoa(j);\n        }\n    }\n \n    int extraInAnsCount = 0;\n \n    while (!ans.seekEof())\n    {\n        ans.readLong();\n        extraInAnsCount++;\n    }\n    \n    int extraInOufCount = 0;\n \n    while (!ouf.seekEof())\n    {\n        ouf.readLong();\n        extraInOufCount++;\n    }\n \n    if (extraInAnsCount > 0)\n        quitf(_wa, \"Answer contains longer sequence [length = %d], but output contains %d elements\", n + extraInAnsCount, n);\n    \n    if (extraInOufCount > 0)\n        quitf(_wa, \"Output contains longer sequence [length = %d], but answer contains %d elements\", n + extraInOufCount, n);\n    \n    if (n <= 5)\n    {\n        quitf(_ok, \"%d number(s): \\\"%s\\\"\", n, firstElems.c_str());\n    }\n    else\n        quitf(_ok, \"%d numbers\", n);\n}\n', 'checker', '', 0, '2022-11-13 03:52:52', '2022-11-13 11:57:11', NULL),
(0, 'rcmp4.cpp', '#include \"testlib.h\"\n#include <cmath>\n \nusing namespace std;\n \n#define EPS 1E-4\n \nstring ending(int x)\n{\n    x %= 100;\n    if (x / 10 == 1)\n        return \"th\";\n    if (x % 10 == 1)\n        return \"st\";\n    if (x % 10 == 2)\n        return \"nd\";\n    if (x % 10 == 3)\n        return \"rd\";\n    return \"th\";\n}\n \nint main(int argc, char * argv[])\n{\n    setName(\"compare two sequences of doubles, max absolute or relative error = %.5lf\", EPS);\n    registerTestlibCmd(argc, argv);\n \n    int n = 0;\n    double j, p;\n \n    while (!ans.seekEof()) \n    {\n      n++;\n      j = ans.readDouble();\n      p = ouf.readDouble();\n      if (!doubleCompare(j, p, EPS))\n        quitf(_wa, \"%d%s numbers differ - expected: \'%.5lf\', found: \'%.5lf\', error = \'%.5lf\'\", n, ending(n).c_str(), j, p, doubleDelta(j, p));\n    }\n \n    if (n == 1)\n        quitf(_ok, \"found \'%.5lf\', expected \'%.5lf\', error \'%.5lf\'\", p, j, doubleDelta(j, p));\n \n    quitf(_ok, \"%d numbers\", n);\n}\n', 'checker', '', 0, '2022-11-13 03:53:29', '2022-11-13 11:57:11', NULL),
(0, 'rcmp6.cpp', '#include \"testlib.h\"\n#include <cmath>\n \nusing namespace std;\n \n#define EPS 1E-6\n \nstring ending(int x)\n{\n    x %= 100;\n    if (x / 10 == 1)\n        return \"th\";\n    if (x % 10 == 1)\n        return \"st\";\n    if (x % 10 == 2)\n        return \"nd\";\n    if (x % 10 == 3)\n        return \"rd\";\n    return \"th\";\n}\n \nint main(int argc, char * argv[])\n{\n    setName(\"compare two sequences of doubles, max absolute or relative  error = %.7lf\", EPS);\n    registerTestlibCmd(argc, argv);\n \n    int n = 0;\n    double j, p;\n \n    while (!ans.seekEof()) \n    {\n      n++;\n      j = ans.readDouble();\n      p = ouf.readDouble();\n      if (!doubleCompare(j, p, EPS))\n        quitf(_wa, \"%d%s numbers differ - expected: \'%.7lf\', found: \'%.7lf\', error = \'%.7lf\'\", n, ending(n).c_str(), j, p, doubleDelta(j, p));\n    }\n \n    if (n == 1)\n        quitf(_ok, \"found \'%.7lf\', expected \'%.7lf\', error \'%.7lf\'\", p, j, doubleDelta(j, p));\n \n    quitf(_ok, \"%d numbers\", n);\n}\n', 'checker', '', 0, '2022-11-13 03:54:24', '2022-11-13 11:57:11', NULL),
(0, 'rcmp9.cpp', '#include \"testlib.h\"\n#include <cmath>\n \nusing namespace std;\n \n#define EPS 1E-9\n \nstring ending(int x)\n{\n    x %= 100;\n    if (x / 10 == 1)\n        return \"th\";\n    if (x % 10 == 1)\n        return \"st\";\n    if (x % 10 == 2)\n        return \"nd\";\n    if (x % 10 == 3)\n        return \"rd\";\n    return \"th\";\n}\n \nint main(int argc, char * argv[])\n{\n    setName(\"compare two sequences of doubles, max absolute or relative error = %.10lf\", EPS);\n    registerTestlibCmd(argc, argv);\n \n    int n = 0;\n    double j, p;\n \n    while (!ans.seekEof()) \n    {\n      n++;\n      j = ans.readDouble();\n      p = ouf.readDouble();\n      if (!doubleCompare(j, p, EPS))\n        quitf(_wa, \"%d%s numbers differ - expected: \'%.10lf\', found: \'%.10lf\', error = \'%.10lf\'\", n, ending(n).c_str(), j, p, doubleDelta(j, p));\n    }\n \n    if (n == 1)\n        quitf(_ok, \"found \'%.10lf\', expected \'%.10lf\', error \'%.10lf\'\", p, j, doubleDelta(j, p));\n \n    quitf(_ok, \"%d numbers\", n);\n}\n', 'checker', '', 0, '2022-11-13 03:54:39', '2022-11-13 11:57:11', NULL),
(0, 'wcmp.cpp', '#include \"testlib.h\"\n \nusing namespace std;\n \nstring ending(int x)\n{\n    x %= 100;\n    if (x / 10 == 1)\n        return \"th\";\n    if (x % 10 == 1)\n        return \"st\";\n    if (x % 10 == 2)\n        return \"nd\";\n    if (x % 10 == 3)\n        return \"rd\";\n    return \"th\";\n}\n \nint main(int argc, char * argv[])\n{\n    setName(\"compare sequences of tokens\");\n    registerTestlibCmd(argc, argv);\n \n    std::string strAnswer;\n \n    int n = 0;\n \n    while (!ans.seekEof()) \n    {\n      n++;\n      std::string j = ans.readWord();\n      std::string p = ouf.readWord();\n      strAnswer = p;\n      if (j != p)\n        quitf(_wa, \"%d%s words differ - expected: \'%s\', found: \'%s\'\", n, ending(n).c_str(), j.c_str(), p.c_str());\n    }\n \n    if (n == 1 && strAnswer.length() <= 128)\n        quitf(_ok, \"%s\", strAnswer.c_str());\n \n    quitf(_ok, \"%d words\", n);\n}\n', 'checker', '', 0, '2022-11-13 03:55:05', '2022-11-13 11:57:11', NULL),
(0, 'yesno.cpp', '#include \"testlib.h\"\n \nstd::string upper(std::string sa)\n{\n    for (size_t i = 0; i < sa.length(); i++)\n        if (\'a\' <= sa[i] && sa[i] <= \'z\')\n            sa[i] = sa[i] - \'a\' + \'A\';\n    return sa;\n}\n \nint main(int argc, char * argv[])\n{\n    setName(\"YES or NO (case insensetive)\");\n    registerTestlibCmd(argc, argv);\n \n    std::string ja = upper(ans.readWord());\n    std::string pa = upper(ouf.readWord());\n \n    if (pa != \"YES\" && pa != \"NO\")\n        quitf(_pe, \"YES or NO expected, but %s found\", pa.c_str());\n \n    if (ja != \"YES\" && ja != \"NO\")\n        quitf(_fail, \"YES or NO expected in answer, but %s found\", ja.c_str());\n \n    if (ja != pa)\n        quitf(_wa, \"expected %s, found %s\", ja.c_str(), pa.c_str());\n \n    quitf(_ok, \"answer is %s\", ja.c_str());\n}\n', 'checker', '', 0, '2022-11-13 03:55:22', '2022-11-13 11:57:11', NULL);
