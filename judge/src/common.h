#ifndef COMMON_H_INCLUDED_
#define COMMON_H_INCLUDED_

#include <stdbool.h>

#define LENGTH 256
struct database {
    char host_name[LENGTH];
    char user_name[LENGTH];
    char password[LENGTH];
    char db_name[LENGTH];
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

#endif