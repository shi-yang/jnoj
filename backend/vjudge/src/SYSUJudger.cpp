/* 
 * File:   SYSUJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月26日, 上午11:49
 */

#include "SYSUJudger.h"

/**
 * Create a SYSU Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
SYSUJudger::SYSUJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "2";
  language_table[CLANG] = "1";
  language_table[FPASLANG] = "3";
}

SYSUJudger::~SYSUJudger() {
}

void SYSUJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nSYSU");
}

/**
 * Login to SYSU
 */
void SYSUJudger::login() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://soj.sysu.edu.cn/action.php?act=Login");
  string post = "username=" + escapeURL(info->GetUsername()) + "&password=" +
      escapeURL(info->GetPassword()) + "&lsession=1";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("{\"success\":1") == string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int SYSUJudger::submit(Bott * bott) {


  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://soj.sysu.edu.cn/action.php?act=Submit");
  string post = (string)
      "cid=0&language=" + convertLanguage(bott->Getlanguage()) + "&pid=" +
      bott->Getvid() + "&source=" + escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("{\"success\":1") == string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  // parse remote runid from submit page
  string runid;
  if (!RE2::PartialMatch(html, "(?s)sid.*?:.*?([0-9]*)", &runid)) {
    return SUBMIT_OTHER_ERROR;
  }
  bott->Setremote_runid(trim(runid));
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * SYSUJudger::getStatus(Bott * bott) {
  time_t begin_time = time(NULL);

  Bott * result_bott;
  while (true) {
    // check wait time
    if (time(NULL) - begin_time > info->GetMax_wait_time()) {
      throw Exception("Failed to get current result, judge time out.");
    }

    prepareCurl();
    curl_easy_setopt(curl, CURLOPT_URL,
                     "http://soj.sysu.edu.cn/action.php?act=QueryStatus");
    string post = "sid=" + bott->Getremote_runid();
    curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
    performCurl();

    string status = loadAllFromFile(tmpfilename);
    string result, time_used, memory_used;

    // get first row
    if (status.find("{\"success\":1") == string::npos) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status, "(?s)status.*?:.*?\"(.*?)\"", &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      result = convertResult(result);
      if (!RE2::PartialMatch(
          status, "(?s)run_time.*?:.*?\"(.*?)\".*?run_memory.*?:.*?\"(.*?)\"",
          &time_used, &memory_used)) {
        throw Exception("Failed to parse details from status row.");
      }
      int time_ms = stringToDouble(time_used) * 1000 + 0.001;
      time_used = intToString(time_ms);
      result_bott = new Bott;
      result_bott->Setremote_runid(bott->Getremote_runid());
      result_bott->Settype(RESULT_REPORT);
      result_bott->Setresult(result);
      result_bott->Settime_used(stringToInt(time_used));
      result_bott->Setmemory_used(stringToInt(memory_used));
      break;
    }
  }
  return result_bott;
}

/**
 * Get compile error info from SYSU
 * @param bott      Result bott file
 * @return Compile error info
 */
string SYSUJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://soj.sysu.edu.cn/compileresult.php?sid=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info, "(?s)<pre>(.*?)</pre>", &result)) {
    return "";
  }
  return result;
}

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string SYSUJudger::convertResult(string result) {
    if (result.find("Compilation Error") != string::npos)
      return "Compile Error";
    if (result.find("Runtime Error") != string::npos)
      return "Runtime Error";
    if (result.find("Time Limit Exceeded") != string::npos)
      return "Time Limit Exceed";
    if (result.find("Memory Limit Exceeded") != string::npos)
      return "Memory Limit Exceed";
    if (result.find("Output Limit Exceeded") != string::npos)
      return "Output Limit Exceed";
    if (result.find("System Error") != string::npos)
      return "Judge Error";
    if (result.find("Other") != string::npos) return
        "Judge Error";
    if (result.find("Restrict Function") != string::npos)
      return "Restricted Function";
    return trim(result);
}
