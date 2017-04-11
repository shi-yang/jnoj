/* 
 * File:   NBUTJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年8月5日, 下午4:47
 */

#include "NBUTJudger.h"

/**
 * Create a NBUT Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
NBUTJudger::NBUTJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "2";
  language_table[CLANG] = "1";
  language_table[FPASLANG] = "4";
}

NBUTJudger::~NBUTJudger() {
}

void NBUTJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nNBUT");
}

/**
 * Login to NBUT
 */
void NBUTJudger::login() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "https://ac.2333.moe/User/chklogin.xhtml");
  string post = "username=" + escapeURL(info->GetUsername()) + "&password=" +
      escapeURL(info->GetPassword());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("1") != 0) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int NBUTJudger::submit(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "https://ac.2333.moe/Problem/submitok.xhtml");
  string post = (string) "id=" + bott->Getvid() +
      "&language=" + convertLanguage(bott->Getlanguage()) +
      "&code=" + escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("{\"status\":0") != string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * NBUTJudger::getStatus(Bott * bott) {
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
        ((string)"https://ac.2333.moe/Problem/status.xhtml?username=" +
            escapeURL(info->GetUsername()) + "&language=" +
            convertLanguage(bott->Getlanguage()) + "&problemid=" +
            escapeURL(bott->Getvid())).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (html.find("Error Occurred") != string::npos ||
        html.find("The page is temporarily unavailable") != string::npos ||
        !RE2::PartialMatch(html, "(?s)<tbody.*?(<tr>.*?</tr>)", &status)) {
      throw Exception("Failed to get status row.");
    }
    // get result
    if (!RE2::PartialMatch(status,
                           "(?s)<td.*?>([0-9]*).*?<span style=.*?>(.*?)</span>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      if (!RE2::PartialMatch(
          status,
          "(?s)<span style=.*?</span>.*?<td.*?>([0-9]*).*?<td.*?>([0-9]*)",
          &time_used, &memory_used)) {
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
 * Get compile error info from NBUT
 * @param bott      Result bott file
 * @return Compile error info
 */
string NBUTJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "https://ac.2333.moe/Problem/viewce.xhtml?submitid=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info, "(?s)<pre.*?>(.*)</pre>", &result)) {
    return "";
  }

  return result;
}

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string NBUTJudger::convertResult(string result) {
  if (result.find("ACCEPTED") != string::npos)
    return "Accepted";
  if (result.find("WRONG_ANSWER") != string::npos)
    return "Wrong Answer";
  if (result.find("PRESENTATION_ERROR") != string::npos)
    return "Presentation Error";
  if (result.find("RUNTIME_ERROR") != string::npos)
    return "Runtime Error";
  if (result.find("TIME_LIMIT_EXCEEDED") != string::npos)
    return "Time Limit Exceed";
  if (result.find("MEMORY_LIMIT_EXCEEDED") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("OUTPUT_LIMIT_EXCEEDED") != string::npos)
    return "Output Limit Exceed";
  if (result.find("COMPILATION_ERROR") != string::npos)
    return "Compile Error";
  if (result.find("RESTRICTED_FUNCTION") != string::npos)
    return "Restricted Function";
  return trim(result);
}
