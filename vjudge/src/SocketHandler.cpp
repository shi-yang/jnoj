/* 
 * File:   SocketHandler.cpp
 * Author: 51isoft
 * 
 * Created on 2014年1月13日, 下午10:06
 */

#include "SocketHandler.h"

SocketHandler::SocketHandler() {
  connectDispatcher();
}

SocketHandler::~SocketHandler() {
  close(sockfd);
}

/**
 * Connect to dispatcher
 */
void SocketHandler::connectDispatcher() {
  struct sockaddr_in server;

  sockfd = socket(AF_INET, SOCK_STREAM, 0);
  bzero(&server, sizeof (server));
  server.sin_family = AF_INET;
  server.sin_port = htons(CONFIG->GetDispatcher_port());
  server.sin_addr.s_addr = inet_addr(CONFIG->GetDispatcher_ip().c_str());
  if (connect(sockfd, (struct sockaddr *) &server, sizeof (server)) == -1) {
    close(sockfd);
    throw Exception("Cannot connect to dispatcher");
  }
}

/**
 * Send a message without any prepend length info
 * @param message       Message to be sent, use const char *
 * @param length        Length of the message
 */
void SocketHandler::sendMessage(const char * message, size_t length) {
  size_t sent = 0;
  for (sent = 0; sent < length;) {
    ssize_t current_sent = write(sockfd, message, length - sent);
    if (current_sent == -1) {
      throw Exception("Connection lost");
    } else {
      sent += current_sent;
    }
  }
}

/**
 * Send a message without any prepend length info
 * @param message       Message to send
 */
void SocketHandler::sendMessage(string message) {
  sendMessage(message.c_str(), message.length());
}

/**
 * Send file without prepend length info
 * @param filename      File to send
 */
void SocketHandler::sendFileWithoutLength(string filename) {
  int source = open(filename.c_str(), O_RDONLY);
  size_t temp_length = 0;
  char buffer[1024];
  while ((temp_length = read(source, buffer, sizeof (buffer))) > 0) {
    try {
      sendMessage(buffer, temp_length);
    } catch (Exception & e) {
      close(source);
      throw e;
    }
  }
  close(source);
}

/**
 * Send file, prepend length info
 * @param filename      File to send
 */
void SocketHandler::sendFile(string filename) {
  int source = open(filename.c_str(), O_RDONLY);
  struct stat file_stat;
  char buffer[1024];

  fstat(source, &file_stat);
  size_t length = htonl(file_stat.st_size);

  // use first 4 bytes to send file size
  memcpy(buffer, &length, sizeof (length));
  try {
    sendMessage(buffer, sizeof (length));
  } catch (Exception & e) {
    close(source);
    throw e;
  }

  // send the message body
  while ((length = read(source, buffer, sizeof (buffer))) > 0) {
    try {
      sendMessage(buffer, length);
    } catch (Exception & e) {
      close(source);
      throw e;
    }
  }
  close(source);
}

/**
 * Receive message from the socket, store them to buffer
 * @param buffer        Message buffer
 * @param length        The length of the buffer
 * @return Number of bytes received
 */
size_t SocketHandler::receiveMessage(char * buffer, size_t length) {
  ssize_t got;
  got = recv(sockfd, buffer, length, 0);
  if (got == 0) {
    throw (Exception("Connection lost"));
  }
  return got;
}

/**
 * Receive a file from the socket
 * @param filename      Filename to store
 */
void SocketHandler::receiveFile(string filename) {
  FILE * fp;
  size_t length, got;
  char buffer[1024];

  // get first 4 bytes, the length info
  receiveMessage(buffer, sizeof (length));
  memcpy(&length, buffer, sizeof (length));
  length = ntohl(length);

  // recieve body, write them to file
  fp = fopen(filename.c_str(), "wb");
  got = 0;
  while (got < length) {
    size_t current_got;
    try {
      current_got = receiveMessage(buffer, min(length - got, sizeof (buffer)));
    } catch (Exception & e) {
      fclose(fp);
      throw e;
    }
    got += current_got;
    fwrite(buffer, sizeof (char), current_got, fp);
  }
  fclose(fp);
}

void SocketHandler::receiveFileWithoutLength(string filename) {
  bool got_things = false;
  ssize_t got;
  FILE * fp;
  char buffer[1024];

  fp = fopen(filename.c_str(), "wb");
  while (!got_things) {
    buffer[0] = 0; // clear first char, for sanity check
    // use non-blocking method since we don't know the file size
    while ((got = recv(sockfd, buffer, 1024, MSG_DONTWAIT)) > 0) {
      got_things = true;
      fwrite(buffer, sizeof (char), got, fp);
    }
    if (got == 0) {
      fclose(fp);
      throw (Exception("Connection lost"));
    }
    usleep(67354); // sleep for a random time
    // sanity check, ensure it's the message we need
    if (buffer[0] != '<') got_things = false;
  }
  fclose(fp);
}