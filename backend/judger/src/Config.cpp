#include "Config.h"

Config* Config::instance = new Config;

Config * Config::Getinstance() {
  return instance;
}

Config::Config() {
  try {
    INI::Parser ini("config.ini");
    dispatcher_ip = ini.top()["dispatcher_ip"];
    judge_connect_string = ini.top()["judge_connect_string"];
    dispatcher_port = atoi(ini.top()["dispatcher_port"].c_str());
    low_privilege_uid = atoi(ini.top()["low_privilege_uid"].c_str());
    general_compile_time = atoi(ini.top()["general_compile_time"].c_str());
//    generator_run_time = atoi(ini.top()["generator_run_time"].c_str());
    generator_run_memory = atoi(ini.top()["generator_run_memory"].c_str());
    vmlang_multiplier = atoi(ini.top()["vmlang_multiplier"].c_str());
    max_output_limit = atoi(ini.top()["max_output_limit"].c_str());
    extra_runtime = atoi(ini.top()["extra_runtime"].c_str());
    checker_run_time = atoi(ini.top()["checker_run_time"].c_str());
    checker_run_memory = atoi(ini.top()["checker_run_memory"].c_str());
    interactive_max_run_time =
        atoi(ini.top()["interactive_max_run_time"].c_str());
    tmpfile_path = ini.top()["tmpfile_path"];
  } catch (runtime_error & e) {
    cerr << e.what();
    exit(1);
  }
}

Config::~Config() {
  //dtor
}
