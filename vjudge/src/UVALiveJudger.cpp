/* 
 * File:   UVALiveJudger.cpp
 * Author: payper
 * 
 * Created on 2014年3月24日, 下午3:31
 */

#include "UVALiveJudger.h"

/**
 * Create a UVALive Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
UVALiveJudger::UVALiveJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "3";
  language_table[CLANG]  = "1";
  language_table[JAVALANG]  = "2";
  language_table[FPASLANG]  = "4";
}

UVALiveJudger::~UVALiveJudger() {
}

void UVALiveJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nUVALive");
}

/**
 * Get input=hidden stuffs for login
 * @param url
 * @return 
 */
string UVALiveJudger::getLoginHiddenParams() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "https://icpcarchive.ecs.baylor.edu/index.php");
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  string form;
  // login form
  if (!RE2::PartialMatch(html, "(?s).*(<form.*?mod_loginform.*?</form>)",
                         &form)) {
    throw Exception("Failed to get hidden params.");
  }
  string key, value, result = "";
  // get all hidden params
  re2::StringPiece formString(form);
  while (RE2::FindAndConsume(
      &formString, "(?s)<input type=\"hidden\" name=\"(.*?)\" value=\"(.*?)\"",
      &key, &value)) {
    result += escapeURL(key) + "=" + escapeURL(value) + "&";
  }
  return result;
}

/**
 * Login to UVALive
 */
void UVALiveJudger::login() {
  string hiddenParams = getLoginHiddenParams();

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_REFERER,
                   "https://livearchive.onlinejudge.org/");
  curl_easy_setopt(
      curl, CURLOPT_URL,
      "https://icpcarchive.ecs.baylor.edu/index.php?option="
          "com_comprofiler&task=login");
  string post = hiddenParams + "username=" + escapeURL(info->GetUsername()) +
      "&passwd=" + escapeURL(info->GetPassword()) +
      "&remember=yes&Submit=Login";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("alert(\"") != string::npos ||
      html.find("<div class='error'>") != string::npos ||
      html.find("You are not authorized to view this page!") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int UVALiveJudger::submit(Bott * bott) {

  // prepare form for post
  struct curl_httppost * formpost = NULL;
  struct curl_httppost * lastptr = NULL;
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "localid",
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
  curl_easy_setopt(
      curl, CURLOPT_URL,
      "https://icpcarchive.ecs.baylor.edu/index.php?option="
          "com_onlinejudge&Itemid=25&page=save_submission");
  curl_easy_setopt(curl, CURLOPT_HTTPPOST, formpost);
  performCurl();
  curl_formfree(formpost);

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("Submission+received+with+ID") == string::npos)
    return SUBMIT_OTHER_ERROR;
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * UVALiveJudger::getStatus(Bott * bott) {
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
        "https://icpcarchive.ecs.baylor.edu/index.php?option="
            "com_onlinejudge&Itemid=9");
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (html.find("<b>One or more following ERROR(s) occurred.") != string::npos ||
        html.find("The page is temporarily unavailable") != string::npos ||
        !RE2::PartialMatch(html,
                           "(?s)(<tr class=\"sectiontableentry1\">.*?</tr>)",
                           &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
                           "(?s)<td>([0-9]*?)</td>.*?<td>.*?<td>(.*?)</td>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // if result if final, get details
      if (!RE2::PartialMatch(
          status,
          "(?s)<td>([0-9]*?)</td>.*?<td>.*?<td>(.*?)</td>"
              ".*?<td>.*?<td>(.*?)</td>.*?<td>(.*?)</td>",
          &runid, &result, &time_used, &memory_used)) {
        throw Exception("Failed to parse details from status row.");
      }
      int time_ms = stringToDouble(time_used) * 1000 + 0.001;
      result_bott = new Bott;
      result_bott->Settype(RESULT_REPORT);
      result_bott->Setresult(convertResult(result));
      result_bott->Settime_used(time_ms);
      result_bott->Setmemory_used(stringToInt(memory_used));
      result_bott->Setremote_runid(trim(runid));
      break;
    }
  }
  return result_bott;
}

/**
 * Get compile error info from UVALive
 * @param bott      Result bott file
 * @return Compile error info
 */
string UVALiveJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "https://icpcarchive.ecs.baylor.edu/index.php?option="
          "com_onlinejudge&Itemid=9&page=show_compilationerror&submission=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info, "(?s)<pre>(.*)</pre>", &result)) {
    return "";
  }
  return result;
}

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string UVALiveJudger::convertResult(string result) {
  result = capitalize(result);
  if (result.find("Compilation Error") != string::npos)
    return "Compile Error";
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output Limit Exceeded") != string::npos)
    return "Output Limit Exceed";
  if (result.find("Presentation error") != string::npos)
    return "Presentation Error";
  if (result.find("System Error") != string::npos)
    return "Judge Error";
  if (result.find("Submission Error") != string::npos)
    return "Judge Error";
  return trim(result);
}
