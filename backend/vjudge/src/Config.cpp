#include "Config.h"

Config* Config::instance = new Config;

Config * Config::Getinstance() {
  return instance;
}

Config::Config() {
  try {
    INI::Parser ini("config.ini");
    dispatcher_ip = ini.top()["dispatcher_ip"];
    dispatcher_port = atoi(ini.top()["dispatcher_port"].c_str());
    judge_connect_string = ini.top()["judge_connect_string"];
    max_curl_time = atoi(ini.top()["max_curl_time"].c_str());
    tmpfile_path = ini.top()["tmpfile_path"];
    cookies_path = ini.top()["cookies_path"];

    // Parse judger info
    judger_info.clear();
    for (map<string, INI::Level>::iterator it = ini.top().sections.begin();
        it != ini.top().sections.end();
        ++it) {
      string oj = it->first;
      int max_wait_time = atoi(it->second["max_wait_time"].c_str());
      for (map<string, INI::Level>::iterator ij = it->second.sections.begin();
          ij != it->second.sections.end();
          ++ij) {
        judger_info.push_back(JudgerInfo(oj, ij->first, ij->second["username"],
                                         ij->second["password"], max_wait_time));
      }
    }
  } catch (runtime_error & e) {
    cerr << e.what();
    exit(1);
  }
}

Config::~Config() {
  //dtor
}
