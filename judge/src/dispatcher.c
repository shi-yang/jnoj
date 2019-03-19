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
#include <time.h>
#include <stdio.h>
#include <string.h>
#include <ctype.h>
#include <stdlib.h>
#include <unistd.h>
#include <getopt.h>
#include <syslog.h>
#include <errno.h>
#include <fcntl.h>
#include <stdarg.h>
#include <mysql/mysql.h>
#include <sys/wait.h>
#include <sys/stat.h>
#include <sys/sysinfo.h>
#include <sys/resource.h>
#include <signal.h>
#include "common.h"

#define LOCKFILE "/var/run/judged.pid"
#define LOCKMODE (S_IRUSR | S_IWUSR | S_IRGRP | S_IROTH)
#define BUFFER_SIZE 1024
#define STD_MB 1048576

static char lock_file[BUFFER_SIZE] = LOCKFILE;
static char oj_home[BUFFER_SIZE];
static char judge_path[BUFFER_SIZE];
static char oj_lang_set[BUFFER_SIZE];
static int max_running;
static int sleep_time;
static int sleep_tmp;
static int oj_tot;
static int oj_mod;


static int oi_mode = 0;
static bool STOP = false;
static MYSQL *conn;
static MYSQL_RES *res;
static MYSQL_ROW row;
//static FILE *fp_log;
static char query[BUFFER_SIZE];

const char * judge_name = "judge";

void call_for_exit(int s)
{
    STOP = true;
    printf("Stopping judged...\n");
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
    if (DEBUG)
        printf("%s\n", buffer);
    va_end(ap);
    fclose(fp);
}

// read the configue file
void init_mysql_conf()
{
    FILE *fp = NULL;
    char buf[BUFFER_SIZE];
    db.port_number = 3306;
    max_running = get_nprocs();
    sleep_time = 1;
    oj_tot = 1;
    oj_mod = 0;
    strcpy(oj_lang_set, "0,1,2,3");
    fp = fopen("./config.ini", "r");
    if (fp != NULL) {
        while (fgets(buf, BUFFER_SIZE - 1, fp)) {
            read_buf(buf, "OJ_HOST_NAME", db.host_name);
            read_buf(buf, "OJ_USER_NAME", db.user_name);
            read_buf(buf, "OJ_PASSWORD", db.password);
            read_buf(buf, "OJ_DB_NAME", db.db_name);
            read_buf(buf, "OJ_MYSQL_UNIX_PORT", db.mysql_unix_port);
            read_int(buf, "OJ_PORT_NUMBER", &db.port_number);
            read_int(buf, "OJ_SLEEP_TIME", &sleep_time);
            read_int(buf, "OJ_TOTAL", &oj_tot);
            read_int(buf, "OJ_MOD", &oj_mod);
            read_buf(buf, "OJ_LANG_SET", oj_lang_set);
        }
        sprintf(query,
                "SELECT id FROM solution "
                "WHERE language in (%s) and result<2 and MOD(id,%d)=%d "
                "ORDER BY result ASC,id ASC limit %d",
                oj_lang_set, oj_tot, oj_mod, max_running * 2);
        sleep_tmp = sleep_time;
        fclose(fp);
    } else {
        printf("Can not open config.ini\n");
        exit(EXIT_FAILURE);
    }
}

void run_client(int runid, int clientid)
{
    char clientidstr[BUFFER_SIZE];
    char runidstr[BUFFER_SIZE];
    struct rlimit LIM;
    LIM.rlim_max = 800;
    LIM.rlim_cur = 800;
    setrlimit(RLIMIT_CPU, &LIM);

    LIM.rlim_max = 180 * STD_MB;
    LIM.rlim_cur = 180 * STD_MB;
    setrlimit(RLIMIT_FSIZE, &LIM);

    LIM.rlim_max = STD_MB << 11;
    LIM.rlim_cur = STD_MB << 11;
    setrlimit(RLIMIT_AS, &LIM);

    LIM.rlim_cur = LIM.rlim_max = 200;
    setrlimit(RLIMIT_NPROC, &LIM);

    sprintf(runidstr, "-s %d", runid);
    sprintf(clientidstr, "-r %d", clientid);

    execl(judge_path, judge_path, runidstr, clientidstr,
          oi_mode ? "-o" : "",
          DEBUG ? "-d" : "",
          (char *) NULL);
}

int executesql(const char *sql)
{
    if (mysql_real_query(conn, sql, strlen(sql))) {
        if (DEBUG) {
            write_log("%s", mysql_error(conn));
        }
        sleep(20);
        conn = NULL;
        return 1;
    } else {
        return 0;
    }
}

