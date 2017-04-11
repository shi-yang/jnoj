/* 
 * File:   NJUPTJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年8月12日, 下午9:25
 */

#include "NJUPTJudger.h"

NJUPTJudger::NJUPTJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "G++";
  language_table[CLANG]  = "GCC";
  language_table[JAVALANG]  = "Java";
  language_table[FPASLANG]  = "Pascal";
}

NJUPTJudger::~NJUPTJudger() {
}

void NJUPTJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nNJUPT");
}

/**
 * Login to NJUPT
 */
void NJUPTJudger::login() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.njupt.edu.cn/acmhome/login.do");
  string post = (string) "userName=" + info->GetUsername() + "&password=" +
      info->GetPassword();
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  //cout<<ts;
  if (html.find("<div style=\"color:red;\"><UL><LI>") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int NJUPTJudger::submit(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.njupt.edu.cn/acmhome/submitcode.do");
  string post = (string) "problemId=" + bott->Getvid() +
      "&language=" + convertLanguage(bott->Getlanguage()) +
      "&code=" + escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());

  try {
    performCurl();
  } catch (Exception & e) {
    return SUBMIT_OTHER_ERROR;
  }

  string html = loadAllFromFile(tmpfilename);
  if (html.find("<div style=\"color:red;\"><UL><LI>") != string::npos)
    return SUBMIT_OTHER_ERROR;
  return SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * NJUPTJudger::getStatus(Bott * bott) {
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
        ((string) "http://acm.njupt.edu.cn/acmhome/showstatus.do").c_str());
    string post = (string) "problemId=" + bott->Getvid() + "&languageS=" +
        convertLanguage(bott->Getlanguage()) + "&userName=" +
        escapeURL(info->GetUsername()) + "&resultS=All";
    curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
    performCurl();

    string html = charsetConvert("GBK", "UTF-8", loadAllFromFile(tmpfilename));
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(html, "(?s)<table.*?</thead>.*?(<tr.*?</tr>)",
                           &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(
        status,
        "(?s)method=showdetail.*?value=\"(.*?)\".*?<b acc=\"acc\"></b>\\s*(.*?)"
            "\\s*<b acc=\"acc\"></b>",
        &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = convertResult(trim(result));
    if (isFinalResult(result)) {
      // result is the final one, get details
      if (result == "Accepted") {
        // only accepted run has details
        if (!RE2::PartialMatch(
            status,
            "(?s)([0-9]*)<b ms=\"ms\"></b>MS.*?([0-9]*)<b k=\"k\"></b>K",
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
string NJUPTJudger::convertResult(string result) {
  if (result.find("Time Limit Exceed") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceed") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output Limit Exceed") != string::npos)
    return "Output Limit Exceed";
  if (result.find("Compile Error") != string::npos)
    return "Compile Error";
  if (result.find("Runtime Error") != string::npos)
    return "Runtime Error";
  if (result.find("Wrong Answer") != string::npos)
    return "Wrong Answer";
  if (result.find("Presentation Error") != string::npos)
    return "Presentation Error";
  if (result.find("Accepted") != string::npos)
    return "Accepted";
  if (result.find("System Error") != string::npos)
    return "Judge Error";
  if (result.find("Judge Delay") != string::npos)
    return "Judge Error";
  if (result.find("Judge Error") != string::npos)
    return "Judge Error";
  return trim(result);
}

/**
 * Get compile error info
 * @param bott      Result bott file
 * @return Compile error info
 */
string NJUPTJudger::getCEinfo(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string)"http://acm.njupt.edu.cn/acmhome/compileError.do?id=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = charsetConvert("GBK", "UTF-8", loadAllFromFile(tmpfilename));
  string result;
  char * buffer = new char[info.length() * 2];

  if (!RE2::PartialMatch(
      info,
      "(?s)Details of Compile Error</strong></h2></div>(.*)"
          "<div align=\"center\">",
      &result)) {
    return "";
  }

  strcpy(buffer, result.c_str());
  decode_html_entities_utf8(buffer, NULL);
  result = buffer;
  delete [] buffer;

  return trim(result);
}
