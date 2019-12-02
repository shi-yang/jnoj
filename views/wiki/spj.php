<h2>Special Judge</h2>
<hr>
<p>简称 SPJ，这是针对用户输出的特判。比如，根据题面求解出来的答案可能存在多个，这样就无法定义一个准确的输出文件来判断用户是否正确，这时就需要 SPJ。或者允许用户的输出在某一精度范围内是正确的。</p>

<p>SPJ 是一个用C、C++写的可执行程序，其返回值决定着判断结果，成功返回(0)表示AC，其他非零值表示WA。</p>
<p>SPJ 的编译参数为：g++ -fno-asm -std=c++11 -O2 ，即已经开启C++11以及O2优化。</p>
<p>请确保 SPJ 程序的正确运行，也<b>未调用与判题无关的系统函数</b>，当 SPJ 在 OJ 中编译出错或运行出错时，OJ 不会给出反馈。</p>
<p> spj 输出到 <code>stderr(标准错误)</code> 的内容将会被记录到用户错误数据点中。

<p>下面给出两种写 SPJ 的方法，采用其中一种即可。</p>
<hr>
<h3>示例一：</h3>
<div class="pre"><p>#include &lt;stdio.h&gt;
<iostream>
#define AC 0
#define WA 1
const double eps = 1e-4;
int main(int argc,char *args[])
{
    FILE * f_in = fopen(args[1],"r");
    FILE * f_user = fopen(args[2],"r");
    FILE * f_out = fopen(args[3],"r");
    int ret = AC;
    /**************判题逻辑**************/
    /**
    * 以下判题逻辑代码只是举例：输入有 t 组数据，写 spj 来判断每组数据测试输出与用户输出之差是否在 eps 之内。
    */
    int t;
    double a, x;
    fscanf(f_in, “%d”, &t); //从输入中读取数据组数 t
    while (t-–) {
        fscanf(f_out, “%lf”, &a); //从读取测试输出
        fscanf(f_user, “%lf”, &x); //从读取用户输出
        if(fabs(a-x) > eps) {
            ret = WA;//Wrong Answer
            // 或使用 fprintf(stderr, "结果误差过大\n"); 输出到 标准错误 时，会被错误数据点记录下来
            std::cerr << "答案：" << a << "  你的输出：" << x << "。结果误差过大" << std::endl;
            break;
        }
    }
    /***********************************/
    fclose(f_in);
    fclose(f_out);
    fclose(f_user);
    return ret;
}
</p></div>

<hr>
<h3>示例二：</h3>

<p>当前 OJ 采用了跟 Codeforces 一样的 SPJ 标准，即Testlib库。</p>

<p>下载地址：
    <a href="https://github.com/MikeMirzayanov/testlib">
        https://github.com/MikeMirzayanov/testlib
    </a>
</p>

<p>当标准输出和选手输出的差小于0.01，那么可以AC，否则WA。</p>
<div class="pre"><p>#include "testlib.h"
int main(int argc, char* argv[]) {
    registerTestlibCmd(argc, argv);
    double pans = ouf.readDouble();
    double jans = ans.readDouble();

    if (fabs(pans - jans)<0.01)
        quitf(_ok, "The answer is correct.");
    else
        quitf(_wa, "The answer is wrong: expected = %f, found = %f", jans, pans);
}
</p></div>

<p>在程序中，有3个重要的结构体：inf指数据输入文件（本例没有），ouf指选手输出文件，ans指标准答案。</p>

<p>然后，可以从这3表结构体读入数据，不需要用到标准输入输出。如果读到的数据和下面的期望不一致，则spj返回fail结果。</p>

<p>这边继续给出一个多行（不定行数）的spj判断：</p>
<div class="pre"><p>#include "testlib.h"
int main(int argc, char* argv[]) {
    registerTestlibCmd(argc, argv);

    while(!ans.eof()){
    double pans = ouf.readDouble();
    double jans = ans.readDouble();
    ans.readEoln();

    if (fabs(pans - jans)>0.01)
        quitf(_wa, "The answer is wrong: expected = %f, found = %f", jans, pans);
    }
    quitf(_ok, "The answer is correct.");
    return 0;
}
</p></div>

<p>以下读入命令可以使用：</p>

<p>初始化checker，必须在最前面调用一次：<code>void registerTestlibCmd(argc, argv)</code></p>

<p>读入一个char，指针后移一位：<code>char readChar()</code></p>

<p>和上面一样，但是只能读到一个字母c：<code>char readChar(char c)</code></p>

<p>同 readChar(' ')：<code>char readSpace()</code></p>

<p>读入一个字符串，但是遇到空格、换行、eof为止：<code>string readToken()</code></p>

<p>读入一个long long/int64：<code>long long readLong()</code></p>

<p>同上，但是限定范围（包括L，R）：<code>long long readLong(long long L, long long R)</code></p>

<p>读入一个int：<code>int readInt()</code></p>

<p>同上，但是限定范围（包括L，R）：<code>int readInt(int L, int R)</code></p>

<p>读入一个实数：<code>double readReal()</code></p>

<p>同上，但是限定范围（包括L，R）：<code>double readReal(double L, double R)</code></p>

<p>读入一个限定范围精度位数的实数：<code>double readStrictReal(double L, double R, int minPrecision, int maxPrecision)</code></p>

<p>读入string，到换行或者eof为止:<code>string readString(), string readLine()</code></p>

<p>读入一个换行符: <code>void readEoln()</code></p>

<p>读入一个eof:<code>void readEof()</code></p>

<p>输出：</p>

<p>给出 AC:<code>quitf(\_ok, "The answer is correct. answer is %d", ans);</code></p>

<p>给出 WA:<code>quitf(\_wa, "The answer is wrong: expected = %f, found = %f", jans, pans);</code></p>

<hr>
<h3>测试</h3>

使用编译器将该文件编译。在命令行中输入:
<div class="pre"><p>./spj in.txt out.txt ans.txt   # Linux
spj.exe in.txt out.txt ans.txt  # Windows
</p></div>
其中in.txt out.txt ans.txt分别是放在同一目录下的输入文件、选手输出、标准答案。

程序将返回结果。