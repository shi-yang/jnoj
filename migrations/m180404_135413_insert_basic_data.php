<?php

use app\migrations\BaseMigration;

/**
 * Class m180404_135413_insert_basic_data
 */
class m180404_135413_insert_basic_data extends BaseMigration
{
    public function up()
    {
        $time = new \yii\db\Expression('NOW()');

        $this->insert('{{%problem}}', [
            'id' => 1000,
            'title' => '[在线测评解答教程] A+B Problem',
            'description' => '输入两个数字，输出它们的和。',
            'input' => '两个整数: $$ a, ｂ(0 \\leq  a, b \\leq 100)$$。',
            'output' => '输出一个整数，该整数为 $$a, b$$ 两数字之和。',
            'sample_input' => "a:3:{i:0;s:3:\"1 2\";i:1;s:0:\"\";i:2;s:0:\"\";}",
            'sample_output' => "a:3:{i:0;s:1:\"3\";i:1;s:0:\"\";i:2;s:0:\"\";}",
            'spj' => 0,
            'hint' => "Q：输入和输出在哪里？\r\n\r\nA：您的程序应始终从 stdin（标准输入）读取输入，并将输出写入 stdout（标准输出）。 例如，您可以使用C中的 `scanf` 或 C++ 中的 `cin` 从 stdin 中读取，并使用 C 中的 `printf` 或 C++ 中的 `cout` 写入 stdout。\r\n\r\n如果不是题目要求的，您不得输出任何额外的信息到标准输出，否则您会得到一个 `Wrong Answer`。\r\n\r\n用户程序不允许打开和读取/写入文件。 如果您尝试这样做，您将收到 `Runtime Error` 或 `Wrong Answer`。\r\n\r\n以下是问题 1000 使用 C / C++ / Java 的示例解决方案：\r\n\r\n```c\r\n#include <stdio.h>\r\nint main()\r\n{\r\n    int a, b;\r\n    scanf(\"%d %d\", &a, &b);\r\n    printf(\"%d\\n\", a + b);\r\n    return 0;\r\n}\r\n```\r\n\r\n```cpp\r\n#include <iostream>\r\nusing namespace std;\r\nint  main()\r\n{\r\n    int a, b;\r\n    cin >> a >> b;\r\n    cout << a + b << endl;\r\n    return 0;\r\n}\r\n```\r\n```\r\nimport java.util.Scanner;\r\n\r\npublic class Main {\r\n    public static void main(String[] args) {\r\n        Scanner in = new Scanner(System.in);\r\n        int a = in.nextInt();\r\n        int b = in.nextInt();\r\n        System.out.println(a + b);\r\n    }\r\n}\r\n```",
            'time_limit' => 1,
            'memory_limit' => 128,
            'status' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $this->insert('{{%problem}}', [
            'id' => 1001,
            'title' => '[在线测评解答教程] 求和',
            'description' => '输入一个数 $$n$$，你的任务是计算 1 + 2 + ... + n 的结果．',
            'input' => '输入的数据有多行，每行一个整数 $$n (1 < n < 1000)$$，以 EOF 表示输入结束。',
            'output' => '输出数据同样有多行，每行输出一个整数，该整数的值为 $$1 + 2 + ... + n$$。',
            'sample_input' => "a:3:{i:0;s:7:\"10\r\n100\";i:1;N;i:2;N;}",
            'sample_output' => "a:3:{i:0;s:8:\"55\r\n5050\";i:1;N;i:2;N;}",
            'spj' => 0,
            'hint' => "通常，题目会要求多组样例输入。对于多组样例输入，一般会是读到 `EOF` 结束。\r\n\r\n`EOF` 的意思是 `End Of File`，表示读到文件尾，结束输入。\r\n`scanf` 函数的返回值如果为 `EOF` 的话，就表示输入结束了。比如题目输入一个数，以 `EOF` 结束，你就可以这样写：\r\n\r\nC 语言：\r\n```\r\n#include<stdio.h>\r\nint main()\r\n{\r\n	int n;\r\n	while (scanf(\"%d\", &n) != EOF) {\r\n		//解题代码\r\n	}\r\n	return 0;\r\n}\r\n```\r\nC++:\r\n```cpp\r\n#include <iostream>\r\nusing namespace std;\r\nint main()\r\n{\r\n	int n;\r\n	while (cin >> n) {\r\n		//解题代码\r\n	}\r\n	return 0;\r\n}\r\n```\r\n\r\n所以，这道题的 Accepted 代码是（以Ｃ语言为例）：\r\n```c\r\n#include<stdio.h>\r\nint main()\r\n{\r\n	int n;\r\n	while (scanf(\"%d\", &n) != EOF) {\r\n		printf(\"%d\\n\", n * (n + 1) / 2 );\r\n	}\r\n	return 0;\r\n}\r\n```",
            'time_limit' => 1,
            'memory_limit' => 128,
            'status' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $this->insert('{{%problem}}', [
            'id' => 1002,
            'title' => '[在线测评解答教程] 闰年',
            'description' => '给你一个年份，请判断它是不是闰年。',
            'input' => "第一行，输入一个整数 $$ t $$，表示有 $$ t $$ 组样例。\r\n\r\n接下来 $$ t $$ 行，每行输入一个整数 $$ n ( 1000 \leq n \leq 4000 ) $$，表示需要你判断的年份。",
            'output' => "输出 $$ t $$ 行。\r\n\r\n对于输入的 $$ n $$，如果它是闰年，输出 `Yes`，否则输出 `No`。",
            'sample_input' => "a:3:{i:0;s:31:\"5\r\n2016\r\n2017\r\n2018\r\n2019\r\n2020\";i:1;N;i:2;N;}",
            'sample_output' => "a:3:{i:0;s:20:\"Yes\r\nNo\r\nNo\r\nNo\r\nYes\";i:1;N;i:2;N;}",
            'spj' => 0,
            'hint' =>  "题目要求输入 t 组样例，那么我们可以这样写：\r\n```c\r\n#include <stdio.h>\r\nint main()\r\n{\r\n	int t, n;\r\n	scanf(\"%d\", &t);\r\n	while (t--) {\r\n		scanf(\"%d\", &n);\r\n		//在这里写判断 n 是否为闰年的代码及输出结果\r\n	}\r\n}\r\n```\r\n这道题的解题方法就靠大家发挥了．",
            'time_limit' => 1,
            'memory_limit' => 128,
            'status' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);
    }

    public function down()
    {
        $this->delete('{{%problem}}', ['id' => 1000]);
        $this->delete('{{%problem}}', ['id' => 1001]);
        $this->delete('{{%problem}}', ['id' => 1002]);
    }
}
