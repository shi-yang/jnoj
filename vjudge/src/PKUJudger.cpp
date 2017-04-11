/* 
 * File:   PKUJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年2月4日, 下午2:41
 */

#include "PKUJudger.h"

/**
 * Create a PKU Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
PKUJudger::PKUJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "0";
  language_table[CLANG]  = "1";
  language_table[JAVALANG]  = "2";
  language_table[FPASLANG]  = "3";
  language_table[FORTLANG] = "6";
  language_table[VCLANG] = "5";
  language_table[VCPPLANG] = "4";
}

PKUJudger::~PKUJudger() {
}

void PKUJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nPKU");
}

/**
 * Login to PKU
 */
void PKUJudger::login() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://poj.org/login");
  string post = (string) "user_id1=" + info->GetUsername() + "&password1=" +
      info->GetPassword() + "&B1=login";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  //cout<<ts;
  if (html.find("alert(\"Login failed!)") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int PKUJudger::submit(Bott * bott) {
  string post = (string) "problem_id=" + bott->Getvid() +
      "&language=" + convertLanguage(bott->Getlanguage()) +
      "&source=" + escapeURL(base64Encode(bott->Getsrc())) +
      "&submit=Submit&encoded=1";
  try {
    prepareCurl();
    curl_easy_setopt(curl, CURLOPT_URL, "http://poj.org/submit");
    curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
    performCurl();
  } catch (Exception & e) {
    log("POST denied by POJ...");
  }

  string html = loadAllFromFile(tmpfilename);
  if (html.find("Error Occurred") != string::npos ||
      html.find("The page is temporarily unavailable") != string::npos)
    return SUBMIT_OTHER_ERROR;
  return SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * PKUJudger::getStatus(Bott * bott) {
  time_t begin_time = time(NULL);

  Bott * result_bott;
  while (true) {
    usleep(2000000); // wait 2s for PKU, since it forces refresh rate
    // check wait time
    if (time(NULL) - begin_time > info->GetMax_wait_time()) {
      throw Exception("Failed to get current result, judge time out.");
    }

    prepareCurl();
    curl_easy_setopt(
        curl, CURLOPT_URL,
        ((string) "http://poj.org/status?problem_id=" + bott->Getvid() +
            "&user_id=" + info->GetUsername() + "&language=" +
            convertLanguage(bott->Getlanguage())).c_str());
    curl_easy_setopt(curl, CURLOPT_COOKIEFILE, "");
    curl_easy_setopt(curl, CURLOPT_COOKIEJAR, "");
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (html.find("Error Occurred") != string::npos ||
        html.find("The page is temporarily unavailable") != string::npos ||
        !RE2::PartialMatch(html, "(?s)class=in.*?(<tr align=center.*?</tr>)",
                           &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status, "<td>(.*?)</td>.*<font.*?>(.*?)</font>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      if (result == "Accepted") {
        // only accepted run has details in pku
        if (!RE2::PartialMatch(status, "([0-9]*)K.*?([0-9]*)MS", &memory_used,
                               &time_used)) {
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
string PKUJudger::convertResult(string result) {
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

/**
 * Get compile error info
 * @param bott      Result bott file
 * @return Compile error info
 */
string PKUJudger::getCEinfo(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://poj.org/showcompileinfo?solution_id=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info, "(?s)<pre>(.*)</pre>", &result)) {
    return "";
  }

  char * ce_info = new char[info.length() + 1];
  decode_html_entities_utf8(ce_info, result.c_str());
  result = ce_info;
  delete [] ce_info;

  return result;
}
