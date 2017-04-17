/* 
 * File:   SocketHandler.h
 * Author: 51isoft
 *
 * Created on 2014年1月13日, 下午10:06
 */

#ifndef SOCKETHANDLER_H
#define SOCKETHANDLER_H

#include "vjudge.h"

class SocketHandler {
public:
  SocketHandler();
  void connectDispatcher();
  string getConnectionMessage();
  void sendMessage(string);
  void sendFile(string);
  void sendFileWithoutLength(string);
  void receiveFile(string);
  void receiveFileWithoutLength(string);
  virtual ~SocketHandler();
private:
  void sendMessage(const char *, size_t);
  size_t receiveMessage(char *, size_t);
  int sockfd;
};

#endif /* SOCKETHANDLER_H */

