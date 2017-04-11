/* 
 * File:   HRBUSTJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年8月22日, 下午10:26
 */

#include "HRBUSTJudger.h"

HRBUSTJudger::HRBUSTJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "2";
  language_table[CLANG]  = "1";
  language_table[JAVALANG]  = "3";
}

HRBUSTJudger::~HRBUSTJudger() {
}

void HRBUSTJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nHRBUST");
}

/**
 * Login to HRBUST
 */
void HRBUSTJudger::login() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.hrbust.edu.cn/index.php?m=User&a=login");
  string post = (string) "m=User&a=login&ajax=1&user_name=" +
      escapeURL(info->GetUsername()) + "&password=" +
      escapeURL(info->GetPassword());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  if (html.find("{\"status\":0") != string::npos ||
      html.find("<div class='body'>Sorry!Login Error,Please Retry!</div>") !=
          string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int HRBUSTJudger::submit(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(curl,
                   CURLOPT_URL,
                   "http://acm.hrbust.edu.cn/index.php?m=ProblemSet&a=postCode");
  string post = (string) "jumpUrl=&language=" +
      convertLanguage(bott->Getlanguage()) + "&problem_id=" + bott->Getvid() +
      "&source_code=" + escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  if (html.find("<html") == string::npos ||
      html.find("var is_login=\"\";") != string::npos)
    return SUBMIT_OTHER_ERROR;
  return SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * HRBUSTJudger::getStatus(Bott * bott) {
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
        ((string) "http://acm.hrbust.edu.cn/index.php?m=Status&a="
            "showStatus&problem_id=" + escapeURL(bott->Getvid()) +
            "&user_name=" + escapeURL(info->GetUsername()) + "&language=" +
            convertLanguage(bott->Getlanguage())).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (html.find("var is_login=\"\";") != string::npos ||
        !RE2::PartialMatch(html,
                           "(?s)<table class=\"ojlist\".*?<tr.*?(<tr.*?</tr>)",
                           &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
                           "(?s)<td.*?<td>([0-9]*).*?<td.*?<td.*?>(.*?)</td>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = convertResult(trim(result));
    if (isFinalResult(result)) {
      // result is the final one
      if (!RE2::PartialMatch(status,
                             "(?s)>([0-9]*)ms.*?>([0-9]*)k", &time_used,
                             &memory_used)) {
        throw Exception("Failed to parse details from status row.");
      }
      result_bott = new Bott;
      result_bott->Settype(RESULT_REPORT);
      result_bott->Setresult(result);
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
string HRBUSTJudger::convertResult(string result) {
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output Limit Exceeded") != string::npos)
    return "Output Limit Exceed";
  if (result.find("System Error") != string::npos) return
      "Judge Error";
  if (result.find("Compile Error") != string::npos) return
      "Compile Error";
  return trim(result);
}

/**
 * Get compile error info
 * @param bott      Result bott file
 * @return Compile error info
 */
string HRBUSTJudger::getCEinfo(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string)"http://acm.hrbust.edu.cn/index.php?m=Status&a="
          "showCompileError&run_id=" + bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info, "(?s)showcode_mod_info.*?>(.*?)</td>",
                         &result)) {
    return "";
  }

  return result;
}
