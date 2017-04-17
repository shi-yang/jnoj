/* 
 * File:   HUSTJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年2月4日, 下午9:05
 */

#include "HUSTJudger.h"

HUSTJudger::HUSTJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "1";
  language_table[CLANG]  = "0";
  language_table[JAVALANG]  = "3";
  language_table[FPASLANG]  = "2";
}

HUSTJudger::~HUSTJudger() {
}

void HUSTJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nHUST");
}

/**
 * Login to HUST
 */
void HUSTJudger::login() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://acm.hust.edu.cn/login");
  string post = (string) "username=" + info->GetUsername() + "&pwd=" +
      info->GetPassword() + "&code=";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  //cout<<ts;
  if (html.find("<div class=\"alert alert-danger\">") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int HUSTJudger::submit(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://acm.hust.edu.cn/problem/submit");
  string post = (string) "pid=" + bott->Getvid() +
      "&language=" + convertLanguage(bott->Getlanguage()) +
      "&source=" + escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  if (html.find("<div class=\"alert alert-danger\">") != string::npos)
    return SUBMIT_OTHER_ERROR;
  return SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * HUSTJudger::getStatus(Bott * bott) {
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
        ((string) "http://acm.hust.edu.cn/status?pid=" + bott->Getvid() +
            "&uid=" + info->GetUsername() + "&language" +
            convertLanguage(bott->Getlanguage())).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(html, "(?s)<tr.*?(<tr.*?</tr>)", &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(
        status, "(?s)source/([0-9]*).*?<td.*?<td.*?<td>\\s*(.*?)\\s*<",
        &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // only accepted runs has details in hust
      if (result == "Accepted") {
        if (!RE2::PartialMatch(status, "(?s)<td>([0-9]*)ms.*?([0-9]*)kb</td>",
                               &time_used, &memory_used)) {
          throw Exception("Failed to parse details from status row.");
        }
      } else {
        memory_used = time_used = "0";
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
 * Convert result text to local ones, keep consistency
 * @param result Original result
 * @return Converted local result
 */
string HUSTJudger::convertResult(string result) {
  return trim(result);
}

/**
 * Get compile error info
 * @param bott      Result bott file
 * @return Compile error info
 */
string HUSTJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string)"http://acm.hust.edu.cn/solution/source/" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info,
                         "(?s)<pre class=\"col-sm-12 linenums\">(.*?)</pre>",
                         &result)) {
    return "";
  }

  return result;
}
