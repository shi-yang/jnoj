/* 
 * File:   Bott.h
 * Author: 51isoft
 *
 * Created on 2014年1月19日, 上午1:04
 */

#ifndef BOTT_H
#define BOTT_H

#include "vjudge.h"
#include "rapidjson/document.h"

using namespace rapidjson;

class Bott {
public:
  /** Default constructor */
  Bott();
  Bott(string filename);
  /** Default destructor */
  virtual ~Bott();

  int Gettype() const {
    return type;
  }

  void Settype(int val) {
    type = val;
  }

  int Getrunid() const {
    return runid;
  }

  void Setrunid(int val) {
    runid = val;
  }

  int Getcha_id() const {
    return cha_id;
  }

  void Setcha_id(int val) {
    cha_id = val;
  }

  string Getsrc() const {
    return src;
  }

  void Setsrc(string val) {
    src = val;
  }

  int Getlanguage() const {
    return language;
  }

  void Setlanguage(int val) {
    language = val;
  }

  int Getpid() const {
    return pid;
  }

  void Setpid(int val) {
    pid = val;
  }

  int Getnumber_of_testcases() const {
    return number_of_testcases;
  }

  void Setnumber_of_testcases(int val) {
    number_of_testcases = val;
  }

  int Gettime_limit() const {
    return time_limit;
  }

  void Settime_limit(int val) {
    time_limit = val;
  }

  int Getcase_limit() const {
    return case_limit;
  }

  void Setcase_limit(int val) {
    case_limit = val;
  }

  int Getmemory_limit() const {
    return memory_limit;
  }

  void Setmemory_limit(int val) {
    memory_limit = val;
  }

  int Getspj() const {
    return spj;
  }

  void Setspj(int val) {
    spj = val;
  }

  string Getvname() const {
    return vname;
  }

  void Setvname(string val) {
    vname = val;
  }

  string Getvid() const {
    return vid;
  }

  void Setvid(string val) {
    vid = val;
  }

  int Getmemory_used() const {
    return memory_used;
  }

  void Setmemory_used(int val) {
    memory_used = val;
  }

  int Gettime_used() const {
    return time_used;
  }

  void Settime_used(int val) {
    time_used = val;
  }

  string Getresult() const {
    return result;
  }

  void Setresult(string val) {
    result = val;
  }

  string Getce_info() const {
    return ce_info;
  }

  void Setce_info(string val) {
    ce_info = val;
  }

  int Getdata_type() const {
    return data_type;
  }

  void Setdata_type(int val) {
    data_type = val;
  }

  string Getdata_detail() const {
    return data_detail;
  }

  void Setdata_detail(string val) {
    data_detail = val;
  }

  int Getdata_lang() const {
    return data_lang;
  }

  void Setdata_lang(int val) {
    data_lang = val;
  }

  string Getcha_result() const {
    return cha_result;
  }

  void Setcha_result(string val) {
    cha_result = val;
  }

  string Getcha_detail() const {
    return cha_detail;
  }

  void Setcha_detail(string val) {
    cha_detail = val;
  }

  string Getout_filename() const {
    return out_filename;
  }

  void Setout_filename(string val) {
    out_filename = val;
  }

  string Getremote_runid() const {
    return remote_runid;
  }

  void Setremote_runid(string remote_runid) {
    this->remote_runid = remote_runid;
  }

  void toFile();

  void save() {
    toFile();
  }

  static const string RAW_FILES_DIRECTORY;
  static const string CHA_RAW_FILES_DIRECTORY;
  static const string RESULTS_DIRECTORY;
  static const string CHA_RESULTS_DIRECTORY;
  static const string EXTENTION;

  static const string SOURCE_CODE_BEGIN;
  static const string SOURCE_CODE_END;
  static const string COMPILE_INFO_BEGIN;
  static const string COMPILE_INFO_END;
  static const string DATA_DETAIL_BEGIN;
  static const string DATA_DETAIL_END;
  static const string CHALLENGE_DETAIL_BEGIN;
  static const string CHALLENGE_DETAIL_END;
protected:
private:
  int type;
  int runid;
  int cha_id;
  string src;
  int language;
  int pid;
  int number_of_testcases;
  int time_limit;
  int case_limit;
  int memory_limit;
  int spj;
  string vname;
  string vid;
  int memory_used;
  int time_used;
  string result;
  string ce_info;
  int data_type;
  int data_lang;
  string data_detail;
  string cha_result;
  string cha_detail;
  string remote_runid;

  void addIntValue(Document &, const char *, int);
  void addStringValue(Document &, const char *, const char *);
  void addIntValueToRef(Document &, Value &, const char *, int);
  void addStringValueToRef(Document &, Value &, const char *, const char *);
  string out_filename;

};

#endif // BOTT_H

