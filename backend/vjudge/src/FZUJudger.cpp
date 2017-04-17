/* 
 * File:   FZUJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年2月4日, 下午5:00
 */

#include "FZUJudger.h"

FZUJudger::FZUJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "0";
  language_table[CLANG]  = "1";
  language_table[JAVALANG]  = "2";
  language_table[FPASLANG]  = "3";
  language_table[VCLANG] = "4";
  language_table[VCPPLANG] = "5";
}

FZUJudger::~FZUJudger() {
}

void FZUJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nFZU");
}

/**
 * Login to FZU
 */
void FZUJudger::login() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://acm.fzu.edu.cn/login.php?act=1");
  string post = (string) "uname=" + info->GetUsername() + "&upassword=" +
      info->GetPassword() + "&submit=Submit";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  //cout<<ts;
  if (html.find(
      "Warning: Please Check Your UserID And Password!") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int FZUJudger::submit(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://acm.fzu.edu.cn/submit.php?act=5");
  string post = (string) "usr=" + info->GetUsername() + "&lang=" +
      convertLanguage(bott->Getlanguage()) + "&pid=" + bott->Getvid() + "&code=" +
      escapeURL(bott->Getsrc()) + "&submit=Submit";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  if (html.find("Warning: This problem is not exist.") != string::npos ||
      html.find("The page is temporarily unavailable") != string::npos ||
      html.find(">Login</a>") != string::npos) return SUBMIT_OTHER_ERROR;
  return SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * FZUJudger::getStatus(Bott * bott) {
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
        ((string) "http://acm.fzu.edu.cn/log.php?pid=" +
            bott->Getvid() + "&user=" + info->GetUsername()).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (html.find("Error Occurred") != string::npos ||
        html.find("The page is temporarily unavailable") != string::npos ||
        html.find(">Login</a>") != string::npos ||
        !RE2::PartialMatch(html, "(?s)(<tr onmouseover.*?</tr>)", &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
                           "(?s)<td>([0-9]*?)</td>.*<font.*?>(.*)</font>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      if (result == "Accepted") {
        // only accepted run has details in fzu
        if (!RE2::PartialMatch(status, "(?s)([0-9]*) ms.*?([0-9]*)KB",
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
string FZUJudger::convertResult(string result) {
  if (result.find("Compile Error") != string::npos)
    return "Compile Error";
  if (result.find("Restrict Function Call") != string::npos)
    return "Restricted Function";
  return trim(result);
}

/**
 * Get compile error info
 * @param bott      Result bott file
 * @return Compile error info
 */
string FZUJudger::getCEinfo(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   ((string) "http://acm.fzu.edu.cn/ce.php?sid=" +
                   bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info,
                         "(?s)<font color=\"blue\" size=\"-1\">(.*?)</font>",
                         &result)) {
    return "";
  }

  char * ce_info = new char[info.length() + 1];
  decode_html_entities_utf8(ce_info, result.c_str());
  result = ce_info;
  delete [] ce_info;

  return result;
}
