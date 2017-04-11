/* 
 * File:   Exception.h
 * Author: 51isoft
 *
 * Created on 2014年1月13日, 下午9:56
 */

#ifndef EXCEPTION_H
#define EXCEPTION_H

class Exception : std::exception {
public:

  Exception(std::string _msg) : msg(_msg) {
  }

  ~Exception() throw () {
  }

  const char* what() const throw () {
    return msg.c_str();
  }
private:
  std::string msg;
};

#endif /* EXCEPTION_H */

