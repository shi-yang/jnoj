#ifndef COMMON_H_INCLUDED_
#define COMMON_H_INCLUDED_

#include <stdbool.h>
#include <ctype.h>
#include <string.h>

#define LENGTH 256

int DEBUG = 0;

struct database {
    char host_name[LENGTH];
    char user_name[LENGTH];
    char password[LENGTH];
    char db_name[LENGTH];
    char mysql_unix_port[LENGTH]; //连接 mysql sock文件路径
    int port_number;
} db;

const int OJ_WT0 = 0;
const int OJ_WT1 = 1;
const int OJ_CI = 2;
const int OJ_RI = 3;
const int OJ_AC = 4;
const int OJ_PE = 5;
const int OJ_WA = 6;
const int OJ_TL = 7;
const int OJ_ML = 8;
const int OJ_OL = 9;
const int OJ_RE = 10;
const int OJ_CE = 11;
const int OJ_CO = 12;
const int OJ_TR = 13;

//读取配置文件时寻找等号
int after_equal(char * c)
{
    int i = 0;
    for (; c[i] != '\0' && c[i] != '='; i++)
        ;
    return ++i;
}

//读取配置文件时去除空格
void trim(char *c)
{
    char buf[LENGTH];
    char *start, *end;
    strcpy(buf, c);
    start = buf;
    while (isspace(*start))
        start++;
    end = start;
    while (!isspace(*end) && *end != '#')
        end++;
    *end = '\0';
    strcpy(c, start);
}

bool read_buf(char *buf, const char *key, char *value)
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

void read_int(char *buf, const char *key, int *value)
{
    char buf2[LENGTH];
    if (read_buf(buf, key, buf2))
        sscanf(buf2, "%d", value);
}

#endif