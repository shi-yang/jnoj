/* 
 * File:   DatabaseHandler.h
 * Author: 51isoft
 *
 * Created on 2014年1月12日, 下午11:21
 */

#ifndef DATABASEHANDLER_H
#define	DATABASEHANDLER_H

#include "dispatcher.h"

class DatabaseHandler {
public:
  DatabaseHandler();
  virtual ~DatabaseHandler();
  vector <map<string, string> > Getall_results(string);
  string escape(string);
  void query(string);

private:
  MYSQL * mysql;
};

#endif	/* DATABASEHANDLER_H */

