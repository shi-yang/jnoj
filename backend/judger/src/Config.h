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

  string GetTmpfile_path() const {
    return tmpfile_path;
  }

  void SetTmpfile_path(string tmpfile_path) {
    this->tmpfile_path = tmpfile_path;
  }

  int GetChecker_run_memory() const {
    return checker_run_memory;
  }

  void SetChecker_run_memory(int checker_run_memory) {
    this->checker_run_memory = checker_run_memory;
  }

  int GetChecker_run_time() const {
    return checker_run_time;
  }

  void SetChecker_run_time(int checker_run_time) {
    this->checker_run_time = checker_run_time;
  }

  int GetExtra_runtime() const {
    return extra_runtime;
  }

  void SetExtra_runtime(int extra_runtime) {
    this->extra_runtime = extra_runtime;
  }

  int GetGeneral_compile_time() const {
    return general_compile_time;
  }

  void SetGeneral_compile_time(int general_compile_time) {
    this->general_compile_time = general_compile_time;
  }

  int GetGenerator_run_memory() const {
    return generator_run_memory;
  }

  void SetGenerator_run_memory(int generator_run_memory) {
    this->generator_run_memory = generator_run_memory;
  }

  int GetGenerator_run_time() const {
    return generator_run_time;
  }

  void SetGenerator_run_time(int generator_run_time) {
    this->generator_run_time = generator_run_time;
  }

  static Config* GetInstance() {
    return instance;
  }

  static void SetInstance(Config* instance) {
    Config::instance = instance;
  }

  int GetLow_privilege_uid() const {
    return low_privilege_uid;
  }

  void SetLow_privilege_uid(int low_privilege_uid) {
    this->low_privilege_uid = low_privilege_uid;
  }

  int GetMax_output_limit() const {
    return max_output_limit;
  }

  void SetMax_output_limit(int max_output_limit) {
    this->max_output_limit = max_output_limit;
  }

  int GetVmlang_multiplier() const {
    return vmlang_multiplier;
  }

  void SetVmlang_multiplier(int vmlang_multiplier) {
    this->vmlang_multiplier = vmlang_multiplier;
  }

  int GetInteractive_max_run_time() const {
    return interactive_max_run_time;
  }

  void SetInteractive_max_run_time(int interactive_max_run_time) {
    this->interactive_max_run_time = interactive_max_run_time;
  }




protected:
private:

  string dispatcher_ip;
  int dispatcher_port;
  int low_privilege_uid;
  int general_compile_time;
  int generator_run_time;
  int generator_run_memory;
  int vmlang_multiplier;
  int max_output_limit;
  int extra_runtime;
  int checker_run_time;
  int checker_run_memory;
  int interactive_max_run_time;
  string judge_connect_string;
  string tmpfile_path;

  static Config * instance;
};

#endif // CONFIG_H
