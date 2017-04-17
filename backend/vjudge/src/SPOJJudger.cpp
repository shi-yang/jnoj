/* 
 * File:   SPOJJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月25日, 下午3:10
 */

#include "SPOJJudger.h"

/**
 * Create a SPOJ Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
SPOJJudger::SPOJJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "41";
  language_table[CLANG] = "11";
  language_table[JAVALANG] = "10";
  language_table[FPASLANG] = "22";
  language_table[PYLANG] = "4";
  language_table[CSLANG] = "27";
  language_table[FORTLANG] = "5";
  language_table[PERLLANG] = "3";
  language_table[RUBYLANG] = "17";
  language_table[ADALANG] = "7";
}

SPOJJudger::~SPOJJudger() {
}

void SPOJJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nSPOJ");
}

/**
 * Login to SPOJ
 */
void SPOJJudger::login() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_REFERER, "http://www.spoj.com/logout");
  curl_easy_setopt(curl, CURLOPT_URL, "http://www.spoj.com/logout");
  string post = "login_user=" + escapeURL(info->GetUsername()) + "&password=" +
      escapeURL(info->GetPassword()) + "&submit=Log+In";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("Authentication failed! <br/><a href=\"/forgot\">") !=
      string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int SPOJJudger::submit(Bott * bott) {

  // prepare form for post
  struct curl_httppost * formpost = NULL;
  struct curl_httppost * lastptr = NULL;
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "submit",
               CURLFORM_COPYCONTENTS, "Send",
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "problemcode",
               CURLFORM_COPYCONTENTS, bott->Getvid().c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "lang",
               CURLFORM_COPYCONTENTS,
                   convertLanguage(bott->Getlanguage()).c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "file",
               CURLFORM_COPYCONTENTS, bott->Getsrc().c_str(),
               CURLFORM_END);

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://www.spoj.com/submit/complete/");
  curl_easy_setopt(curl, CURLOPT_HTTPPOST, formpost);
  performCurl();

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  // cout << html << endl;
  if (html.find("in this language for this problem") != string::npos) {
    return SUBMIT_INVALID_LANGUAGE;
  }
  if (html.find("<form name=\"login\"  action=\"") != string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * SPOJJudger::getStatus(Bott * bott) {
  time_t begin_time = time(NULL);

  Bott * result_bott;

  while (true) {
    // check wait time
    if (time(NULL) - begin_time > info->GetMax_wait_time()) {
      throw Exception("Failed to get current result, judge time out.");
    }

    prepareCurl();
    curl_easy_setopt(curl, CURLOPT_URL, ("http://www.spoj.com/status/" +
        info->GetUsername() + "/ajax=1").c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(html, "(?s)(<tr class=\"kol.*?</tr>)", &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
        "(?s)statusres_([0-9]*).*?>\\w*(?:<.*?>)?(.*?)\\w*<span",
        &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      result = convertResult(result);
      if (result == "Accepted" || result == "Runtime Error" ||
          result == "Wrong Answer") {
        // only have details for these three results
        string unit;
        if (!RE2::PartialMatch(
            status,
            "(?s)statustime_.*?<a.*?>(.*?)</a>.*?statusmem_.*?>\\s*(.*?)(M|k)",
            &time_used, &memory_used, &unit)) {
          throw Exception("Failed to parse details from status row.");
        }
        int time_ms = stringToDouble(time_used) * 1000 + 0.001;
        time_used = intToString(time_ms);
        if (unit == "M") {
          int memory_mb = stringToDouble(memory_used) * 1024 + 0.001;
          memory_used = intToString(memory_mb);
        }
      } else {
        time_used = memory_used = "0";
      }
      result_bott = new Bott;
      result_bott->Setremote_runid(runid);
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
 * Get compile error info from SPOJ
 * @param bott      Result bott file
 * @return Compile error info
 */
string SPOJJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://www.spoj.com/error/" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info, "(?s)<small>(.*?)</small>", &result)) {
    return "";
  }
  return result;
}

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string SPOJJudger::convertResult(string result) {
  if (result.find("compilation error") != string::npos)
    return "Compile Error";
  if (result.find("wrong answer") != string::npos)
    return "Wrong Answer";
  if (result.find("SIGXFSZ") != string::npos)
    return "Output Limit Exceed";
  if (result.find("runtime error") != string::npos)
    return "Runtime Error";
  if (result.find("time limit exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("memory limit exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("SIGABRT") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("accepted") != string::npos)
    return "Accepted";
  return trim(result);
}
