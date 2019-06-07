/*
 * Copyright 2008 sempr <iamsempr@gmail.com>
 *
 * Refacted and modified by zhblue<newsclan@gmail.com>
 * Bug report email newsclan@gmail.com
 *
 * This file is part of HUSTOJ.
 *
 * HUSTOJ is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * HUSTOJ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HUSTOJ. if not, see <http://www.gnu.org/licenses/>.
 */
#define _GNU_SOURCE
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <dirent.h>
#include <time.h>
#include <stdarg.h>
#include <ctype.h>
#include <getopt.h>
#include <sys/wait.h>
#include <sys/ptrace.h>
#include <sys/types.h>
#include <sys/user.h>
#include <sys/syscall.h>
#include <sys/time.h>
#include <sys/resource.h>
#include <sys/signal.h>
#include <sys/stat.h>
#include <unistd.h>
#include <errno.h>
#include <mysql/mysql.h>
#include <assert.h>
#include <limits.h>
#include "okcalls.h"
#include "common.h"
#include "language.h"
#include "cJSON.h"

#define STD_MB 1048576LL
#define STD_T_LIM 2
#define STD_F_LIM (STD_MB<<5)
#define STD_M_LIM (STD_MB<<7)
#define BUFFER_SIZE 4096
#define RECORD_SIZE 256


/*copy from ZOJ
 http://code.google.com/p/zoj/source/browse/trunk/judge_client/client/tracer.cc?spec=svn367&r=367#39
 */
#ifdef __i386
#define REG_SYSCALL orig_eax
#define REG_RET eax
#define REG_ARG0 ebx
#define REG_ARG1 ecx
#else
#define REG_SYSCALL orig_rax
#define REG_RET rax
#define REG_ARG0 rdi
#define REG_ARG1 rsi
#endif

typedef struct {
    int id;
    int memory_limit;
    int time_limit;
    bool isspj;
} problem_struct;

typedef struct { // 记录每个数据点测试状态
    double score; // OI 中的分数
    int verdict; // 测评结果
    int time; // 测评时间
    int memory; // 测评内存
    int exit_code; // 用户程序退出状态
    char input[RECORD_SIZE]; // 测试输入
    char output[RECORD_SIZE]; // 测试输出
    char user_output[RECORD_SIZE]; // 用户输出
    int checker_exit_code; // SPJ 退出状态
    char checker_log[RECORD_SIZE]; // SPJ 输出
} verdict_struct;

typedef struct subtask_s {
    int score; // 子任务的分数
    int test_count; // 子任务的数据点个数。最大 1000
    char *test_input_name[1000];
    struct subtask_s * next;
} subtask_struct;

static char oj_home[BUFFER_SIZE];

static int sleep_time;
static int java_time_bonus = 5;
static int java_memory_bonus = 512;
static char java_xms[BUFFER_SIZE];
static char java_xmx[BUFFER_SIZE];
static int oi_mode = 0;
static int full_diff = 0;

static int shm_run = 0;

static char record_call = 0;
static int use_ptrace = 1;
static int compile_chroot = 1;
static int subtask_cnt = 0;

static const char * tbname = "solution";
//static int sleep_tmp;

#ifdef _mysql_h
MYSQL *conn;
#endif

long get_file_size(const char * filename)
{
    struct stat f_stat;
    if (stat(filename, &f_stat) == -1) {
        return 0;
    }
    return (long) f_stat.st_size;
}

void write_log(const char *fmt, ...)
{
    va_list ap;
    char buffer[4096];
    sprintf(buffer, "%s/log/client.log", oj_home);
    FILE *fp = fopen(buffer, "ae+");
    if (fp == NULL) {
        fprintf(stderr, "openfile error!\n");
        system("pwd");
    }
    va_start(ap, fmt);
    vsprintf(buffer, fmt, ap);
    fprintf(fp, "%s\n", buffer);
    printf("%s\n", buffer);
    va_end(ap);
    fclose(fp);
}

int execute_cmd(const char * fmt, ...)
{
    char cmd[BUFFER_SIZE];

    int ret = 0;
    va_list ap;

    va_start(ap, fmt);
    vsprintf(cmd, fmt, ap);
    //printf("%s\n",cmd);
    ret = system(cmd);
    va_end(ap);
    return ret;
}

#define CALL_ARRAY_SIZE 512
unsigned int call_id = 0;
unsigned int call_counter[CALL_ARRAY_SIZE];

void init_syscalls_limits(int lang)
{
    int i;
    memset(call_counter, 0, sizeof(call_counter));
    if (DEBUG)
        write_log("init_call_counter:%d", lang);

    for (i = 0; i == 0 || ok_calls[lang].call[i]; i++) {
        call_counter[ok_calls[lang].call[i]] = HOJ_MAX_LIMIT;
    }
}

// read the configue file
void init_mysql_conf()
{
    FILE *fp = NULL;
    char buf[BUFFER_SIZE];
    db.port_number = 3306;
    sleep_time = 3;
    strcpy(java_xms, "-Xms32m");
    strcpy(java_xmx, "-Xmx256m");
    sprintf(buf, "%s/config.ini", oj_home);
    fp = fopen("./config.ini", "re");
    if (fp != NULL) {
        while (fgets(buf, BUFFER_SIZE - 1, fp)) {
            read_buf(buf, "OJ_HOST_NAME", db.host_name);
            read_buf(buf, "OJ_USER_NAME", db.user_name);
            read_buf(buf, "OJ_PASSWORD", db.password);
            read_buf(buf, "OJ_DB_NAME", db.db_name);
            read_buf(buf, "OJ_MYSQL_UNIX_PORT", db.mysql_unix_port);
            read_int(buf, "OJ_PORT_NUMBER", &db.port_number);
            read_int(buf, "OJ_JAVA_TIME_BONUS", &java_time_bonus);
            read_int(buf, "OJ_JAVA_MEMORY_BONUS", &java_memory_bonus);
            read_buf(buf, "OJ_JAVA_XMS", java_xms);
            read_buf(buf, "OJ_JAVA_XMX", java_xmx);
            read_int(buf, "OJ_FULL_DIFF", &full_diff);
            read_int(buf, "OJ_SHM_RUN", &shm_run);
            read_int(buf, "OJ_COMPILE_CHROOT", &compile_chroot);
        }
        fclose(fp);
    }
}

int input_file_filter(const struct dirent *entry)
{
	int l = strlen(entry->d_name);
	if (l <= 3 || strcmp(entry->d_name + l - 3, ".in") != 0)
		return 0;
	else
		return l - 3;
}

