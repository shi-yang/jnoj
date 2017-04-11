/* 
 * File:   LOJJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月25日, 下午12:50
 */

#include "LOJJudger.h"

/**
 * Create a LOJ Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
LOJJudger::LOJJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "C++";
  language_table[CLANG] = "C";
  language_table[JAVALANG] = "JAVA";
  language_table[FPASLANG] = "PASCAL";
  language_table[PYLANG] = "PYTHON";
}

LOJJudger::~LOJJudger() {
}

void LOJJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nLightOJ");
}

/**
 * Login to LOJ
 */
void LOJJudger::login() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://www.lightoj.com/login_check.php");
  string post = "myuserid=" + escapeURL(info->GetUsername()) +
      "&mypassword=" + escapeURL(info->GetPassword()) + "&Submit=Login";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("login_main.php") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int LOJJudger::submit(Bott * bott) {

  // prepare form for post
  struct curl_httppost * formpost = NULL;
  struct curl_httppost * lastptr = NULL;
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "sub_problem",
               CURLFORM_COPYCONTENTS, bott->Getvid().c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "language",
               CURLFORM_COPYCONTENTS,
                   convertLanguage(bott->Getlanguage()).c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "code",
               CURLFORM_COPYCONTENTS, bott->Getsrc().c_str(),
               CURLFORM_END);

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://www.lightoj.com/volume_submit.php");
  curl_easy_setopt(curl, CURLOPT_HTTPPOST, formpost);
  performCurl();
  curl_formfree(formpost);

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find(
      "<script>location.href='volume_usersubmissions.php'</script>") ==
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
Bott * LOJJudger::getStatus(Bott * bott) {
  time_t begin_time = time(NULL);

  Bott * result_bott;
  while (true) {
    // check wait time
    if (time(NULL) - begin_time > info->GetMax_wait_time()) {
      throw Exception("Failed to get current result, judge time out.");
    }

    prepareCurl();
    curl_easy_setopt(curl, CURLOPT_URL,
                     "http://www.lightoj.com/volume_usersubmissions.php");
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(html, "(?s)(<tr class=\"newone\">.*?</tr>)",
                           &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status, "(?s)sub_id=([0-9]*).*?<div.*?>(.*?)</div>",
                           &runid, &result)) {
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
            "(?s)sub_id=([0-9]*).*?<td.*?<td.*?<td.*?<td.*?>(.*?)</td"
                ".*?<td.*?>(.*?)</td>",
            &runid, &time_used, &memory_used)) {
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
 * Get compile error info from LOJ
 * @param bott      Result bott file
 * @return Compile error info
 */
string LOJJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string)"http://www.lightoj.com/volume_showcode.php?sub_id=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info, "(?s)<textarea.*?>(.*?)</textarea>", &result)) {
    return "";
  }
  return result;
}

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string LOJJudger::convertResult(string result) {
  if (result.find("Compilation Error") != string::npos)
    return "Compile Error";
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output Limit Exceeded") != string::npos)
    return "Output Limit Exceed";
  return trim(result);
}
