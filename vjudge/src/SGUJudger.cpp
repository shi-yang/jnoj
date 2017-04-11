/* 
 * File:   SGUJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月25日, 下午12:10
 */

#include "SGUJudger.h"

/**
 * Create a SGU Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
SGUJudger::SGUJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "GNU CPP (MinGW, GCC 4)";
  language_table[CLANG] = "GNU C (MinGW, GCC 4)";
  language_table[JAVALANG] = "JAVA 7";
  language_table[FPASLANG] = "Delphi 7.0";
  language_table[CSLANG] = "C#";
  language_table[VCLANG] = "Visual Studio C++ 2010";
  language_table[VCPPLANG] = "Visual Studio C 2010";
}

SGUJudger::~SGUJudger() {
}

void SGUJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nSGU");
}

/**
 * Login to SGU
 */
void SGUJudger::login() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://acm.sgu.ru/login.php");
  string post = "try_user_id=" + escapeURL(info->GetUsername()) +
      "&try_user_password=" + escapeURL(info->GetPassword()) +
      "&type_log=login";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("<h4>Wrong ID or PASSWORD</h4>") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int SGUJudger::submit(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.sgu.ru/sendfile.php?contest=0");
  string post = (string) "id=" + escapeURL(info->GetUsername()) + "&pass=" +
      escapeURL(info->GetPassword()) + "&problem=" + bott->Getvid() +
      "&elang=" + convertLanguage(bott->Getlanguage()) + "&source=" +
      escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("Your solution was successfully submitted.") == string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * SGUJudger::getStatus(Bott * bott) {
  time_t begin_time = time(NULL);

  Bott * result_bott;
  while (true) {
    // check wait time
    if (time(NULL) - begin_time > info->GetMax_wait_time()) {
      throw Exception("Failed to get current result, judge time out.");
    }

    prepareCurl();
    curl_easy_setopt(
        curl, CURLOPT_URL,
        ((string) "http://acm.sgu.ru/status.php?id=" +
            escapeURL(info->GetUsername()) + "&problem=" +
            escapeURL(bott->Getvid())).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(html, "(?s)(<TR class=st1.*?</TR>)", &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
                           "(?s)<TD>([0-9]*?)</TD>.*<TD class=btab>(.*?)</TD>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // if result if final, get details
      if (!RE2::PartialMatch(
          status,
          "(?s)<TD>([0-9]*?)</TD>.*<TD class=btab>(.*?)</TD>.*?<TD>"
              "([0-9]*) ms.*?<TD>([0-9]*) kb",
          &runid, &result, &time_used, &memory_used)) {
        throw Exception("Failed to parse details from status row.");
      }
      result_bott = new Bott;
      result_bott->Settype(RESULT_REPORT);
      result_bott->Setresult(convertResult(result));
      result_bott->Settime_used(stringToInt(time_used));
      result_bott->Setmemory_used(stringToInt(memory_used));
      result_bott->Setremote_runid(trim(runid));
      break;
    }
  }
  return result_bott;
}

/**
 * Get compile error info from SGU
 * @param bott      Result bott file
 * @return Compile error info
 */
string SGUJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://acm.sgu.ru/cerror.php?id=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info, "(?s)<pre>(.*)</pre>", &result)) {
    return "";
  }
  result = replaceAll(result, "<br>", "\n");
  return unescapeString(result);
}

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string SGUJudger::convertResult(string result) {
  if (result.find("Compilation Error") != string::npos)
    return "Compile Error";
  if (result.find("Accepted") != string::npos)
    return "Accepted";
  if (result.find("Wrong answer") != string::npos)
    return "Wrong Answer";
  if (result.find("Runtime Error") != string::npos)
    return "Runtime Error";
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Presentation Error") != string::npos)
    return "Presentation Error";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  return trim(result);
}
