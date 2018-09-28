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
            'description' => '<p>输入两个数字，输出它们的和。</p>',
            'input' => '<p>两个整数: $ a, b(0 \\leq a, b \\leq 100)$。</p>',
            'output' => '<p>输出一个整数，该整数为 $a, b$ 两数字之和。</p>\'',
            'sample_input' => "a:3:{i:0;s:3:\"1 2\";i:1;s:0:\"\";i:2;s:0:\"\";}",
            'sample_output' => "a:3:{i:0;s:1:\"3\";i:1;s:0:\"\";i:2;s:0:\"\";}",
            'spj' => 0,
            'hint' => "<p>Q：输入和输出在哪里？</p><p>A：您的程序应始终从 <code>stdin</code>（标准输入）读取输入，并将输出写入 <code>stdout</code>（标准输出）。例如，您可以使用C中的 <code>scanf</code> 或 C++ 中的 <code>cin</code> 从 <code>stdin</code> 中读取，并使用 C 中的 <code>printf</code> 或 C++ 中的 <code>cout</code> 写入 <code>stdout</code>。如果不是题目要求的，您不得输出任何额外的信息到标准输出，否则您会得到一个 <code>Wrong Answer</code>。 用户程序不允许打开和读取/写入文件。如果您尝试这样做，您将收到 <code>Runtime Error</code> 或 <code>Wrong Answer</code>。</p><p>以下是问题 1000 使用 C / C++ / Java 的示例解决方案：&nbsp;</p><pre><p>#include &lt;stdio.h&gt;\r\nint main()\r\n{\r\n    int a, b;\r\n    scanf(\"%d %d\", &amp;a, &amp;b);\r\n    printf(\"%d\\n\", a + b);\r\n    return 0;\r\n}</p></pre><pre><p>#include &lt;iostream&gt;\r\nusing namespace std;\r\nint  main()\r\n{\r\n    int a, b;\r\n    cin &gt;&gt; a &gt;&gt; b;\r\n    cout &lt;&lt; a + b &lt;&lt; endl;\r\n    return 0;\r\n}</p></pre><pre><p>import java.util.Scanner;\r\n\r\npublic class Main {\r\n    public static void main(String[] args) {\r\n        Scanner in = new Scanner(System.in);\r\n        int a = in.nextInt();\r\n        int b = in.nextInt();\r\n        System.out.print(a + b + \"\\n\");\r\n    }\r\n}</p></pre>",
            'time_limit' => 1,
            'memory_limit' => 128,
            'status' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $this->insert('{{%problem}}', [
            'id' => 1001,
            'title' => '[在线测评解答教程] 求和',
            'description' => '<p>输入一个数 $n$，你的任务是计算 $1 + 2 + ... + n$ 的结果．</p>',
            'input' => '<p>输入的数据有多行，每行一个整数 $n (1 \\le n \\le 1000)$，以 <code>EOF</code> 表示输入结束。</p>',
            'output' => '<p>输出数据同样有多行，每行输出一个整数，该整数的值为 $1 + 2 + ... + n$。</p>',
            'sample_input' => "a:3:{i:0;s:7:\"10\r\n100\";i:1;N;i:2;N;}",
            'sample_output' => "a:3:{i:0;s:8:\"55\r\n5050\";i:1;N;i:2;N;}",
            'spj' => 0,
            'hint' => "<p>通常，题目会要求多组样例输入。对于多组样例输入，一般会是读到 <code>EOF</code> 结束。 <code>EOF</code> 的意思是 <code>End Of File</code>，表示读到文件尾，结束输入。 <code>scanf</code> 函数的返回值如果为 <code>EOF</code> 的话，就表示输入结束了。比如题目输入一个数，以 <code>EOF</code> 结束，你就可以这样写：</p><p>C 语言：</p><pre><p>#include&lt;stdio.h&gt;<br>int main()<br>{<br>    int n;<br>    while (scanf(\"%d\", &amp;n) != EOF) {<br>         //解题代码<br>    }<br>    return 0;<br>}</p></pre><p>C++:&nbsp;</p><pre><p>#include &lt;iostream&gt;<br>using namespace std;<br>int main()<br>{<br>    int n;<br>    while (cin &gt;&gt; n) {<br>        //解题代码<br>    }<br>    return 0;<br>}</p></pre><p>所以，这道题的 Accepted 代码是（以Ｃ语言为例）：</p><pre><p>#include&lt;stdio.h&gt;<br>int main()<br>{<br>    int n;<br>    while (scanf(\"%d\", &amp;n) != EOF) {<br>        printf(\"%d\\n\", n * (n + 1) / 2 );<br>    }<br>    return 0;<br>}</p></pre>",
            'time_limit' => 1,
            'memory_limit' => 128,
            'status' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $this->insert('{{%problem}}', [
            'id' => 1002,
            'title' => '[在线测评解答教程] 闰年',
            'description' => '<p>给你一个年份，请判断它是不是闰年。</p>',
            'input' => '<p>第一行，输入一个整数 $t$，表示有 $t$ 组样例。<br>接下来 $t$ 行，每行输入一个整数 $ n(1000 \\leq n \\leq 4000)$，表示需要你判断的年份。</p>',
            'output' => '<p>输出 $ t $ 行。 对于输入的 $n$，如果它是闰年，输出 <code>Yes</code>，否则输出 <code>No</code>。</p>',
            'sample_input' => "a:3:{i:0;s:31:\"5\r\n2016\r\n2017\r\n2018\r\n2019\r\n2020\";i:1;N;i:2;N;}",
            'sample_output' => "a:3:{i:0;s:20:\"Yes\r\nNo\r\nNo\r\nNo\r\nYes\";i:1;N;i:2;N;}",
            'spj' => 0,
            'hint' =>  "<p>题目要求输入 \$t\$ 组样例，那么我们可以这样写：</p><pre><p>#include &lt;stdio.h&gt;<br>int main()<br>{<br>    int t, n;<br>    scanf(\"%d\", &amp;t);<br>    while (t--) {<br>        scanf(\"%d\", &amp;n);<br>        //在这里写判断 n 是否为闰年的代码及输出结果<br>    }<br>    return 0;<br>}</p></pre><p>这道题的解题方法就靠大家发挥了．</p><p>提示：在处理多组样例时，可以一组样例、一组样例地输出，而不必等处理完所有样例才统一输出。</p>",
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
