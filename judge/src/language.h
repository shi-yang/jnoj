#ifndef JUDGE_LANGUAGE_H
#define JUDGE_LANGUAGE_H

struct language {
    char name[10]; // 语言名字
    char * compile_cmd[20]; // 编译命令
    char * run_cmd[20]; // 运行命令
    char file_ext[10]; // 文件后缀
    bool is_vmrun; // 该语言是否以虚拟机方式运行
};

struct language languages[] = {
    {
        "c",
        {"gcc", "Main.c", "-o", "Main", "-Wall", "-O2", "-lm", "--static", "-std=c99",
         "-DONLINE_JUDGE", NULL},
        {"./Main", NULL},
        "c",
        false
    },
    {
        "c++",
        {"g++", "-fno-asm", "-O2", "-Wall", "-lm", "--static", "-std=c++11",
         "-DONLINE_JUDGE", "-o", "Main", "Main.cc", NULL},
        {"./Main", NULL},
        "cc",
        false
	},
    {
        "java",
        {"javac", "-J-Xms64M", "-J-Xmx128M",
         "-encoding", "UTF-8", "Main.java", NULL},
        {"java", "-Xms64M", "-Xmx128M", "-Djava.security.manager",
         "-Djava.security.policy=./java.policy", "Main", NULL},
        "java",
        true
    },
    {
        "python3",
        {"python3", "-c",
         "import py_compile; py_compile.compile(r'Main.py')", NULL},
        {"python3", "Main.py", NULL},
        "py",
        true
    }
};

#define LANG_C          0
#define LANG_CPP        1
#define LANG_JAVA       2
#define LANG_PYTHON3    3

#endif //JUDGE_LANGUAGE_H