void find_next_nonspace(int * c1, int * c2, FILE ** f1, FILE ** f2, int * ret)
{
    // Find the next non-space character or \n.
    while ((isspace(*c1)) || (isspace(*c2))) {
        if (*c1 != *c2) {
            if (*c2 == EOF) {
                do {
                    *c1 = fgetc(*f1);
                } while (isspace(*c1));
                continue;
            } else if (*c1 == EOF) {
                do {
                    *c2 = fgetc(*f2);
                } while (isspace(*c2));
                continue;
            } else if (isspace(*c1) && isspace(*c2)) {
                while (*c2 == '\n' && isspace(*c1) && *c1!='\n')
                    *c1 = fgetc(*f1);
                while (*c1 == '\n' && isspace(*c2) && *c2!='\n')
                    *c2 = fgetc(*f2);
            } else {
                *ret = OJ_PE;
            }
        }
        if (isspace(*c1)) {
            *c1 = fgetc(*f1);
        }
        if (isspace(*c2)) {
            *c2 = fgetc(*f2);
        }
    }
}

void delnextline(char s[])
{
    int L;
    L = strlen(s);
    while (L > 0 && (s[L - 1] == '\n' || s[L - 1] == '\r'))
        s[--L] = 0;
}

int compare(const char *file1, const char *file2)
{
    int ret = OJ_AC;
    int c1, c2;
    FILE *f1 = fopen(file1, "re");
    FILE *f2 = fopen(file2, "re");

    if (!f1 || !f2) {
        ret = OJ_RE;
    } else
        for (;;) {
            // Find the first non-space character at the beginning of line.
            // Blank lines are skipped.
            c1 = fgetc(f1);
            c2 = fgetc(f2);
            find_next_nonspace(&c1, &c2, &f1, &f2, &ret);
            // Compare the current line.
            for (;;) {
                // Read until 2 files return a space or 0 together.
                while ((!isspace(c1) && c1) || (!isspace(c2) && c2)) {
                    if (c1 == EOF && c2 == EOF) {
                        goto end;
                    }
                    if (c1 == EOF || c2 == EOF) {
                        break;
                    }
                    if (c1 != c2) {
                        // Consecutive non-space characters should be
                        // all exactly the same
                        ret = OJ_WA;
                        goto end;
                    }
                    c1 = fgetc(f1);
                    c2 = fgetc(f2);
                }
                find_next_nonspace(&c1, &c2, &f1, &f2, &ret);
                if (c1 == EOF && c2 == EOF) {
                    goto end;
                }
                if (c1 == EOF || c2 == EOF) {
                    ret = OJ_WA;
                    goto end;
                }
                if ((c1 == '\n' || !c1) && (c2 == '\n' || !c2)) {
                    break;
                }
            }
        }
    end:
    if (f1)
        fclose(f1);
    if (f2)
        fclose(f2);
    return ret;
}

void update_solution(int solution_id, int result, int time, int memory,
                     char *pass_info, int score)
{
    if (result == OJ_TL && memory == 0)
        result = OJ_ML;
    char sql[BUFFER_SIZE];

    sprintf(sql,
            "UPDATE %s SET result=%d,time=%d,memory=%d,pass_info='%s',"
            "score=%d,judge='%s',judgetime=now() WHERE id=%d LIMIT 1",
            tbname, result, time, memory, pass_info, score, "local", solution_id);

    // printf("sql= %s\n",sql);
    if (mysql_real_query(conn, sql, strlen(sql))) {
        // printf("..update failed! %s\n",mysql_error(conn));
    }
}

void update_solution_info(int solution_id, char * buf)
{
    char sql[(1 << 16)];
    sprintf(sql,
            "INSERT INTO `solution_info`(`solution_id`, `run_info`) VALUES(%d, '%s') "
            "ON DUPLICATE KEY UPDATE `run_info`='%s'",
            solution_id, buf, buf);
    if (mysql_real_query(conn, sql, strlen(sql)))
        write_log(mysql_error(conn));
}

void addceinfo(int solution_id)
{
    char ceinfo[(1 << 15)], *cend;
    FILE *fp = fopen("ce.txt", "re");
    cend = ceinfo;
    while (fgets(cend, 1024, fp)) {
        cend += strlen(cend);
        if (cend - ceinfo > 30000)
            break;
    }
    update_solution_info(solution_id, ceinfo);
}

void update_problem_stat(int pid)
{
    char sql[BUFFER_SIZE];
    sprintf(sql,
            "UPDATE `problem` SET `accepted`=(SELECT count(*) FROM `solution` "
            "WHERE `problem_id`=%d AND `result`=4) WHERE `id`=%d",
            pid, pid);
    if (mysql_real_query(conn, sql, strlen(sql)))
        write_log(mysql_error(conn));
    sprintf(sql,
            "UPDATE `problem` SET `submit`=(SELECT count(*) FROM `solution` "
            "WHERE `problem_id`=%d) WHERE `id`=%d",
            pid, pid);
    if (mysql_real_query(conn, sql, strlen(sql)))
        write_log(mysql_error(conn));
}

void umount(char *work_dir)
{
    execute_cmd("/bin/umount -f %s/proc 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/dev 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/lib 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/lib64 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/etc/alternatives 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/usr 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/bin 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/proc 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f bin usr lib lib64 etc/alternatives proc dev 2>/dev/null");
    execute_cmd("/bin/umount -f %s/* 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/log/* 2>/dev/null", work_dir);
    execute_cmd("/bin/umount -f %s/log/etc/alternatives 2>/dev/null", work_dir);
}

