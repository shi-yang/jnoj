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
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <dirent.h>
#include <unistd.h>
#include <time.h>
#include <stdarg.h>
#include <ctype.h>
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
#include "okcalls.h"
#include "common.h"
#include "language.h"

#define STD_MB 1048576
#define STD_T_LIM 2
#define STD_F_LIM (STD_MB<<5)
#define STD_M_LIM (STD_MB<<7)
#define BUFFER_SIZE 5120


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

struct problem_struct {
    int id;
    int spj_lang;
    int solution_lang;
    int memory_limit;
    int time_limit;
};

static int DEBUG = 0;

static char oj_home[BUFFER_SIZE];

static int max_running;
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

static const char * tbname = "polygon_status";
//static int sleep_tmp;

#ifdef _mysql_h
MYSQL *conn;
#endif

//static char buf[BUFFER_SIZE];

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

int is_input_file(const char fname[])
{
    int l = strlen(fname);
    if (l <= 3 || strcmp(fname + l - 3, ".in") != 0)
        return 0;
    else
        return l - 3;
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
int call_counter[CALL_ARRAY_SIZE];
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

int after_equal(char * c)
{
    int i = 0;
    for (; c[i] != '\0' && c[i] != '='; i++)
        ;
    return ++i;
}

void trim(char * c)
{
    char buf[BUFFER_SIZE];
    char * start, *end;
    strcpy(buf, c);
    start = buf;
    while (isspace(*start))
        start++;
    end = start;
    while (!isspace(*end))
        end++;
    *end = '\0';
    strcpy(c, start);
}

bool read_buf(char * buf, const char * key, char * value)
{
    if (strncmp(buf, key, strlen(key)) == 0) {
        strcpy(value, buf + after_equal(buf));
        trim(value);
        if (DEBUG)
            printf("%s\n", value);
        return 1;
    }
    return 0;
}

void read_int(char * buf, const char * key, int * value)
{
    char buf2[BUFFER_SIZE];
    if (read_buf(buf, key, buf2))
        sscanf(buf2, "%d", value);
}

FILE * read_cmd_output(const char * fmt, ...)
{
    char cmd[BUFFER_SIZE];

    FILE * ret = NULL;
    va_list ap;

    va_start(ap, fmt);
    vsprintf(cmd, fmt, ap);
    va_end(ap);
    if (DEBUG)
        printf("%s\n", cmd);
    ret = popen(cmd, "r");

    return ret;
}

// read the configue file
void init_mysql_conf()
{
    FILE *fp = NULL;
    char buf[BUFFER_SIZE];
    db.port_number = 3306;
    max_running = 3;
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
            read_int(buf, "OJ_PORT_NUMBER", &db.port_number);
            read_int(buf, "OJ_JAVA_TIME_BONUS", &java_time_bonus);
            read_int(buf, "OJ_JAVA_MEMORY_BONUS", &java_memory_bonus);
            read_buf(buf, "OJ_JAVA_XMS", java_xms);
            read_buf(buf, "OJ_JAVA_XMX", java_xmx);
            read_int(buf, "OJ_OI_MODE", &oi_mode);
            read_int(buf, "OJ_FULL_DIFF", &full_diff);
            read_int(buf, "OJ_SHM_RUN", &shm_run);
            read_int(buf, "OJ_USE_PTRACE", &use_ptrace);
            read_int(buf, "OJ_COMPILE_CHROOT", &compile_chroot);
        }
        fclose(fp);
    }
}

void update_solution(int problem_id, int result, int time, int memory)
{
    if (result == OJ_TL && memory == 0)
        result = OJ_ML;
    char sql[BUFFER_SIZE];

    sprintf(sql,
            "UPDATE %s SET result=%d,time=%d,memory=%d "
            "WHERE problem_id=%d LIMIT 1%c",
            tbname, result, time, memory, problem_id, 0);

    // printf("sql= %s\n",sql);
    if (mysql_real_query(conn, sql, strlen(sql))) {
        printf("Update failed! %s\n",mysql_error(conn));
    }
}

