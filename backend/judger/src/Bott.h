#ifndef BOTT_H
#define BOTT_H

#include "chaclient.h"
#include "rapidjson/document.h"

using namespace rapidjson;

class Bott {
public:
  /** Default constructor */
  Bott();
  Bott(string filename);
  /** Default destructor */
  virtual ~Bott();

  /** Access type
   * \return The current value of type
   */
  int Gettype() const {
    return type;
  }

  /** Set type
   * \param val New value to set
   */
  void Settype(int val) {
    type = val;
  }

  /** Access runid
   * \return The current value of runid
   */
  int Getrunid() const {
    return runid;
  }

  /** Set runid
   * \param val New value to set
   */
  void Setrunid(int val) {
    runid = val;
  }

  /** Access cha_id
   * \return The current value of cha_id
   */
  int Getcha_id() const {
    return cha_id;
  }

  /** Set cha_id
   * \param val New value to set
   */
  void Setcha_id(int val) {
    cha_id = val;
  }

  /** Access src
   * \return The current value of src
   */
  string Getsrc() const {
    return src;
  }

  /** Set src
   * \param val New value to set
   */
  void Setsrc(string val) {
    src = val;
  }

  /** Access language
   * \return The current value of language
   */
  int Getlanguage() const {
    return language;
  }

  /** Set language
   * \param val New value to set
   */
  void Setlanguage(int val) {
    language = val;
  }

  /** Access pid
   * \return The current value of pid
   */
  int Getpid() const {
    return pid;
  }

  /** Set pid
   * \param val New value to set
   */
  void Setpid(int val) {
    pid = val;
  }

  /** Access number_of_testcases
   * \return The current value of number_of_testcases
   */
  int Getnumber_of_testcases() const {
    return number_of_testcases;
  }

  /** Set number_of_testcases
   * \param val New value to set
   */
  void Setnumber_of_testcases(int val) {
    number_of_testcases = val;
  }

  /** Access time_limit
   * \return The current value of time_limit
   */
  int Gettime_limit() const {
    return time_limit;
  }

  /** Set time_limit
   * \param val New value to set
   */
  void Settime_limit(int val) {
    time_limit = val;
  }

  /** Access case_limit
   * \return The current value of case_limit
   */
  int Getcase_limit() const {
    return case_limit;
  }

  /** Set case_limit
   * \param val New value to set
   */
  void Setcase_limit(int val) {
    case_limit = val;
  }

  /** Access memory_limit
   * \return The current value of memory_limit
   */
  int Getmemory_limit() const {
    return memory_limit;
  }

  /** Set memory_limit
   * \param val New value to set
   */
  void Setmemory_limit(int val) {
    memory_limit = val;
  }

  /** Access spj
   * \return The current value of spj
   */
  int Getspj() const {
    return spj;
  }

  /** Set spj
   * \param val New value to set
   */
  void Setspj(int val) {
    spj = val;
  }

  /** Access vname
   * \return The current value of vname
   */
  string Getvname() const {
    return vname;
  }

  /** Set vname
   * \param val New value to set
   */
  void Setvname(string val) {
    vname = val;
  }

  /** Access vid
   * \return The current value of vid
   */
  string Getvid() const {
    return vid;
  }

  /** Set vid
   * \param val New value to set
   */
  void Setvid(string val) {
    vid = val;
  }

  /** Access memory_used
   * \return The current value of memory_used
   */
  int Getmemory_used() const {
    return memory_used;
  }

  /** Set memory_used
   * \param val New value to set
   */
  void Setmemory_used(int val) {
    memory_used = val;
  }

  /** Access time_used
   * \return The current value of time_used
   */
  int Gettime_used() const {
    return time_used;
  }

  /** Set time_used
   * \param val New value to set
   */
  void Settime_used(int val) {
    time_used = val;
  }

  /** Access result
   * \return The current value of result
   */
  string Getresult() const {
    return result;
  }

  /** Set result
   * \param val New value to set
   */
  void Setresult(string val) {
    result = val;
  }

  /** Access ce_info
   * \return The current value of ce_info
   */
  string Getce_info() const {
    return ce_info;
  }

  /** Set ce_info
   * \param val New value to set
   */
  void Setce_info(string val) {
    ce_info = val;
  }

  /** Access data_type
   * \return The current value of data_type
   */
  int Getdata_type() const {
    return data_type;
  }

  /** Set data_type
   * \param val New value to set
   */
  void Setdata_type(int val) {
    data_type = val;
  }

  /** Access data_detail
   * \return The current value of data_detail
   */
  string Getdata_detail() const {
    return data_detail;
  }

  /** Set data_detail
   * \param val New value to set
   */
  void Setdata_detail(string val) {
    data_detail = val;
  }

  /** Access data_lang
   * \return The current value of data_detail
   */
  int Getdata_lang() const {
    return data_lang;
  }

  /** Set data_lang
   * \param val New value to set
   */
  void Setdata_lang(int val) {
    data_lang = val;
  }

  /** Access cha_result
   * \return The current value of cha_result
   */
  string Getcha_result() const {
    return cha_result;
  }

  /** Set cha_result
   * \param val New value to set
   */
  void Setcha_result(string val) {
    cha_result = val;
  }

  /** Access cha_detail
   * \return The current value of cha_detail
   */
  string Getcha_detail() const {
    return cha_detail;
  }

  /** Set cha_detail
   * \param val New value to set
   */
  void Setcha_detail(string val) {
    cha_detail = val;
  }

  /** Access out_filename
   * \return The current value of out_filename
   */
  string Getout_filename() const {
    return out_filename;
  }

  /** Set out_filename
   * \param val New value to set
   */
  void Setout_filename(string val) {
    out_filename = val;
  }
  void toFile();
protected:
private:
  int type; //!< Member variable "type"
  int runid; //!< Member variable "runid"
  int cha_id; //!< Member variable "cha_id"
  string src; //!< Member variable "src"
  int language; //!< Member variable "language"
  int pid; //!< Member variable "pid"
  int number_of_testcases; //!< Member variable "number_of_testcases"
  int time_limit; //!< Member variable "time_limit"
  int case_limit; //!< Member variable "case_limit"
  int memory_limit; //!< Member variable "memory_limit"
  int spj; //!< Member variable "spj"
  string vname; //!< Member variable "vname"
  string vid; //!< Member variable "vid"
  int memory_used; //!< Member variable "memory_used"
  int time_used; //!< Member variable "time_used"
  string result; //!< Member variable "result"
  string ce_info; //!< Member variable "ce_info"
  int data_type; //!< Member variable "data_type"
  int data_lang; //!< Member variable "data_lang"
  string data_detail; //!< Member variable "data_detail"
  string cha_result; //!< Member variable "cha_result"
  string cha_detail; //!< Member variable "cha_detail"

  void addIntValue(Document &, const char *, int);
  void addStringValue(Document &, const char *, const char *);
  void addIntValueToRef(Document &, Value &, const char *, int);
  void addStringValueToRef(Document &, Value &, const char *, const char *);
  string out_filename;

};

#endif // BOTT_H
