<h3>Compile</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Language</th>
            <th>Compile method</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>C</th>
            <th>gcc Main.c -o Main -fno-asm -O2 -Wall -lm --static -std=c99 -DONLINE_JUDGE</th>
        </tr>
        <tr>
            <th>C++</th>
            <th>g++ -fno-asm -O2 -Wall -lm --static -std=c++11 -DONLINE_JUDGE -o Main Main.cc</th>
        </tr>
        <tr>
            <th>Java</th>
            <th>javac -J-Xms32m -J-Xmx256m Main.java </th>
        </tr>
        </tbody>
    </table>
</div>

<hr>

<h3>Result</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Result</th>
            <th width="120">中文</th>
            <th>Information</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Pending</th>
            <th>等待测评</th>
            <th>系统忙，你的答案在排队等待</th>
        </tr>
        <tr>
            <th>Pending Rejudge</th>
            <th>等待重测</th>
            <th>因为数据更新或其他原因，系统将重新判你的答案</th>
        </tr>
        <tr>
            <th>Compiling</th>
            <th>正在编译</th>
            <th>正在编译</th>
        </tr>
        <tr>
            <th>Running & Judging</th>
            <th>正在测评</th>
            <th>正在运行和判断</th>
        </tr>
        <tr>
            <th>Accepted</th>
            <th>通过</th>
            <th>程序通过</th>
        </tr>
        <tr>
            <th>Presentation Error</th>
            <th>输出格式错误</th>
            <th>答案基本正确，但是格式不对</th>
        </tr>
        <tr>
            <th>Wrong Answer</th>
            <th>解答错误</th>
            <th>答案不对，仅仅通过样例数据的测试并不一定是正确答案，一定还有你没想到的地方</th>
        </tr>
        <tr>
            <th>Time Limit Exceeded</th>
            <th>运行超时</th>
            <th>运行超出时间限制，检查下是否有死循环，或者应该有更快的计算方法</th>
        </tr>
        <tr>
            <th>Memory Limit Exceeded</th>
            <th>内存超限</th>
            <th>超出内存限制，数据可能需要压缩，检查内存是否有泄露</th>
        </tr>
        <tr>
            <th>Output Limit Exceeded</th>
            <th>输出超限</th>
            <th>输出超过限制，你的输出比正确答案长了两倍</th>
        </tr>
        <tr>
            <th>Runtime Error</th>
            <th>运行出错</th>
            <th>运行时错误，非法的内存访问，数组越界，指针漂移，调用禁用的系统函数。请点击后获得详细输出</th>
        </tr>
        <tr>
            <th>Compile Error</th>
            <th>编译错误</th>
            <th>编译错误，请点击后获得编译器的详细输出</th>
        </tr>
        </tbody>
    </table>
</div>
