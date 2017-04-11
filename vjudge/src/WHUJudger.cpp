/* 
 * File:   WHUJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月25日, 下午4:01
 */

#include "WHUJudger.h"
#include "WHUHelper.hpp"

/**
 * Create a WHU Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
WHUJudger::WHUJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "2";
  language_table[CLANG] = "1";
  language_table[JAVALANG] = "3";
  language_table[FPASLANG] = "4";
}

WHUJudger::~WHUJudger() {
}

void WHUJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nWHU");
}

/**
 * Get seed for md5 hash
 * @return seed for hash
 */
string WHUJudger::getSeed() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://acm.whu.edu.cn/land/");
  performCurl();

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://acm.whu.edu.cn/land/ajax/vcode");
  curl_easy_setopt(curl, CURLOPT_POST, 1);
  // have to set an empty string, otherwise Content-Length will be -1
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, "");
  performCurl();

  return loadAllFromFile(tmpfilename);
}

/**
 * Login to WHU
 */
void WHUJudger::login() {
  string seed = trim(getSeed());
  string encryptedPassword = info->GetPassword();
  encryptedPassword = WHUHelper::hex_md5(encryptedPassword) + seed;
  encryptedPassword = WHUHelper::hex_md5(encryptedPassword);

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_REFERER, "http://acm.whu.edu.cn/land/");
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.whu.edu.cn/land/user/do_login");
  string post = "origURL=%2Fland&password=&passEnc=" + encryptedPassword +
      "&username=" + info->GetUsername() + "&seed=" + seed + "&remember=1";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("<a href=\"/land/user/logout\">Logout</a>") == string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int WHUJudger::submit(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.whu.edu.cn/land/submit/do_submit");
  string post = "lang=" + convertLanguage(bott->Getlanguage()) +
      "&problem_id=" + bott->Getvid() + "&source=" + escapeURL(bott->Getsrc()) +
      "&submit=Submit";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check submit status
  string html = loadAllFromFile(tmpfilename);

  if (html.find("This problem does not exist in Land") != string::npos ||
      html.find("The page is temporarily unavailable") != string::npos ||
      html.find("<a href=\"/land/user/login\">Login</a>") != string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * WHUJudger::getStatus(Bott * bott) {
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
        ((string) "http://acm.whu.edu.cn/land/status?username=" +
            info->GetUsername() + "&problem_id=" + bott->Getvid() +
            "&language=" + convertLanguage(bott->Getlanguage())).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(html, "(?s)(<tr class=\"tro\">.*?</tr>)",
                           &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
                           "(?s)<td.*?>([0-9]*).*?<font.*?>(.*?)</font>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      result = convertResult(result);
      if (!RE2::PartialMatch(
          status,
          "(?s)<td.*?>([0-9]*).*?<font.*?</font>.*?<td.*?>"
              "([0-9]*).*?<td.*?>([0-9]*)",
          &runid, &memory_used, &time_used)) {
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
 * Get compile error info from WHU
 * @param bott      Result bott file
 * @return Compile error info
 */
string WHUJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://acm.whu.edu.cn/land/source/info?source_id=" +
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
string WHUJudger::convertResult(string result) {
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
  return trim(result);
}