int compile(int lang, char * work_dir)
{
    pid_t pid = fork();
    if (pid == 0) {
        struct rlimit LIM;
        LIM.rlim_max = 6;
        LIM.rlim_cur = 6;
        setrlimit(RLIMIT_CPU, &LIM);
        alarm(6);
        LIM.rlim_max = 40 * STD_MB;
        LIM.rlim_cur = 40 * STD_MB;
        setrlimit(RLIMIT_FSIZE, &LIM);

        if (lang == LANG_JAVA) {
            LIM.rlim_max = STD_MB << 11;
            LIM.rlim_cur = STD_MB << 11;    
        } else {
            LIM.rlim_max = STD_MB * 512;
            LIM.rlim_cur = STD_MB * 512;
            setrlimit(RLIMIT_AS, &LIM);
        }
        

        freopen("ce.txt", "w", stderr);
        execute_cmd("/bin/chown judge %s ", work_dir);
        execute_cmd("/bin/chmod 700 %s ", work_dir);

        if (compile_chroot && lang != LANG_JAVA && lang != LANG_PYTHON3) {
            execute_cmd("mkdir -p bin usr lib lib64 etc/alternatives proc tmp dev");
            execute_cmd("chown judge *");
            execute_cmd("mount -o bind /bin bin");
            execute_cmd("mount -o remount,ro bin");
            execute_cmd("mount -o bind /usr usr");
            execute_cmd("mount -o remount,ro usr");
            execute_cmd("mount -o bind /lib lib");
            execute_cmd("mount -o remount,ro lib");
#ifndef __i386__
            execute_cmd("mount -o bind /lib64 lib64");
            execute_cmd("mount -o remount,ro lib64");
#endif
            execute_cmd("mount -o bind /etc/alternatives etc/alternatives");
            execute_cmd("mount -o remount,ro etc/alternatives");
            execute_cmd("mount -t proc /proc proc");
            execute_cmd("mount -o remount,ro proc");
            chroot(work_dir);
        }

        while (setgid(1536) != 0)
            sleep(1);
        while (setuid(1536) != 0)
            sleep(1);
        while (setresuid(1536, 1536, 1536) != 0)
            sleep(1);

        execvp(languages[lang].compile_cmd[0],
               (char * const *) languages[lang].compile_cmd);

        if (DEBUG)
            printf("Compile end!\n");
        exit(0);
    } else {
        int status = 0;

        waitpid(pid, &status, 0);
        if (lang == LANG_PYTHON3)
            status = get_file_size("ce.txt");
        if (DEBUG)
            printf("status = %d\n", status);
        execute_cmd("/bin/umount -f bin usr lib lib64 etc/alternatives proc dev 2>/dev/null");
        execute_cmd("/bin/umount -f %s/* 2>/dev/null", work_dir);
        umount(work_dir);
 
        return status;
    }
}

// 连接 mysql 数据库
int init_mysql_conn()
{
    char * mysql_unix_port = db.mysql_unix_port;
    if (strlen(mysql_unix_port) == 0) {
        mysql_unix_port = NULL;
    }
    conn = mysql_init(NULL);
    const char timeout = 30;
    mysql_options(conn, MYSQL_OPT_CONNECT_TIMEOUT, &timeout);

    if (!mysql_real_connect(conn, db.host_name, db.user_name, db.password,
                            db.db_name, db.port_number, mysql_unix_port, 0)) {
        write_log("%s", mysql_error(conn));
        return 0;
    }
    const char * utf8sql = "set names utf8";
    if (mysql_real_query(conn, utf8sql, strlen(utf8sql))) {
        write_log("%s", mysql_error(conn));
        return 0;
    }
    return 1;
}

void _create_solution_file(char *source, int lang)
{
    char src_pth[BUFFER_SIZE];
    // create the src file
    sprintf(src_pth, "Main.%s", languages[lang].file_ext);
    if (DEBUG)
        printf("Main=%s", src_pth);
    FILE *fp_src = fopen(src_pth, "we");
    fprintf(fp_src, "%s", source);
    fclose(fp_src);
}

void get_solution_info(int solution_id, int * p_id, int * lang)
{
    MYSQL_RES *res;
    MYSQL_ROW row;

    char sql[BUFFER_SIZE];
    // get the problem id and user id from Table:solution
    sprintf(sql,
            "SELECT problem_id, language, source FROM solution "
            "WHERE id=%d", solution_id);
    //printf("%s\n",sql);
    mysql_real_query(conn, sql, strlen(sql));
    res = mysql_store_result(conn);
    row = mysql_fetch_row(res);
    *p_id = atoi(row[0]);
    *lang = atoi(row[1]);
    _create_solution_file(row[2], *lang);
    if (res != NULL) {
        mysql_free_result(res);  // free the memory
        res = NULL;
    }
}

problem_struct get_problem_info(int p_id)
{
    problem_struct problem;
    problem.id = p_id;
    // get the problem info from Table:problem
    char sql[BUFFER_SIZE];
    MYSQL_RES *res;
    MYSQL_ROW row;
    sprintf(sql,
            "SELECT time_limit,memory_limit,spj FROM problem WHERE id=%d",
            p_id);
    mysql_real_query(conn, sql, strlen(sql));
    res = mysql_store_result(conn);
    row = mysql_fetch_row(res);
    problem.time_limit = atoi(row[0]);
    problem.memory_limit = atoi(row[1]);
    problem.isspj = (atoi(row[2]) == 1);
    if(res != NULL) {
        mysql_free_result(res); // free the memory
        res = NULL;
    }
    return problem;
}

char *escape(char s[], char t[])
{
    int i, j;
    for (i = j = 0; t[i] != '\0'; ++i) {
        if (t[i] == '\'') {
            s[j++] = '\'';
            s[j++] = '\\';
            s[j++] = '\'';
            s[j++] = '\'';
            continue;
        } else {
            s[j++] = t[i];
        }
    }
    s[j] = '\0';
    return s;
}

/**
 * 准备需要测试的数据点
 * 成功返回 0，失败返回1
 */
int prepare_files(char * filename, char * infile, int p_id,
                   char * work_dir, char * outfile, char * userfile,
                   int runner_id)
{
    char fname0[BUFFER_SIZE];
    char fname[BUFFER_SIZE];
    int namelen = strlen(filename);
    int res = 0;
    strncpy(fname0, filename, namelen - 3);
    fname0[namelen - 3] = 0;
    escape(fname, fname0);
    sprintf(infile, "%sdata/%d/%s.in", oj_home, p_id, fname);
    res = execute_cmd("/bin/cp '%s' %s/data.in", infile, work_dir);

    // 判断是输出文件是 out 还是 ans 为后缀
    sprintf(outfile, "%sdata/%d/%s.out", oj_home, p_id, fname0);
    if (access(outfile, R_OK) == -1) {
        sprintf(outfile, "%sdata/%d/%s.ans", oj_home, p_id, fname0);
        if (access(outfile, R_OK) == -1) {
            res = 1;
        }
    }
    sprintf(userfile, "%srun/%d/user.out", oj_home, runner_id);
    return res;
}

/**
 * 从文件中读取指定数目的字符到字符串中，每次最多读取bufsize - 1个字符，
 * 返回读取的字符数
 */
int read_file(char *buf, int bufsize, FILE *stream)
{
    char ch;
    int i = 0;
    while ((ch = fgetc(stream)) != EOF && i < bufsize) {
        if (ch == '"') {
            buf[i++] = '\\';
            buf[i++] = '\\';
            buf[i++] = '"';
        } else {
            buf[i++] = ch;
        }
    }
    // 移去末尾换行
    while (buf[i - 1] == '\n') {
        i--;
    }
    buf[i == bufsize ? i - 1 : i] = '\0';
    return i;
}

