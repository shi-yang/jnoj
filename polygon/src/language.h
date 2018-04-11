#ifndef JUDGE_LANGUAGE_H
#define JUDGE_LANGUAGE_H

struct language {
	char name[10];
	char * compile_cmd[20];
    char * run_cmd[20];
    char file_ext[10];
    bool is_vmrun;
};

struct language languages[] = {
	{
		"c",
		{"gcc", "solution.c", "-o", "solution", "-Wall", "-lm", "--static", "-std=c99",
         "-DONLINE_JUDGE", NULL},
        {"./solution", NULL},
        "c",
        false
	},
	{
		"c++",
		{"g++", "-fno-asm", "-Wall", "-lm", "--static", "-std=c++11",
         "-DONLINE_JUDGE", "-o", "solution", "solution.cc", NULL},
        {"./solution", NULL},
        "cc",
        false
	},
    {
        "java",
        {"javac", "-J-Xms64M", "-J-Xmx128M",
         "-encoding", "UTF-8", "solution.java", NULL},
        {"java", "-Xms64M", "-Xmx128M", "-Djava.security.manager",
         "-Djava.security.policy=./java.policy", "solution", NULL},
        "java",
        true
    },
    {
        "python3",
        {"python3", "-c",
         "import py_compile; py_compile.compile(r'solution.py')", NULL},
        {"python3", "solution.py", NULL},
        "py",
        true
    }
};

#define LANG_C          0
#define LANG_CPP        1
#define LANG_JAVA       2
#define LANG_PYTHON3    3

#endif //JUDGE_LANGUAGE_H