void addceinfo(int solution_id)
{
    char sql[(1 << 16)], *end;
    char ceinfo[(1 << 16)], *cend;
    FILE *fp = fopen("ce.txt", "re");
    cend = ceinfo;
    while (fgets(cend, 1024, fp)) {
        cend += strlen(cend);
        if (cend - ceinfo > 40000)
            break;
    }
    cend = 0;
    end = sql;
    strcpy(end, "UPDATE polygon_status SET info =`");
    end += strlen(sql);
    *end++ = '\'';
    end += sprintf(end, "%d", solution_id);
    *end++ = '\'';
    *end++ = ',';
    *end++ = '\'';
    end += mysql_real_escape_string(conn, end, ceinfo, strlen(ceinfo));
    *end++ = '\'';
    *end++ = '`';
    *end = 0;
    // printf("%s\n",ceinfo);
    if (mysql_real_query(conn, sql, end - sql))
        printf("%s\n", mysql_error(conn));
    fclose(fp);
}

// write runtime error message back to database
void _add_solution_info_mysql(int solution_id, const char * filename)
{
    char sql[(1 << 16)], *end;
    char reinfo[(1 << 16)], *rend;
    FILE *fp = fopen(filename, "re");
    rend = reinfo;
    while (fgets(rend, 1024, fp)) {
        rend += strlen(rend);
        if (rend - reinfo > 40000)
            break;
    }
    rend = 0;
    end = sql;
    strcpy(end, "UPDATE polygon_status SET info =`");
    end += strlen(sql);
    *end++ = '\'';
    end += sprintf(end, "%d", solution_id);
    *end++ = '\'';
    *end++ = ',';
    *end++ = '\'';
    end += mysql_real_escape_string(conn, end, reinfo, strlen(reinfo));
    *end++ = '\'';
    *end++ = '`';
    *end = 0;
    //      printf("%s\n",ceinfo);
    if (mysql_real_query(conn, sql, end - sql))
        printf("%s\n", mysql_error(conn));
    fclose(fp);
}

void addreinfo(int solution_id)
{
    _add_solution_info_mysql(solution_id, "error.out");
}

void adddiffinfo(int solution_id)
{
    _add_solution_info_mysql(solution_id, "diff.out");
}

void addcustomout(int solution_id)
{
    _add_solution_info_mysql(solution_id, "user.out");
}

void umount(char * work_dir)
{
    execute_cmd("/bin/umount -f %s/proc", work_dir);
    execute_cmd("/bin/umount -f %s/dev ", work_dir);
    execute_cmd("/bin/umount -f %s/lib ", work_dir);
    execute_cmd("/bin/umount -f %s/lib64 ", work_dir);
    execute_cmd("/bin/umount -f %s/etc/alternatives ", work_dir);
    execute_cmd("/bin/umount -f %s/usr ", work_dir);
    execute_cmd("/bin/umount -f %s/bin ", work_dir);
    execute_cmd("/bin/umount -f %s/proc ", work_dir);
    execute_cmd("/bin/umount -f bin usr lib lib64 etc/alternatives proc dev ");
    execute_cmd("/bin/umount -f bin usr lib lib64 proc dev ");
    execute_cmd("/bin/umount -f %s/* ",work_dir);
    execute_cmd("/bin/umount -f %s/log/* ",work_dir);
    execute_cmd("/bin/umount -f %s/log/etc/alternatives ", work_dir);
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
        LIM.rlim_max = 10 * STD_MB;
        LIM.rlim_cur = 10 * STD_MB;
        setrlimit(RLIMIT_FSIZE, &LIM);

        if (lang == LANG_JAVA) {
            LIM.rlim_max = STD_MB << 11;
            LIM.rlim_cur = STD_MB << 11;    
        } else {
            LIM.rlim_max = STD_MB * 512;
            LIM.rlim_cur = STD_MB * 512;
        }
        setrlimit(RLIMIT_AS, &LIM);
        freopen("ce.txt", "w", stderr);

        if (compile_chroot && lang != LANG_JAVA && lang != LANG_PYTHON3) {
            execute_cmd("mkdir -p "
                        "bin usr lib lib64 etc/alternatives proc tmp dev");
            execute_cmd("chown judge *");
            execute_cmd("mount -o bind /bin bin");
            execute_cmd("mount -o bind /usr usr");
            execute_cmd("mount -o bind /lib lib");
#ifndef __i386
            execute_cmd("mount -o bind /lib64 lib64");
#endif
            execute_cmd("mount -o bind /etc/alternatives etc/alternatives");
            execute_cmd("mount -o bind /proc proc");
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
        execute_cmd("/bin/umount -f bin usr lib lib64 etc/alternatives proc dev 2>&1 >/dev/null");
        execute_cmd("/bin/umount -f %s/* 2>&1 >/dev/null", work_dir);
        umount(work_dir);
 
        return status;
    }
}