/**
 * 记录用户程序非 AC 时数据点的信息。
 * 读取文件数据时，只记录前100个字符，超出100用省略号代替。
 */
void record_data(problem_struct problem,
                 verdict_struct * verdict_res,
                 char * infile, char * outfile, char * userfile)
{
    const int rsize = 200; // 需要记录的字符数
    const char * omit_str = "...";
    int tmp_size;
    FILE * fp = NULL;
    if (problem.isspj) {
        fp = fopen("std_out.txt", "r");
    } else {
        fp = fopen("error.out", "r");
    }
    if (fp != NULL) {
        tmp_size = read_file(verdict_res->checker_log, rsize, fp);
        if (tmp_size >= rsize) {
            strcat(verdict_res->checker_log, omit_str);
        }
        fclose(fp);
    }

    FILE * in_file = fopen(infile, "r");
    FILE * out_file = fopen(outfile, "r");
    FILE * user_file = fopen(userfile, "r");
    
    if (in_file != NULL) {
        tmp_size = read_file(verdict_res->input, rsize, in_file);
        if (tmp_size >= rsize) {
            strcat(verdict_res->input, omit_str);
        }
        fclose(in_file);
    }
    
    if (out_file != NULL) {
        tmp_size = read_file(verdict_res->output, rsize, out_file);
        if (tmp_size >= rsize) {
            strcat(verdict_res->output, omit_str);
        }
        fclose(out_file);
    }

    if (user_file != NULL) {
        tmp_size = read_file(verdict_res->user_output, rsize, user_file);
        if (tmp_size >= rsize) {
            strcat(verdict_res->user_output, omit_str);
        }
        fclose(user_file);
    }
}

void run_solution(problem_struct problem, int lang, char * work_dir,
                  int usedtime)
{
    nice(19);
    // now the user is "judge"
    chdir(work_dir);
    // open the files
    freopen("data.in", "r", stdin);
    freopen("user.out", "w", stdout);
    freopen("error.out", "w", stderr);
    // trace me
    if(use_ptrace) {
        ptrace(PTRACE_TRACEME, 0, NULL, NULL);
    }
    // run me
    if (lang != LANG_JAVA && lang != LANG_PYTHON3) {
        chroot(work_dir);
    }

    while (setgid(1536) != 0)
        sleep(1);
    while (setuid(1536) != 0)
        sleep(1);
    while (setresuid(1536, 1536, 1536) != 0)
        sleep(1);

    // child
    // set the limit
    struct rlimit LIM; // time limit, file limit& memory limit
    // time limit
    if (oi_mode)
        LIM.rlim_cur = problem.time_limit + 1;
    else
        LIM.rlim_cur = (problem.time_limit - usedtime / 1000) + 1;
    LIM.rlim_max = LIM.rlim_cur;
    //if(DEBUG) printf("LIM_CPU=%d",(int)(LIM.rlim_cur));
    setrlimit(RLIMIT_CPU, &LIM);
    alarm(0);
    alarm(problem.time_limit * 5);

    // file limit
    LIM.rlim_max = STD_F_LIM + STD_MB;
    LIM.rlim_cur = STD_F_LIM;
    setrlimit(RLIMIT_FSIZE, &LIM);
    // proc limit
    if (lang == LANG_JAVA) {
        LIM.rlim_cur = LIM.rlim_max = 200;
    } else {
        LIM.rlim_cur = LIM.rlim_max = 1;
    }

    setrlimit(RLIMIT_NPROC, &LIM);

    // set the stack
    LIM.rlim_cur = STD_MB << 7;
    LIM.rlim_max = STD_MB << 7;
    setrlimit(RLIMIT_STACK, &LIM);
    // set the memory
    LIM.rlim_cur = STD_MB * problem.memory_limit / 2 * 3;
    LIM.rlim_max = STD_MB * problem.memory_limit * 2;
    if (lang == LANG_C || lang == LANG_CPP)
        setrlimit(RLIMIT_AS, &LIM);

    // run solution
    execvp(languages[lang].run_cmd[0],
           (char * const *) languages[lang].run_cmd);

    // sleep(1);
    fflush(stderr);
    exit(0);
}

int fix_python_mis_judge(char *work_dir, verdict_struct * verdict_res,
                         int mem_lmt)
{
    int comp_res = OJ_AC;

    comp_res = execute_cmd("/bin/grep 'MemoryError'  %s/error.out", work_dir);

    if (!comp_res) {
        printf("Python need more Memory!");
        verdict_res->verdict = OJ_ML;
        verdict_res->memory = mem_lmt * STD_MB;
    }

    return comp_res;
}

int fix_java_mis_judge(char *work_dir, verdict_struct * verdict_res,
                       int mem_lmt)
{
    int comp_res = OJ_AC;
    execute_cmd("chmod 700 %s/error.out", work_dir);
    if (DEBUG)
        execute_cmd("cat %s/error.out", work_dir);
    comp_res = execute_cmd("/bin/grep 'Exception'  %s/error.out", work_dir);
    if (!comp_res) {
        printf("Exception reported\n");
        verdict_res->verdict = OJ_RE;
    }
    execute_cmd("cat %s/error.out", work_dir);

    comp_res = execute_cmd(
            "/bin/grep 'java.lang.OutOfMemoryError'  %s/error.out", work_dir);

    if (!comp_res) {
        printf("JVM need more Memory!");
        verdict_res->verdict = OJ_ML;
        verdict_res->memory = mem_lmt * STD_MB;
    }

    if (!comp_res) {
        printf("JVM need more Memory or Threads!");
        verdict_res->verdict = OJ_ML;
        verdict_res->memory = mem_lmt * STD_MB;
    }
    comp_res = execute_cmd("/bin/grep 'Could not create'  %s/error.out",
            work_dir);
    if (!comp_res) {
        printf("jvm need more resource,tweak -Xmx(OJ_JAVA_BONUS) Settings");
        verdict_res->verdict = OJ_RE;
        //topmemory=0;
    }
    return comp_res;
}

