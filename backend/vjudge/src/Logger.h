/* 
 * File:   Logger.h
 * Author: 51isoft
 *
 * Created on 2014年1月18日, 下午12:51
 */

#ifndef LOGGER_H
#define LOGGER_H

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/time.h>
#include <pthread.h>

#include <string>
#include <vector>

using namespace std;

class Logger {
public:
  /** Default constructor */
  Logger();
  /** Default destructor */
  virtual ~Logger();
  /** Access instance
   * \return The current value of instance
   */
  static Logger * Getinstance();
  void log(char *);
  void log(const char *);
  void log(string);
  void log(string, string);

  static const string LOG_DIRECTORY;
protected:
private:
  string name_prefix;
  static Logger * instance; //!< Member variable "instance"
  static pthread_mutex_t log_mutex;
};

#endif /* LOGGER_H */

