/* 
 * File:   SocketHandler.h
 * Author: 51isoft
 *
 * Created on 2014年1月13日, 下午10:06
 */

#ifndef SOCKETHANDLER_H
#define	SOCKETHANDLER_H

#include "dispatcher.h"

class SocketHandler {
public:
  SocketHandler(int);
  string getConnectionMessage();
  void sendMessage(string);
  void sendFile(string);
  void sendFileWithoutLength(string);
  void receiveFile(string);
  void receiveFileWithoutLength(string);
  bool checkAlive();
  virtual ~SocketHandler();
  static const int CHECK_ALIVE_INTERVAL;
private:
  void sendMessage(const char *, size_t);
  size_t receiveMessage(char *, size_t);
  int sockfd;
  time_t last_check;
};

#endif	/* SOCKETHANDLER_H */

