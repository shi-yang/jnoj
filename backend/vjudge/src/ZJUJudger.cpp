/* 
 * File:   ZJUJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月25日, 下午2:09
 */

#include "ZJUJudger.h"

/**
 * Create a ZJU Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
ZJUJudger::ZJUJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "2";
  language_table[CLANG] = "1";
  language_table[JAVALANG] = "4";
  language_table[FPASLANG] = "3";
  language_table[PYLANG] = "5";
  language_table[PERLLANG] = "6";
}

ZJUJudger::~ZJUJudger() {
}

void ZJUJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nZJU");
}

/**
 * Login to ZJU
 */
void ZJUJudger::login() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.zju.edu.cn/onlinejudge/login.do");
  string post = "handle=" + escapeURL(info->GetUsername()) +
      "&password=" + escapeURL(info->GetPassword());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("Handle or password is invalid.") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int ZJUJudger::submit(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.zju.edu.cn/onlinejudge/submit.do");
  string post = (string) "problemCode=" + bott->Getvid() +
      "&languageId=" + convertLanguage(bott->Getlanguage()) +
      "&source=" + escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("Submit Successfully</div>") == string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  // parse remote runid from submit page
  string runid;
  if (!RE2::PartialMatch(html, "(?s)The submission id is.*?>(.*?)<", &runid)) {
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
Bott * ZJUJudger::getStatus(Bott * bott) {
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
        ((string) "http://acm.zju.edu.cn/onlinejudge/showRuns.do?"
            "contestId=1&idStart=" + bott->Getremote_runid() + "&idEnd=" +
            bott->Getremote_runid()).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(html, "(?s)(<tr class=\"rowOdd\">.*?</tr>)",
                           &status)) {
      continue;
    }

    // get result
    if (!RE2::PartialMatch(
        status, "(?s)<td class=\"runJudgeStatus\".*?<span.*?>(.*?)</span>",
        &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      result = convertResult(result);
      if (!RE2::PartialMatch(
          status,
          "(?s)<td class=\"runTime\".*?>(.*?)</td>.*"
              "<td class=\"runMemory\".*?>(.*?)</td>",
          &time_used, &memory_used)) {
        throw Exception("Failed to parse details from status row.");
      }
      result_bott = new Bott;
      result_bott->Setremote_runid(bott->Getremote_runid());
      result_bott->Settype(RESULT_REPORT);
      result_bott->Setresult(result);
      result_bott->Settime_used(stringToInt(time_used));
      result_bott->Setmemory_used(stringToInt(memory_used));
      if (result == "Compile Error") {
        // hack for ZJU, don't know why its submission id is inconsistent with
        // judge protocol
        if (!RE2::PartialMatch(
            status, "(?s)showJudgeComment\\.do\\?submissionId=([0-9]*)",
            &submission_id_for_ce)) {
          submission_id_for_ce = bott->Getremote_runid();
        }
      }
      break;
    }
  }
  return result_bott;
}

/**
 * Get compile error info from ZJU
 * @param bott      Result bott file
 * @return Compile error info
 */
string ZJUJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://acm.zju.edu.cn/onlinejudge/showJudgeComment.do?"
          "submissionId=" + submission_id_for_ce).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  return info;
}

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string ZJUJudger::convertResult(string result) {
  if (result.find("Compilation Error") != string::npos)
    return "Compile Error";
  if (result.find("Segmentation Fault") != string::npos)
    return "Runtime Error";
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Non-zero Exit Code") != string::npos)
    return "Runtime Error";
  if (result.find("Floating Point Error") != string::npos)
    return "Runtime Error";
  if (result.find("Output Limit Exceeded") != string::npos)
    return "Output Limit Exceed";
  return trim(result);
}