int special_judge(char* oj_home, int problem_id, verdict_struct * verdict_res,
                  char *infile, char *outfile, char *userfile)
{
    pid_t pid = fork();
    int ret = 0;
    if (pid == 0) {
        while (setgid(1536) != 0)
            sleep(1);
        while (setuid(1536) != 0)
            sleep(1);
        while (setresuid(1536, 1536, 1536) != 0)
            sleep(1);

        struct rlimit LIM; // time limit, file limit& memory limit

        LIM.rlim_cur = 5;
        LIM.rlim_max = LIM.rlim_cur;
        setrlimit(RLIMIT_CPU, &LIM);
        alarm(0);
        alarm(10);

        // file limit
        LIM.rlim_max = STD_F_LIM + STD_MB;
        LIM.rlim_cur = STD_F_LIM;
        setrlimit(RLIMIT_FSIZE, &LIM);
        freopen("std_out.txt", "w", stderr);
        ret = execute_cmd("%sdata/%d/spj '%s' '%s' %s", oj_home, problem_id,
                          infile, userfile, outfile);
        fflush(stderr);
        if (ret) { // WA
            exit(1);
        } else { //AC
            exit(0);
        }
    } else {
        int status;
        waitpid(pid, &status, 0);
        ret = WEXITSTATUS(status);
    }
    verdict_res->checker_exit_code = ret;
    return ret ? OJ_WA : OJ_AC;
}

void judge_solution(problem_struct problem,
                    verdict_struct * verdict_res,
                    char * infile, char * outfile, char * userfile,
                    int * PEflg, int lang, char * work_dir, int solution_id)
{
    int mem_lmt = problem.memory_limit;
    int comp_res;
    if (verdict_res->verdict == OJ_AC && verdict_res->time > problem.time_limit * 1000)
        verdict_res->verdict = OJ_TL;
    if (verdict_res->memory > mem_lmt * STD_MB)
        verdict_res->verdict = OJ_ML;
    // compare
    if (verdict_res->verdict == OJ_AC) {
        if (problem.isspj) {
            comp_res = special_judge(oj_home, problem.id, verdict_res,
                                     infile, outfile, userfile);
        } else {
            comp_res = compare(outfile, userfile);
        }
        if (comp_res == OJ_PE) {
            *PEflg = OJ_PE;
        }
        verdict_res->verdict = comp_res;
    }
    //jvm popup messages, if don't consider them will get miss-WrongAnswer
    if (lang == LANG_JAVA) {
        comp_res = fix_java_mis_judge(work_dir, verdict_res, mem_lmt);
    }
    if (lang == LANG_PYTHON3) {
        comp_res = fix_python_mis_judge(work_dir, verdict_res, mem_lmt);
    }
}

void print_runtimeerror(char * err)
{
    FILE *ferr = fopen("error.out", "a+");
    fprintf(ferr, "Runtime Error:%s\n", err);
    fclose(ferr);
}

void watch_solution(problem_struct problem,
                    verdict_struct * verdict_res,
                    pid_t pidApp,
                    char * infile, char * userfile, char * outfile,
                    int solution_id, int lang, char * work_dir)
{
    int mem_lmt = problem.memory_limit;
    int isspj = problem.isspj;

    if (DEBUG)
        printf("pid=%d [Solution ID: %d] judging %s\n",
                pidApp, solution_id, infile);

    int status, sig, exitcode;
    struct user_regs_struct reg;
    struct rusage ruse;
    bool first_run = true;
    for (;;) {
        // check the usage
        wait4(pidApp, &status, __WALL, &ruse);
        if(first_run){ // 
            ptrace(PTRACE_SETOPTIONS, pidApp, NULL, PTRACE_O_TRACESYSGOOD 
                   | PTRACE_O_TRACEEXIT 
                //    |PTRACE_O_EXITKILL 
                //  |PTRACE_O_TRACECLONE 
                //  |PTRACE_O_TRACEFORK 
                //  |PTRACE_O_TRACEVFORK
            );
        }
        if (verdict_res->memory < getpagesize() * ruse.ru_minflt)
            verdict_res->memory = getpagesize() * ruse.ru_minflt;

        if (verdict_res->memory > mem_lmt * STD_MB) {
            if (DEBUG)
                printf("out of memory %d\n", verdict_res->memory);
            if (verdict_res->verdict == OJ_AC)
                verdict_res->verdict = OJ_ML;
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }
        //sig = status >> 8;/*status >> 8 EXITCODE*/
        if (WIFEXITED(status))
            break;
        if ((lang == LANG_C || lang == LANG_CPP) && get_file_size("error.out")) {
            verdict_res->verdict = OJ_RE;
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }

        if (!isspj && get_file_size(userfile) > get_file_size(outfile) * 2 + 1024) {
            verdict_res->verdict = OJ_OL;
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }

        exitcode = WEXITSTATUS(status);
        /*exitcode == 5 waiting for next CPU allocation
         * ruby using system to run,exit 17 ok
         * Runtime Error:Unknown signal xxx need be added here  
         */
        if (((lang == LANG_JAVA || lang == LANG_PYTHON3) && exitcode == 17) ||
            exitcode == 0x05 || exitcode == 0 || exitcode == 133) {
            //go on and on
            ;
        } else {
            if (DEBUG) {
                printf("status>>8=%d\n", exitcode);
            }
            //psignal(exitcode, NULL);

            if (verdict_res->verdict == OJ_AC) {
                switch (exitcode) {
                case SIGCHLD:
                case SIGALRM:
                    alarm(0);
                case SIGKILL:
                case SIGXCPU:
                    verdict_res->verdict = OJ_TL;
                    break;
                case SIGXFSZ:
                    verdict_res->verdict = OJ_OL;
                    break;
                default:
                    verdict_res->verdict = OJ_RE;
                }
                print_runtimeerror(strsignal(exitcode));
            }
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }
        if (WIFSIGNALED(status)) {
            /* WIFSIGNALED: if the process is terminated by signal
             *
             * psignal(int sig, char *s)，like perror(char *s)，print out s,
             * with error msg from system of sig  
             * sig = 5 means Trace/breakpoint trap
             * sig = 11 means Segmentation fault
             * sig = 25 means File size limit exceeded
             */
            sig = WTERMSIG(status);

            if (DEBUG) {
                printf("WTERMSIG=%d\n", sig);
                psignal(sig, NULL);
            }
            if (verdict_res->verdict == OJ_AC) {
                switch (sig) {
                case SIGCHLD:
                case SIGALRM:
                    alarm(0);
                case SIGKILL:
                case SIGXCPU:
                    verdict_res->verdict = OJ_TL;
                    break;
                case SIGXFSZ:
                    verdict_res->verdict = OJ_OL;
                    break;
                default:
                    verdict_res->verdict = OJ_RE;
                }
                print_runtimeerror(strsignal(sig));
            }
            break;
        }
        // comment from http://www.felix021.com/blog/read.php?1662

        // WIFSTOPPED: return true if the process is paused or stopped while
        // ptrace is watching on it
        // WSTOPSIG: get the signal if it was stopped by signal

        // check the system calls
        ptrace(PTRACE_GETREGS, pidApp, NULL, &reg);
        call_id = (unsigned int)reg.REG_SYSCALL % CALL_ARRAY_SIZE;
        if (call_counter[call_id]) {
            //call_counter[reg.REG_SYSCALL]--;
        } else if (record_call) {
            call_counter[call_id] = 1;
        } else { //do not limit JVM syscall for using different JVM
            verdict_res->verdict = OJ_RE;
            char error[BUFFER_SIZE];
            sprintf(error, "[ERROR] A not allowed system call.\nCall ID:%u",
                call_id);
            write_log(error);
            print_runtimeerror(error);
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
        }
        ptrace(PTRACE_SYSCALL, pidApp, NULL, NULL);
        first_run = false;
    }
    verdict_res->time += (ruse.ru_utime.tv_sec * 1000 + ruse.ru_utime.tv_usec / 1000);
    verdict_res->time += (ruse.ru_stime.tv_sec * 1000 + ruse.ru_stime.tv_usec / 1000);
}

