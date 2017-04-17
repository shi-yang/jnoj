/* 
 * File:   OpenJudgeJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月26日, 下午12:28
 */

#include "OpenJudgeJudger.h"

/**
 * Create a OpenJudge Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
OpenJudgeJudger::OpenJudgeJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "G++";
  language_table[CLANG] = "GCC";
  language_table[JAVALANG] = "Java";
  language_table[FPASLANG] = "Pascal";
}

OpenJudgeJudger::~OpenJudgeJudger() {
}

void OpenJudgeJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nOpenJudge");
}

/**
 * Login to OpenJudge
 */
void OpenJudgeJudger::login() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://poj.openjudge.cn/api/auth/login/");
  string post = "email=" + escapeURL(info->GetUsername()) + "&password=" +
      escapeURL(info->GetPassword());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("\"result\":\"ERROR\"") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int OpenJudgeJudger::submit(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://poj.openjudge.cn/api/solution/submit/");
  string post = "contestId=2&problemNumber=" + bott->Getvid() +
      "&sourceEncode=base64" +
      "&language=" + escapeURL(convertLanguage(bott->Getlanguage())) +
      "&source=" + escapeURL(base64Encode(bott->Getsrc()));
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("\"result\":\"ERROR\"") != string::npos ||
      html.find("The page is temporarily unavailable") != string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  // parse remote runid from submit page
  string runid;
  if (!RE2::PartialMatch(html, "(?s)\\/practice\\\\\\/solution\\\\\\/([0-9]*)",
                         &runid)) {
    return SUBMIT_OTHER_ERROR;
  }
  bott->Setremote_runid(runid);
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * OpenJudgeJudger::getStatus(Bott * bott) {
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
        ((string) "http://poj.openjudge.cn/practice/solution/" +
            bott->Getremote_runid()).c_str());
    performCurl();

    string status = loadAllFromFile(tmpfilename);
    string result, time_used, memory_used;

    // get first row
    if (status.find("Error Occurred") != string::npos ||
        status.find("The page is temporarily unavailable") != string::npos) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status, "(?s)<p class=\"compile-status\">.*?>(.*?)<",
                           &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      result = convertResult(result);
      if (result != "Compile Error") {
        // no details for CE results
        if (!RE2::PartialMatch(status,
                               "(?s).*?([0-9]*)kB</dd>.*?([0-9]*)ms</dd>",
                               &memory_used, &time_used)) {
          throw Exception("Failed to parse details from status row.");
        }
      } else {
        memory_used = time_used = "0";
      }
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
 * Get compile error info from OpenJudge
 * @param bott      Result bott file
 * @return Compile error info
 */
string OpenJudgeJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string)"http://poj.openjudge.cn/practice/solution/" +
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
string OpenJudgeJudger::convertResult(string result) {
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output Limit Exceeded") != string::npos)
    return "Output Limit Exceed";
  if (result.find("System Error") != string::npos)
    return "Judge Error";
  return trim(result);
}
