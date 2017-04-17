#ifndef CONFIG_H
#define CONFIG_H

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#include <string>
#include <vector>
#include <map>
#include <list>
#include <iterator>

using namespace std;

#include "Exception.h"
#include "ini.hpp"
#include "JudgerInfo.h"

class Config {
public:
  Config();
  virtual ~Config();

  static Config * Getinstance();

  string GetDispatcher_ip() const {
    return dispatcher_ip;
  }

  void SetDispatcher_ip(string dispatcher_ip) {
    this->dispatcher_ip = dispatcher_ip;
  }

  int GetDispatcher_port() const {
    return dispatcher_port;
  }

  void SetDispatcher_port(int dispatcher_port) {
    this->dispatcher_port = dispatcher_port;
  }

  string GetJudge_connect_string() const {
    return judge_connect_string;
  }

  void SetJudge_connect_string(string judge_connect_string) {
    this->judge_connect_string = judge_connect_string;
  }

  vector<JudgerInfo> GetJudger_info() const {
    return judger_info;
  }

  void SetJudger_info(vector<JudgerInfo> judger_info) {
    this->judger_info = judger_info;
  }

  int GetMax_curl_time() const {
    return max_curl_time;
  }

  void SetMax_curl_time(int max_curl_time) {
    this->max_curl_time = max_curl_time;
  }

  string GetTmpfile_path() const {
    return tmpfile_path;
  }

  void SetTmpfile_path(string tmpfile_path) {
    this->tmpfile_path = tmpfile_path;
  }

  string GetCookies_path() const {
    return cookies_path;
  }

  void SetCookies_path(string cookies_path) {
    this->cookies_path = cookies_path;
  }


protected:
private:

  string dispatcher_ip;
  int dispatcher_port;
  string judge_connect_string;
  int max_curl_time;
  string tmpfile_path;
  string cookies_path;
  vector <JudgerInfo> judger_info;

  static Config * instance;
};

#endif // CONFIG_H