void clean_workdir(char * work_dir)
{
    umount(work_dir);
    if (DEBUG) {
        execute_cmd("/bin/rm -rf %s/log/* 2>/dev/null", work_dir);
        execute_cmd("mkdir %s/log/ 2>/dev/null", work_dir);
        execute_cmd("/bin/mv %s/* %s/log/ 2>/dev/null", work_dir, work_dir);
    } else {
        execute_cmd("mkdir %s/log/ 2>/dev/null", work_dir);
        execute_cmd("/bin/mv %s/* %s/log/ 2>/dev/null", work_dir, work_dir);
        execute_cmd("/bin/rm -rf %s/log/* 2>/dev/null", work_dir);
    }
}

void display_usage()
{
    fprintf(stderr, "Usage:judge -s solution -r runner\n");
    fprintf(stderr, "  -s [n] .. --solution [n]\n");
    fprintf(stderr, "              Solution id\n");
    fprintf(stderr, "  -r [n] .. --runner [n]\n");
    fprintf(stderr, "              Runner id, default 0.\n");
    fprintf(stderr, "  -d ...... --debug\n");
    fprintf(stderr, "              Enable debug mode.\n");
    fprintf(stderr, "  -o ...... --oi\n");
    fprintf(stderr, "              Enable oi mode.\n");
    fprintf(stderr, "  -h ...... --help\n");
    fprintf(stderr, "              Dsiplay help (from command line).\n");
}

void init_parameters(int argc, char ** argv, int * solution_id, int * runner_id)
{
    *solution_id = -1;
    *runner_id = 0;
    int ch;
    struct option long_options[] = {
        {"solution", required_argument, 0, 's'},
        {"runner", no_argument, 0, 'r'},
        {"debug", no_argument, 0,  'd'},
        {"oi",  no_argument, 0, 'o'},
        {"help",  no_argument, 0, 'h'}
    };
    while ((ch = getopt_long(argc, argv, "s:r:dho", long_options, 0)) != -1) {
        switch (ch) {
            case 'd':
                DEBUG = 1;
                fprintf(stderr, "Enable DEBUG mode.\n");
                break;
            case 's':
                *solution_id = atoi(optarg);
                break;
            case 'r':
                *runner_id = atoi(optarg);
                break;
            case 'o':
                oi_mode = 1;
                fprintf(stderr, "Enable OI mode.\n");
                break;
            case 'h':
                display_usage();
                exit(1);
        }
    }
    if (*solution_id < 0) {
        display_usage();
        exit(1);
    }
    getcwd(oj_home, sizeof(oj_home));
    int len = strlen(oj_home);
    oj_home[len] = '/';
    oj_home[len + 1] = '\0';

    chdir(oj_home); // change the dir
}

void mk_shm_workdir(char * work_dir)
{
    char shm_path[BUFFER_SIZE];
    sprintf(shm_path, "/dev/shm/jnoj%s", work_dir);
    execute_cmd("/bin/mkdir -p %s", shm_path);
    execute_cmd("/bin/ln -s %s %s", shm_path, oj_home);
    execute_cmd("/bin/chown judge %s ", shm_path);
    execute_cmd("chmod 755 %s ", shm_path);
    //sim need a soft link in shm_dir to work correctly
    sprintf(shm_path, "/dev/shm/jnoj%s", oj_home);
    execute_cmd("/bin/ln -s %sdata %s", oj_home, shm_path);
}

cJSON * create_case_object(verdict_struct verdict_res)
{
    cJSON * case_item = cJSON_CreateObject();
    cJSON_AddItemToObject(case_item, "verdict", cJSON_CreateNumber(verdict_res.verdict));
    cJSON_AddItemToObject(case_item, "time", cJSON_CreateNumber(verdict_res.time));
    cJSON_AddItemToObject(case_item, "memory", cJSON_CreateNumber(verdict_res.memory >> 10));
    cJSON_AddItemToObject(case_item, "exit_code", cJSON_CreateNumber(verdict_res.exit_code));
    cJSON_AddItemToObject(case_item, "input", cJSON_CreateString(verdict_res.input));
    cJSON_AddItemToObject(case_item, "output", cJSON_CreateString(verdict_res.output));
    cJSON_AddItemToObject(case_item, "user_output", cJSON_CreateString(verdict_res.user_output));
    cJSON_AddItemToObject(case_item, "checker_exit_code", cJSON_CreateNumber(verdict_res.checker_exit_code));
    cJSON_AddItemToObject(case_item, "checker_log", cJSON_CreateString(verdict_res.checker_log));
    return case_item;
}

/**
 * 读取 OI 子任务设定的 config
 * config 内容如下：
 * name[start-end] score
 * 例如：
 * data[1-12] 10
 * data[13-23] 90
 * 表示 data1.in, data2.in, ..., data12.in 打包成一个子任务，分数是10分
 * data13.in, data14.in, ..., data23.in 打包成一个子任务，分数是90分
 */