int init_mysql()
{
    if (conn == NULL) {
        // init the database connection
        conn = mysql_init(NULL);
        // connect the database
        const char timeout = 30;
        // set mysql unix socket
        char * mysql_unix_port = db.mysql_unix_port;
        if (strlen(mysql_unix_port) == 0) {
            mysql_unix_port = NULL;
        }
        mysql_options(conn, MYSQL_OPT_CONNECT_TIMEOUT, &timeout);
        if (!mysql_real_connect(conn, db.host_name, db.user_name, db.password,
                                db.db_name, db.port_number, mysql_unix_port, 0)) {
            if (DEBUG)
                write_log("%s", mysql_error(conn));
            sleep(2);
            return 1;
        } else {
            return executesql("set names utf8");
        }
    } else {
        return executesql("commit");
    }
}

FILE *read_cmd_output(const char *fmt, ...)
{
    char cmd[BUFFER_SIZE];

    FILE *ret = NULL;
    va_list ap;

    va_start(ap, fmt);
    vsprintf(cmd, fmt, ap);
    va_end(ap);
    ret = popen(cmd, "r");

    return ret;
}

int _get_jobs_mysql(int *jobs)
{
    if (mysql_real_query(conn, query, strlen(query))) {
        if (DEBUG)
            write_log("%s", mysql_error(conn));
        sleep(20);
        return 0;
    }
    res = mysql_store_result(conn);
    int i = 0;
    int ret = 0;
    while (res != NULL && (row = mysql_fetch_row(res)) != NULL) {
        jobs[i++] = atoi(row[0]);
    }

    if (res != NULL && !executesql("commit")) {
        mysql_free_result(res);  // free the memory
        res = NULL;
    } else {
        i = 0;
    }
    ret = i;
    while (i <= max_running * 2)
        jobs[i++] = 0;
    return ret;
}

int get_jobs(int *jobs)
{
    return _get_jobs_mysql(jobs);
}

bool check_out(int solution_id, int result)
{
    if (oj_tot > 1)
        return true;

    char sql[BUFFER_SIZE];
    sprintf(sql,
            "UPDATE solution SET result=%d,time=0,memory=0,judgetime=NOW() "
            "WHERE id=%d and result<2 LIMIT 1",
            result, solution_id);
    if (mysql_real_query(conn, sql, strlen(sql))) {
        syslog(LOG_ERR | LOG_DAEMON, "%s", mysql_error(conn));
        return false;
    } else {
        if (conn != NULL && mysql_affected_rows(conn) > 0ul)
            return true;
        else
            return false;
    }
}

int work()
{
    static int retcnt = 0;
    int i = 0;
    static pid_t ID[100];
    static int workcnt = 0;
    int runid = 0;
    int jobs[max_running * 2 + 1];
    pid_t tmp_pid = 0;

    // sleep_time = sleep_tmp;
    // get the database info
    if (!get_jobs(jobs)) {
        return 0;
    }

    // exec the submit
    for (int j = 0; jobs[j] > 0; j++) {
        runid = jobs[j];
        if (runid % oj_tot != oj_mod)
            continue;
        if (DEBUG)
            write_log("Judging solution %d", runid);
        // if no more client can running
        if (workcnt >= max_running) {
            // wait 4 one child exit
            tmp_pid = waitpid(-1, NULL, 0);

            // get the client id
            for (i = 0; i < max_running; i++) {
                // got the client id
                if (ID[i] == tmp_pid) {
                    workcnt--;
                    retcnt++;
                    ID[i] = 0;
                    break;
                }
            }
        } else {  // have free client
            for (i = 0; i < max_running; i++)  // find the client id
                if (ID[i] == 0)
                    break;  // got the client id
        }
        if (i < max_running) {
            if (workcnt < max_running && check_out(runid, OJ_CI)) {
                workcnt++;
                ID[i] = fork();  // start to fork
                if (ID[i] == 0) {
                    if (DEBUG)
                        write_log("<<=sid=%d===clientid=%d==>>\n", runid, i);
                    run_client(runid, i);  // if the process is the son, run it
                    exit(0);
                }
            } else {
                ID[i] = 0;
            }
        }
    }
    while ((tmp_pid = waitpid(-1, NULL, 0)) > 0) {
        for (i = 0; i < max_running; i++) {  // get the client id
            if (ID[i] == tmp_pid) {
                workcnt--;
                retcnt++;
                ID[i] = 0;
                break;  // got the client id
            }
        }
        printf("tmp_pid = %d\n", tmp_pid);
    }
    if (res != NULL) {
        mysql_free_result(res);  // free the memory
        res = NULL;
    }
    executesql("commit");

    if (DEBUG && retcnt)
        write_log("<<%d done!>>", retcnt);
    return retcnt;
}

