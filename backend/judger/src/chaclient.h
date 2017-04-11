#ifndef CHACLIENT_H_INCLUDED
#define CHACLIENT_H_INCLUDED

#include <stdio.h>
#include <stdlib.h>
#include <assert.h>
#include <string.h>
#include <strings.h>
#include <unistd.h>
#include <pthread.h>
#include <string>
#include <map>
#include <vector>
#include <algorithm>
#include <fstream>
#include <iostream>


#include <sys/types.h>
#include <sys/fcntl.h>
#include <sys/socket.h>
#include <sys/wait.h>
#include <sys/resource.h>
#include <sys/signal.h>
#include <sys/time.h>
#include <sys/ptrace.h>
#include <sys/syscall.h>
#include <sys/user.h>
#include <sys/stat.h>

#include <netinet/in.h>

#include <arpa/inet.h>

#include <errno.h>
#include <netdb.h>

#include "Exception.h"
#include "GlobalHelpers.h"


using namespace std;

#define MAX_DATA_SIZE 4096
#define CHECK_STATUS 1
#define NEED_JUDGE 2
#define SEND_DATA 3
#define DO_CHALLENGE 4
#define DO_PRETEST 5
#define DO_TESTALL 6
#define DO_INTERACTIVE 7
#define JUDGER_STATUS_REPORT 1
#define NEED_DATA 2
#define RESULT_REPORT 3
#define CHALLENGE_REPORT 4

#define MIN_LANG_NUM 1
#define CPPLANG 1
#define CLANG 2
#define JAVALANG 3
#define FPASLANG 4
#define PY2LANG 5
#define CSLANG 6
#define FORTLANG 7
#define PERLLANG 8
#define RUBYLANG 9
#define ADALANG 10
#define SMLLANG 11
#define VCLANG 12
#define VCPPLANG 13
#define CLANGLANG 14
#define CLANGPPLANG 15
#define PY3LANG 16
#define CPP11LANG 17
#define MAX_LANG_NUM 17

#define AC_STATUS 0
#define CE_STATUS 1
#define RE_STATUS 2
#define WA_STATUS 3
#define TLE_STATUS 4
#define MLE_STATUS 5
#define PE_STATUS 6
#define OLE_STATUS 7
#define RF_STATUS 8
#define ND_STATUS 9
#define NJ_STATUS 10
#define JE_STATUS 11


#endif // CHACLIENT_H_INCLUDED