subtask_struct * read_oi_mode_substask_configfile(char * configfile_path)
{
    FILE * fp = fopen(configfile_path, "r");
    if (fp == NULL) {
        return NULL;
    }
    char buf[BUFFER_SIZE];
    subtask_struct * head = (subtask_struct *)malloc(sizeof(subtask_struct));
    subtask_struct * subtask_rear = head;
    subtask_struct * subtask_node;
    if (head == NULL) {
        return NULL;
    }
    head->next = NULL;
    while (fgets(buf, BUFFER_SIZE - 1, fp)) {
        char name_prefix[NAME_MAX] = {0};
        int begin = 0, end = 0, score = 0;
        int i = 0, j = 0;
        int found_num = 0;
        // 跳过空格
        while (buf[i] && isspace(buf[i])) {
            i++;
        }
        // 跳过空行
        if (!buf[i]) {
            continue;
        }
        while (buf[i] && buf[i] != '[') {
            name_prefix[j++] = buf[i++];
        }
        if (buf[i] != '[') {
            printf("Configuration file format error\n");
            break;
        }
        i++; // 跳过 '['
        // 读取开始区间
        while (buf[i] && buf[i] != '-' && buf[i] != ']') {
            if (isdigit(buf[i])) {
                begin = begin * 10 + buf[i] - '0';
                found_num = 1;
            }
            i++;
        }
        if (buf[i] == '-') {
            i++; // 跳过 '-'
            while (buf[i] && buf[i] != ']') {
                if (isdigit(buf[i])) {
                    end = end * 10 + buf[i] - '0';
                }
                i++;
            }
        } else {
            end = begin;
        }
        if (buf[i] != ']') {
            printf("Configuration file format error\n");
            break;
        }
        i++; // 跳过 ']'
        // 跳过空格
        while (buf[i] && isspace(buf[i])) {
            i++;
        }
        while (buf[i] && isdigit(buf[i])) {
            score = score * 10 + buf[i++] - '0';
        }
        if (score <= 0 || begin > end) {
            printf("Configuration file format error\n");
            break;
        }
        // 储存子任务的数据
        subtask_node = (subtask_struct *)malloc(sizeof(subtask_struct));
        subtask_node->test_count = end - begin + 1;
        subtask_node->score = score;
        if (found_num) {
            for (i = begin, j = 0; i <= end; i++, j++) {
                subtask_node->test_input_name[j] = (char *)malloc(sizeof(char) * NAME_MAX);
                snprintf(subtask_node->test_input_name[j], NAME_MAX,"%s%d.in", name_prefix, i);
            }
        } else {
            subtask_node->test_input_name[j] = (char *)malloc(sizeof(char) * NAME_MAX);
            snprintf(subtask_node->test_input_name[j], NAME_MAX, "%s.in", name_prefix);
        }
        subtask_rear->next = subtask_node;
        subtask_rear = subtask_node; 
        subtask_cnt++;
        if (DEBUG) {
            printf("cnt = %d, name_prefix = %s, begin = %d, end = %d, score = %d\n",
                   subtask_cnt, name_prefix, begin, end, score);
        }
    }
    fclose(fp);
    subtask_rear->next = NULL;
    if (subtask_cnt == 0) {
        free(head);
        return NULL;
    }
    return head;
}

void read_files_run_solution(verdict_struct * verdict_res,
                             problem_struct problem, char * infile_name,
                             char * infile,  char * outfile, char * userfile,
                             char * work_dir, int * is_pe, int solution_id,
                             int problem_id, int runner_id, int lang)
{
    verdict_res->score = 0;
    verdict_res->verdict = OJ_AC;
    verdict_res->time = 0;
    verdict_res->memory = 0;
    verdict_res->exit_code = 0;
    verdict_res->checker_exit_code = 0;
    memset(verdict_res->input, 0, sizeof(verdict_res->input));
    memset(verdict_res->output, 0, sizeof(verdict_res->output));
    memset(verdict_res->user_output, 0, sizeof(verdict_res->user_output));
    memset(verdict_res->checker_log, 0, sizeof(verdict_res->checker_log));

    init_syscalls_limits(lang);

    int tmp = prepare_files(infile_name, infile, problem_id, work_dir,
                    outfile, userfile, runner_id);
    if (tmp) {
        verdict_res->verdict = OJ_NT;
        return;
    }

    pid_t pid = fork();
    if (pid == 0) {
        run_solution(problem, lang, work_dir, verdict_res->time);
    } else {
        watch_solution(problem, verdict_res, pid, infile, userfile, outfile,
                solution_id, lang, work_dir);
        judge_solution(problem, verdict_res, infile,
                outfile, userfile, is_pe, lang, work_dir,
                solution_id);
    }
}

