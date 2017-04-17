/* 
 * File:   UralJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月25日, 下午1:22
 */

#include "UralJudger.h"

/**
 * Create a Ural Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
UralJudger::UralJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "26";
  language_table[CLANG] = "25";
  language_table[JAVALANG] = "32";
  language_table[FPASLANG] = "31";
  language_table[CSLANG] = "11";

  if (!RE2::PartialMatch(info->GetUsername(), "([0-9]*)", &author_id)) {
      throw Exception("Cannot retrieve author id from username");
  }
}

UralJudger::~UralJudger() {
}

void UralJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nUral");
}

/**
 * Ural doesn't require login, it uses judgeID to identify the submitter
 */
void UralJudger::login() {
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int UralJudger::submit(Bott * bott) {    
    // prepare form for post
  struct curl_httppost * formpost = NULL;
  struct curl_httppost * lastptr = NULL;
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "Action",
               CURLFORM_COPYCONTENTS, "submit",
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "SpaceID",
               CURLFORM_COPYCONTENTS, "1",
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "JudgeID",
               CURLFORM_COPYCONTENTS, info->GetUsername().c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "Language",
               CURLFORM_COPYCONTENTS,
                   convertLanguage(bott->Getlanguage()).c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "ProblemNum",
               CURLFORM_COPYCONTENTS, bott->Getvid().c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "Source",
               CURLFORM_COPYCONTENTS, bott->Getsrc().c_str(),
               CURLFORM_END);


  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.timus.ru/submit.aspx?space=1");
  curl_easy_setopt(curl, CURLOPT_HTTPPOST, formpost);
  performCurl();
  curl_formfree(formpost);

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("<H2 CLASS=\"title\">Solutions judgement results</H2>") ==
      string::npos) {
    return SUBMIT_OTHER_ERROR;
  }

  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * UralJudger::getStatus(Bott * bott) {
  time_t begin_time = time(NULL);

  Bott * result_bott;

  while (true) {
    // check wait time
    if (time(NULL) - begin_time > info->GetMax_wait_time()) {
      throw Exception("Failed to get current result, judge time out.");
    }

    prepareCurl();
    // count can be set to 100 if Ural is very busy
    curl_easy_setopt(
        curl, CURLOPT_URL,
        ("http://acm.timus.ru/status.aspx?author=" + author_id).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(html, "(?s)(<TR class=\"even\">.*?</TR>)",
        &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
        "(?s)<TD class=\"id\"><A.*?>([0-9]*)</A>.*"
        "<TD class=\"verdict.*?>(.*?)</TD>", &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      result = convertResult(result);
      if (result != "Compile Error") {
        // no details for CE results
        if (!RE2::PartialMatch(
            status,
            "<TD class=\"runtime\">(.*?)</TD><TD class=\"memory\">"
                "(.*?) KB</TD>",
            &time_used, &memory_used)) {
          throw Exception("Failed to parse details from status row.");
        }
        int time_ms = stringToDouble(time_used) * 1000 + 0.001;
        time_used = intToString(time_ms);
      } else {
        memory_used = time_used = "0";
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
 * Get compile error info from Ural
 * @param bott      Result bott file
 * @return Compile error info
 */
string UralJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://acm.timus.ru/ce.aspx?id=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  return info;
}

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string UralJudger::convertResult(string result) {
  if (result.find("Compilation error") != string::npos)
    return "Compile Error";
  if (result.find("Time limit exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory limit exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output limit exceeded") != string::npos)
    return "Output Limit Exceed";
  if (result.find("Wrong answer") != string::npos)
    return "Wrong Answer";
  if (result.find("Crash") != string::npos)
    return "Runtime Error";
  if (result.find("Runtime error") != string::npos)
    return "Runtime Error";
  return trim(result);
}