int get_proc_status(int pid, const char * mark)
{
    char fn[BUFFER_SIZE], buf[BUFFER_SIZE];
    int ret = 0;
    FILE * pf = fopen(fn, "re");
    int m = strlen(mark);
    sprintf(fn, "/proc/%d/status", pid);
    while (pf && fgets(buf, BUFFER_SIZE - 1, pf)) {
        buf[strlen(buf) - 1] = 0;
        if (strncmp(buf, mark, m) == 0) {
            sscanf(buf + m + 1, "%d", &ret);
        }
    }
    if (pf)
        fclose(pf);
    return ret;
}

// 连接 mysql 数据库
int init_mysql_conn()
{
    conn = mysql_init(NULL);
    const char timeout = 30;
    mysql_options(conn, MYSQL_OPT_CONNECT_TIMEOUT, &timeout);

    if (!mysql_real_connect(conn, db.host_name, db.user_name, db.password,
                            db.db_name, db.port_number, 0, 0)) {
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

void _create_file(char *source, char *filename, int lang)
{
    char src_pth[BUFFER_SIZE];
    // create the src file
    sprintf(src_pth, "%s.%s", filename, languages[lang].file_ext);
    if (DEBUG)
        printf("%s=%s", filename, src_pth);
    FILE *fp_src = fopen(src_pth, "we");
    fprintf(fp_src, "%s", source);
    fclose(fp_src);
}

struct problem_struct get_problem_info(int p_id)
{
    struct problem_struct problem;
    problem.id = p_id;
    problem.memory_limit = 256;
    problem.time_limit = 10;
    // get the problem info from Table:problem
    char sql[BUFFER_SIZE];
    MYSQL_RES *res;
    MYSQL_ROW row;
    sprintf(sql,
            "SELECT spj, spj_source, spj_lang, solution_lang, solution_source FROM polygon_problem WHERE id=%d",
            p_id);
    mysql_real_query(conn, sql, strlen(sql));
    res = mysql_store_result(conn);
    row = mysql_fetch_row(res);

    problem.spj_lang = 1;
    problem.solution_lang = 1;
    //printf("_create_file\n%s\n", row[1]);
    //_create_file(row[1], "spj", problem.spj_lang);
    _create_file(row[4], "solution", problem.solution_lang);
    if(res != NULL) {
        mysql_free_result(res);                         // free the memory
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

void prepare_files(char * filename, int p_id,
                   char * work_dir,  int runner_id)
{
    char infile[BUFFER_SIZE];
    int namelen = strlen(filename);
    char fname0[BUFFER_SIZE];
    char fname[BUFFER_SIZE];
    strncpy(fname0, filename, namelen);
    fname0[namelen] = 0;
    escape(fname, fname0);
    sprintf(infile, "%sdata/%d/%s", oj_home, p_id, fname);
    execute_cmd("/bin/cp '%s' %s/%s.in", infile, work_dir, fname);
}

void run_solution(struct problem_struct problem, char * work_dir,
                  int usedtime, char * data_filename)
{
    int lang = problem.solution_lang;
    char infile[BUFFER_SIZE];
    char outfile[BUFFER_SIZE];
    sprintf(infile, "%s.in", data_filename);
    sprintf(outfile, "%s.out", data_filename);
    nice(19);
    // now the user is "judge"
    chdir(work_dir);
    // open the files
    freopen(infile, "r", stdin);
    freopen(outfile, "w", stdout);
    freopen("error.out", "a+", stderr);
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
        LIM.rlim_cur = LIM.rlim_max = 80;
    } else {
        LIM.rlim_cur = LIM.rlim_max = 1;
    }

    setrlimit(RLIMIT_NPROC, &LIM);

    // set the stack
    LIM.rlim_cur = STD_MB << 6;
    LIM.rlim_max = STD_MB << 6;
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

int fix_python_mis_judge(char *work_dir, int * ACflg, int * topmemory,
                         int mem_lmt)
{
    int comp_res = OJ_AC;

    comp_res = execute_cmd("/bin/grep 'MemoryError'  %s/error.out", work_dir);

    if (!comp_res) {
        printf("Python need more Memory!");
        *ACflg = OJ_ML;
        *topmemory = mem_lmt * STD_MB;
    }

    return comp_res;
}

int fix_java_mis_judge(char *work_dir, int * ACflg, int * topmemory,
                       int mem_lmt)
{
    int comp_res = OJ_AC;
    execute_cmd("chmod 700 %s/error.out", work_dir);
    if (DEBUG)
        execute_cmd("cat %s/error.out", work_dir);
    comp_res = execute_cmd("/bin/grep 'Exception'  %s/error.out", work_dir);
    if (!comp_res) {
        printf("Exception reported\n");
        *ACflg = OJ_RE;
    }
    execute_cmd("cat %s/error.out", work_dir);

    comp_res = execute_cmd(
            "/bin/grep 'java.lang.OutOfMemoryError'  %s/error.out", work_dir);

    if (!comp_res) {
        printf("JVM need more Memory!");
        *ACflg = OJ_ML;
        *topmemory = mem_lmt * STD_MB;
    }

    if (!comp_res) {
        printf("JVM need more Memory or Threads!");
        *ACflg = OJ_ML;
        *topmemory = mem_lmt * STD_MB;
    }
    comp_res = execute_cmd("/bin/grep 'Could not create'  %s/error.out",
            work_dir);
    if (!comp_res) {
        printf("jvm need more resource,tweak -Xmx(OJ_JAVA_BONUS) Settings");
        *ACflg = OJ_RE;
        //topmemory=0;
    }
    return comp_res;
}

void print_runtimeerror(char * err)
{
    FILE *ferr = fopen("error.out", "a+");
    fprintf(ferr, "Runtime Error:%s\n", err);
    fclose(ferr);
}

void watch_solution(struct problem_struct problem, pid_t pidApp,
                    int * ACflg,
                    int * topmemory, int * usedtime,
                    char * work_dir, char * data_filename)
{
    int mem_lmt = problem.memory_limit;
    int lang = problem.solution_lang;

    if (DEBUG)
        printf("pid=%d [Problem ID: %d] judging %s\n", pidApp, problem.id,
               data_filename);

    int status, sig, exitcode;
    struct user_regs_struct reg;
    struct rusage ruse;
    bool first_run = true;
    if(*topmemory == 0) {
        *topmemory = get_proc_status(pidApp, "VmRSS:") << 10;
    } 
    for (;;) {
        // check the usage
        wait4(pidApp, &status, __WALL, &ruse);
        if(first_run){ // 
            ptrace(PTRACE_SETOPTIONS, pidApp, NULL, PTRACE_O_TRACESYSGOOD 
                                |PTRACE_O_TRACEEXIT 
                                |PTRACE_O_EXITKILL 
                            //  |PTRACE_O_TRACECLONE 
                            //  |PTRACE_O_TRACEFORK 
                            //  |PTRACE_O_TRACEVFORK
            );
        }
        if (*topmemory < getpagesize() * ruse.ru_minflt)
            *topmemory = getpagesize() * ruse.ru_minflt;

        if (*topmemory > mem_lmt * STD_MB) {
            if (DEBUG)
                printf("out of memory %d\n", *topmemory);
            if (*ACflg == OJ_AC)
                *ACflg = OJ_ML;
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }
        //sig = status >> 8;/*status >> 8 EXITCODE*/
        if (WIFEXITED(status))
            break;
        if ((lang == LANG_C || lang == LANG_CPP) && get_file_size("error.out")) {
            *ACflg = OJ_RE;
            addreinfo(problem.id);
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
            break;
        }

        // if (!isspj && get_file_size(userfile) > get_file_size(outfile) * 2 + 1024) {
        //     *ACflg = OJ_OL;
        //     ptrace(PTRACE_KILL, pidApp, NULL, NULL);
        //     break;
        // }

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

            if (*ACflg == OJ_AC) {
                switch (exitcode) {
                case SIGCHLD:
                case SIGALRM:
                    alarm(0);
                case SIGKILL:
                case SIGXCPU:
                    *ACflg = OJ_TL;
                    break;
                case SIGXFSZ:
                    *ACflg = OJ_OL;
                    break;
                default:
                    *ACflg = OJ_RE;
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
            if (*ACflg == OJ_AC) {
                switch (sig) {
                case SIGCHLD:
                case SIGALRM:
                    alarm(0);
                case SIGKILL:
                case SIGXCPU:
                    *ACflg = OJ_TL;
                    break;
                case SIGXFSZ:
                    *ACflg = OJ_OL;
                    break;

                default:
                    *ACflg = OJ_RE;
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
        if (call_counter[reg.REG_SYSCALL]) {
            //call_counter[reg.REG_SYSCALL]--;
        } else if (record_call) {
            call_counter[reg.REG_SYSCALL] = 1;
        } else { //do not limit JVM syscall for using different JVM
            *ACflg = OJ_RE;
            char error[BUFFER_SIZE];
            sprintf(error, "[ERROR] A not allowed system call.\nCall ID:%ld",
                (long)reg.REG_SYSCALL);
            write_log(error);
            print_runtimeerror(error);
            ptrace(PTRACE_KILL, pidApp, NULL, NULL);
        }
        ptrace(PTRACE_SYSCALL, pidApp, NULL, NULL);
        first_run = false;
    }
    *usedtime += (ruse.ru_utime.tv_sec * 1000 + ruse.ru_utime.tv_usec / 1000);
    *usedtime += (ruse.ru_stime.tv_sec * 1000 + ruse.ru_stime.tv_usec / 1000);
}

void clean_workdir(char * work_dir)
{
    umount(work_dir);
    if (DEBUG) {
        execute_cmd("/bin/rm -rf %s/log/*", work_dir);
        execute_cmd("mkdir %s/log/", work_dir);
        execute_cmd("/bin/mv %s/* %s/log/", work_dir, work_dir);
    } else {
        execute_cmd("mkdir %s/log/", work_dir);
        execute_cmd("/bin/mv %s/* %s/log/", work_dir, work_dir);
        execute_cmd("/bin/rm -rf %s/log/*", work_dir);
    }
}

void init_parameters(int argc, char ** argv, int *problem_id, int *runner_id)
{
    if (argc < 3) {
        fprintf(stderr, "Usage:%s problem_id runner_id.\n", argv[0]);
        fprintf(stderr, "Multi:%s problem_id runner_id judge_base_path.\n", argv[0]);
        fprintf(stderr, "Debug:%s problem_id runner_id judge_base_path debug.\n", argv[0]);
        exit(1);
    }
    DEBUG = argc > 4;

    getcwd(oj_home, sizeof(oj_home));
    int len = strlen(oj_home);
    oj_home[len] = '/';
    oj_home[len + 1] = '\0';

    chdir(oj_home); // change the dir

    *problem_id = atoi(argv[1]);
    *runner_id = atoi(argv[2]);
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

void copy_data_file(char *work_dir,char *data_path, char * filename)
{
    execute_cmd("/bin/cp %s/%s.out %s/%s.out", work_dir, filename, data_path,
                filename);
    execute_cmd("chmod 766 %s/*", data_path);
    execute_cmd("chown judge %s/*", data_path);
}

int main(int argc, char** argv)
{
    char work_dir[BUFFER_SIZE];
    int runner_id = 0;
    int problem_id, max_case_time = 0;
    struct problem_struct problem;
    init_parameters(argc, argv, &problem_id, &runner_id);

    init_mysql_conf();

    if (!init_mysql_conn()) {
        exit(0); //exit if mysql is down
    }
    //set work directory to start running & judging
    sprintf(work_dir, "%srun/%d", oj_home, runner_id);
    if (opendir(work_dir) == NULL) {
        execute_cmd("/bin/mkdir -p %s", work_dir);
        execute_cmd("/bin/chown judge %s ", work_dir);
        execute_cmd("chmod 777 %s ", work_dir);
    }
    clean_workdir(work_dir);
    if (shm_run)
        mk_shm_workdir(work_dir);
    chdir(work_dir);

    //get the limit
    problem = get_problem_info(problem_id);
    // copy java.policy
    if (problem.solution_lang == LANG_JAVA) {
        execute_cmd("/bin/cp %s/etc/java0.policy %s/java.policy", oj_home,
            work_dir);
        execute_cmd("chmod 755 %s/java.policy", work_dir);
        execute_cmd("chown judge %s/java.policy", work_dir);
    }

    // compile
    // set the result to compiling
    if (compile(problem.solution_lang, work_dir) != 0) {
        addceinfo(problem_id);
        update_solution(problem_id, OJ_CE, 0, 0);
        mysql_close(conn);
        clean_workdir(work_dir);
        write_log("[Problem ID: %d] Compile Error", problem_id);
        exit(0);
    } else {
        update_solution(problem_id, OJ_RI, 0, 0);
        umount(work_dir);
    }

    char fullpath[BUFFER_SIZE];
    char data_filename[BUFFER_SIZE];

    // the fullpath of data dir
    sprintf(fullpath, "%sdata/%d", oj_home, problem_id);

    // open DIRs
    DIR *dp;
    struct dirent *dirp;
    if ((dp = opendir(fullpath)) == NULL) {
        write_log("No such test data dir:%s!\n", fullpath);
        mysql_close(conn);
        exit(-1);
    }

    int run_result = OJ_AC;
    int usedtime = 0, topmemory = 0;
    
    // read files and run
    while (run_result == OJ_AC && (dirp = readdir(dp)) != NULL) {
        if (!is_input_file(dirp->d_name))
            continue;
        memcpy(data_filename, dirp->d_name, strlen(dirp->d_name) - 3);
        prepare_files(dirp->d_name, problem_id, work_dir, runner_id);
        init_syscalls_limits(problem.solution_lang);

        pid_t pid = fork();

        if (pid == 0) {
            run_solution(problem, work_dir, usedtime, data_filename);
        } else {
            watch_solution(problem, pid, &run_result, &topmemory, &usedtime,
                           work_dir, data_filename);
            copy_data_file(work_dir, fullpath, data_filename);
            max_case_time =
                        usedtime > max_case_time ? usedtime : max_case_time;
            usedtime = 0;
        }
    }

    if (run_result == OJ_RE) {
        addreinfo(problem_id);
    }
    usedtime = max_case_time;
    
    if (run_result == OJ_TL) {
        usedtime = problem.time_limit * 1000;
    }

    update_solution(problem_id, run_result, usedtime, topmemory >> 10);
   
    if (run_result == OJ_WA) {
        adddiffinfo(problem_id);
    }

    clean_workdir(work_dir);
    mysql_close(conn);
    closedir(dp);

    write_log("[Problem ID: %d] Result = %d", problem_id, run_result);
    return 0;
}