int lockfile(int fd)
{
    struct flock fl;
    fl.l_type = F_WRLCK;
    fl.l_start = 0;
    fl.l_whence = SEEK_SET;
    fl.l_len = 0;
    return fcntl(fd, F_SETLK, &fl);
}

// Returns 1 if the daemon is running, otherwise returns 0. 
int already_running()
{
    char buf[16];
    int fd = open(lock_file, O_RDWR | O_CREAT, LOCKMODE);
    if (fd < 0) {
        syslog(LOG_ERR | LOG_DAEMON, "Can't open %s: %s", LOCKFILE,
               strerror(errno));
        exit(1);
    }
    if (lockfile(fd) < 0) {
        if (errno == EACCES || errno == EAGAIN) {
            close(fd);
            return 1;
        }
        syslog(LOG_ERR | LOG_DAEMON, "Can't lock %s: %s", LOCKFILE,
               strerror(errno));
        exit(1);
    }
    ftruncate(fd, 0);
    sprintf(buf, "%d", getpid());
    write(fd, buf, strlen(buf) + 1);
    return 0;
}

int daemon_init(void)
{
    pid_t pid;

    if ((pid = fork()) < 0)
        return -1;
    else if (pid != 0)
        exit(0);  // parent exit

    // child continues
    setsid();  // become session leader
    chdir(oj_home);  // change working directory
    umask(0);  // clear file mode creation mask
    close(0);  // close stdin
    close(1);  // close stdout
    close(2);  // close stderr
    int fd = open("/dev/null", O_RDWR);
    dup2(fd, 0);
    dup2(fd, 1);
    dup2(fd, 2);
    if (fd > 2) {
        close(fd);
    }
    return 0;
}

void turbo_mode2()
{
    char sql[BUFFER_SIZE];
    sprintf(sql, " CALL `sync_result`();");
    if (mysql_real_query(conn, sql, strlen(sql)));
}

void set_path()
{
    getcwd(oj_home, sizeof(oj_home));
    int cnt = strlen(oj_home);
    oj_home[cnt] = '/';
    oj_home[cnt + 1] = '\0';

    strcpy(judge_path, oj_home);
    int len = strlen(judge_name);
    for (int i = 0; i < len; i++) {
        judge_path[++cnt] = judge_name[i];
    }
    judge_path[++cnt] = '\0';
}

void display_usage()
{
    fprintf(stderr, "Judge server for JNOJ.\n\n");
    fprintf(stderr, "  Options may be given in one of two forms: either a single\n");
    fprintf(stderr, "  character preceded by a -, or a name preceded by --.\n");
    fprintf(stderr, "  -d ...... --debug\n");
    fprintf(stderr, "              Enable debug mode.\n");
    fprintf(stderr, "  -o ...... --oi\n");
    fprintf(stderr, "              Enable oi mode.\n");
    fprintf(stderr, "  -h ...... --help\n");
    fprintf(stderr, "              Dsiplay help (from command line).\n");
}

void init_parameters(int argc, char *argv[])
{
    int ch;
    struct option long_options[] = {
        {"debug", no_argument, 0,  'd'},
        {"oi",  no_argument, 0, 'o'},
        {"help",  no_argument, 0, 'h'}
    };
    while ((ch = getopt_long(argc, argv, "doh", long_options, 0)) != -1) {
        switch (ch) {
            case 'o':
                oi_mode = 1;
                printf("Enable OI mode.\n");
                break;
            case 'd':
                DEBUG = 1;
                printf("Enable DEBUG mode.\n");
                break;
            case 'h':
                display_usage();
                exit(1);
        }
    }
}
int main(int argc, char *argv[])
{
    init_parameters(argc, argv);
    set_path();
    chdir(oj_home); // change the dir

    sprintf(lock_file, "%s/etc/judge.pid", oj_home);
    if (!DEBUG)
        daemon_init();
    if (already_running()) {
        syslog(LOG_ERR | LOG_DAEMON,
            "This daemon program is already running!\n");
        printf("Already has one dispatcher on it!\n");
        return 1;
    }
    if (!DEBUG)
        system("/sbin/iptables -A OUTPUT -m owner --uid-owner judge -j DROP");

    init_mysql_conf();  // set the database info
    
    signal(SIGQUIT, call_for_exit);
    signal(SIGKILL, call_for_exit);
    signal(SIGTERM, call_for_exit);

    // start to run
    for (;;) {
        int j = 1;
        while (j && !init_mysql()) {
            j = work();
        }
        turbo_mode2();
        sleep(sleep_time);
    }
    return 0;
}
