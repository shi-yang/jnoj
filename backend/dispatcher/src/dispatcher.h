#ifndef DISPATCHER_H_INCLUDED
#define DISPATCHER_H_INCLUDED

#include <stdio.h>
#include <stdlib.h>
#include <assert.h>
#include <string.h>
#include <strings.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/fcntl.h>
#include <sys/socket.h>
#include <sys/wait.h>
#include <sys/stat.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/time.h>
#include <errno.h>
#include <netdb.h>
#include <pthread.h>
#include <mysql/mysql.h>

#include <string>
#include <map>
#include <iostream>
#include <fstream>
#include <queue>
#include <list>

using namespace std;

#include "Exception.h"
#include "GlobalHelpers.h"

#define MAX_JUDGER_NUMBER 256
#define MAX_DATA_SIZE 855350

// from dispatcher to judger, message type
#define CHECK_STATUS 1
#define NEED_JUDGE 2
#define SEND_DATA 3
#define DO_CHALLENGE 4
#define DO_PRETEST 5
#define DO_TESTALL 6
#define DO_INTERACTIVE 7

// from judger to dispatcher, message type
#define JUDGER_STATUS_REPORT 1001
#define NEED_DATA 1002
#define RESULT_REPORT 1003
#define CHALLENGE_REPORT 1004

// handshake timout, in ms
#define HANDSHAKE_TIMEOUT 5000

#endif // DISPATCHER_H_INCLUDED