int main(int argc, char** argv)
{
    char work_dir[BUFFER_SIZE];
    int solution_id;
    int runner_id;
    int problem_id, lang;
    problem_struct problem;
    verdict_struct verdict_res;
    subtask_struct * subtask_list; // 子任务链表
    double score = 0; // OI mode score
    init_parameters(argc, argv, &solution_id, &runner_id);

    init_mysql_conf();

    if (!init_mysql_conn()) {
        exit(0); //exit if mysql is down
    }

    //set work directory to start running & judging
    sprintf(work_dir, "%srun/%d/", oj_home, runner_id);
    if (opendir(work_dir) == NULL) {
        execute_cmd("/bin/mkdir -p %s", work_dir);
        execute_cmd("/bin/chown judge %s ", work_dir);
        execute_cmd("chmod 777 %s ", work_dir);
    }
    clean_workdir(work_dir);
    if (shm_run) {
        mk_shm_workdir(work_dir);
    }
    chdir(work_dir);
    get_solution_info(solution_id, &problem_id, &lang);

    //get the limit
    problem = get_problem_info(problem_id);

    //java is lucky
    // Clang Clang++ not VM or Script
    if (lang >= 2) {
        // the limit for java
        problem.time_limit = problem.time_limit + java_time_bonus;
        problem.memory_limit = problem.memory_limit + java_memory_bonus;
        // copy java.policy
        if (lang == LANG_JAVA) {
            execute_cmd("/bin/cp %s/etc/java0.policy %s/java.policy", oj_home,
                work_dir);
            execute_cmd("chmod 755 %s/java.policy", work_dir);
            execute_cmd("chown judge %s/java.policy", work_dir);
        }
    }

    //never bigger than judged set value;
    if (problem.time_limit > 300 || problem.time_limit < 1)
        problem.time_limit = 300;
    if (problem.memory_limit > 1024 || problem.memory_limit < 1)
        problem.memory_limit = 1024;

    // compile
    // set the result to compiling
    if (compile(lang, work_dir) != 0) {
        addceinfo(solution_id);
        update_solution(solution_id, OJ_CE, 0, 0, "0/0", 0);
        update_problem_stat(problem_id);
        mysql_close(conn);
        clean_workdir(work_dir);
        write_log("[Solution ID: %d] Compile Error", solution_id);
        exit(0);
    } else {
        update_solution(solution_id, OJ_RI, 0, 0, "0/0", 0);
        umount(work_dir);
    }

    char fullpath[BUFFER_SIZE];
    char infile[BUFFER_SIZE];
    char outfile[BUFFER_SIZE];
    char userfile[BUFFER_SIZE];
    char oi_substask_configfile[BUFFER_SIZE]; // OI 子任务设定的 config 文件路径

    // the fullpath of data dir
    sprintf(fullpath, "%sdata/%d", oj_home, problem_id);
    sprintf(oi_substask_configfile, "%sdata/%d/config", oj_home, problem_id);

    // open DIRs
    DIR *dp;
    if ((dp = opendir(fullpath)) == NULL) {
        update_solution(solution_id, OJ_NT, 0, 0, "0/0", 0);
        write_log("No such test data dir:%s!\n", fullpath);
        mysql_close(conn);
        exit(-1);
    }

    int run_result, is_pe;
    run_result = is_pe = OJ_AC;
    int topmemory = 0, max_case_time = 0;

    struct dirent **namelist;

    int test_total_count = scandir(fullpath, &namelist, input_file_filter, versionsort);
    int pass_total_test_count = 0;
    if (test_total_count <= 0) {
        update_solution(solution_id, OJ_NT, 0, 0, "0/0", 0);
        write_log("No input files!\n");
        mysql_close(conn);
        exit(-1);
    }

    // OI 子任务设定的 config 文件
    subtask_list = read_oi_mode_substask_configfile(oi_substask_configfile);
    // 读取不成功，则采用满分100分分给每个测试点
    if (subtask_list == NULL) {
        subtask_list = (subtask_struct *)malloc(sizeof(subtask_struct));
        subtask_list->next = (subtask_struct *)malloc(sizeof(subtask_struct));
        subtask_list->next->test_count = 0;
        subtask_list->next->next = NULL;
        for (int i = 0; i < test_total_count; i++) {
            subtask_list->next->test_input_name[i] = (char *)malloc(sizeof(char) * (strlen(namelist[i]->d_name) + 1));
            strcpy(subtask_list->next->test_input_name[i], namelist[i]->d_name);
            subtask_list->next->test_count++;
            free(namelist[i]);
        }
        free(namelist);
    }

    if (subtask_list == NULL) {
        update_solution(solution_id, OJ_SE, 0, 0, "0/0", 0);
        write_log("Unable to allocate memory!\n");
        mysql_close(conn);
        exit(-1);
    }

    // create json struct
    cJSON * judge_json_object = cJSON_CreateObject();
    cJSON * subtask_json_array = cJSON_CreateArray();
    cJSON * subtask_json_object = NULL;
    cJSON * cases_array = NULL;
    cJSON_AddItemToObject(judge_json_object, "subtasks", subtask_json_array);

    if (oi_mode) {
        test_total_count = 0; //OI 模式下的测试总数，根据实际测试点数据来计算
    }

    subtask_struct * subtask_node = subtask_list;
    int test_result_rec[OJ_NT + 1]; // 记录各个测试点的通过结果的数量
    memset(test_result_rec, 0, sizeof(test_result_rec));

    // 遍历子任务链表
    while (subtask_node->next != NULL) {
        subtask_node = subtask_node->next;
        int pass_count = 0;
        subtask_json_object = cJSON_CreateObject();
        cases_array = cJSON_CreateArray();
        cJSON_AddItemToArray(subtask_json_array, subtask_json_object);
        cJSON_AddItemToObject(subtask_json_object, "cases", cases_array);
        // 遍历数据点进行测评
        for (int i = 0; i < subtask_node->test_count; i++) {
            read_files_run_solution(&verdict_res, problem, subtask_node->test_input_name[i],
                                    infile, outfile, userfile, work_dir, &is_pe, solution_id,
                                    problem_id, runner_id, lang);
            max_case_time = verdict_res.time > max_case_time ? verdict_res.time : max_case_time;
            topmemory = verdict_res.memory > topmemory ? verdict_res.memory : topmemory;
            run_result = verdict_res.verdict;
            test_result_rec[run_result]++;
            if (run_result == OJ_AC) {
                pass_count++;
            } else {
                // 记录该数据点的运行信息
                record_data(problem, &verdict_res, infile, outfile, userfile);
            }
            cJSON * case_json_object = create_case_object(verdict_res);
            cJSON_AddItemToArray(cases_array, case_json_object);
            if (oi_mode) {
                test_total_count++;
            }
            // 非OI模式出错后不再测评
            if (!oi_mode && run_result != OJ_AC && run_result != OJ_PE) {
                break;
            }
            // OI 模式下存在子任务时，若子任务出错则不再测评该子任务
            if (oi_mode && subtask_cnt != 0 && run_result != OJ_AC && run_result != OJ_PE) {
                break;
            }
        }
        // 没有子任务情况下所有数据点总分100分。
        if (subtask_cnt == 0) {
            score = 100.0 * pass_count / subtask_node->test_count;
        } else if (pass_count == subtask_node->test_count) {
            score += subtask_node->score;
        }
        pass_total_test_count += pass_count;
        cJSON_AddItemToObject(subtask_json_object, "score",
                              cJSON_CreateNumber((int)subtask_node->score));
    }
    if (DEBUG) {
        printf("%s\n", cJSON_Print(judge_json_object));
    }
    if (run_result == OJ_AC && is_pe == OJ_PE) {
        run_result = OJ_PE;
    }
    if (run_result == OJ_TL) {
        max_case_time = problem.time_limit * 1000;
    }

    // OI 模式下，没有通过所有数据点时，取报错信息最多的那个点作为结果
    if (oi_mode && pass_total_test_count != test_total_count) {
        int tmp_cnt = OJ_AC + 1;
        for (int i = tmp_cnt; i <= OJ_NT; i++) {
            if (test_result_rec[tmp_cnt] < test_result_rec[i]) {
                tmp_cnt = i;
            }
        }
        run_result = tmp_cnt;
    }
    char pass_info[BUFFER_SIZE];
    sprintf(pass_info, "%d/%d", pass_total_test_count, test_total_count);
    update_solution(solution_id, run_result, max_case_time, topmemory >> 10, pass_info, (int)score);
    update_problem_stat(problem_id);
    update_solution_info(solution_id, cJSON_PrintUnformatted(judge_json_object));
    clean_workdir(work_dir);
    mysql_close(conn);
    closedir(dp);
    write_log("[Solution ID: %d] Result = %d", solution_id, run_result);
    return 0;
}
