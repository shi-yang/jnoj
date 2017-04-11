/* 
 * File:   Logger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年1月18日, 下午12:51
 */

#include "Logger.h"
#include "GlobalHelpers.h"

Logger* Logger::instance = new Logger;
pthread_mutex_t Logger::log_mutex = PTHREAD_MUTEX_INITIALIZER;
const string Logger::LOG_DIRECTORY = "log/";

Logger::Logger() {
  //ctor
}

Logger * Logger::Getinstance() {
  return instance;
}

Logger::~Logger() {
  //dtor
}

void Logger::log(const char* msg) {
  log((string) msg);
}

void Logger::log(char* msg) {
  log((string) msg);
}

void Logger::log(string msg) {
  string filename = LOG_DIRECTORY + name_prefix + currentDate() + ".log";
  vector <string> messages = split(msg, '\n');

  pthread_mutex_lock(&log_mutex);

  FILE * fp = fopen(filename.c_str(), "a");
  while (fp == NULL) {
    usleep(50000);
    fp = fopen(filename.c_str(), "a");
  }

  string id = identifier.find(getpid()) == identifier.end() ?
    "Main" : identifier[getpid()];
  for (vector <string>::iterator it = messages.begin(); it != messages.end();
      ++it) {
    fprintf(fp, "%s %s[%d]: %s\n", currentDateTime().c_str(), id.c_str(),
            getpid(), it -> c_str());
  }

  fclose(fp);

  pthread_mutex_unlock(&log_mutex);
}

/**
 * Add process ID to name conversion
 * @param pt    Process ID
 * @param id    Name
 */
void Logger::addIdentifier(int pt, string id) {
  identifier[pt] = id;
}

/**
 * Erase a process ID conversion
 * @param pt    Process ID
 */
void Logger::eraseIdentifier(int pt) {
  identifier.erase(pt);
}