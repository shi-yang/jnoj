/* 
 * File:   HDUJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年2月4日, 下午6:07
 */

#include "HDUJudger.h"

HDUJudger::HDUJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "0";
  language_table[CLANG]  = "1";
  language_table[JAVALANG]  = "5";
  language_table[FPASLANG]  = "4";
  language_table[VCLANG] = "2";
  language_table[VCPPLANG] = "3";
}

HDUJudger::~HDUJudger() {
}

void HDUJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nHDU");
}

/**
 * Login to HDU
 */
void HDUJudger::login() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.hdu.edu.cn/userloginex.php?action=login");
  string post = (string) "username=" + info->GetUsername() + "&userpass=" +
      info->GetPassword() + "&login=Sign+In";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  //cout<<ts;
  if (html.find("No such user or wrong password.") != string::npos ||
      html.find("<b>One or more following ERROR(s) occurred.") !=
          string::npos ||
      html.find("<h2>The requested URL could not be retrieved</h2>") !=
          string::npos ||
      html.find("<H1 style=\"COLOR: #1A5CC8\" align=center>"
          "Sign In Your Account</H1>") != string::npos ||
      html.find("PHP: Maximum execution time of") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int HDUJudger::submit(Bott * bott) {
  // Convert code to GBK, some output in HDU contains GB18030 characters.
  // Example: HDU2815
  string converted_code = charsetConvert("UTF-8", "GB18030", bott->Getsrc());

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.hdu.edu.cn/submit.php?action=submit");
  string post = (string) "check=0&problemid=" + bott->Getvid() +
      "&language=" + convertLanguage(bott->Getlanguage()) +
      "&usercode=" + escapeURL(converted_code);
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  if (html.find("Connect(0) to MySQL Server failed.") != string::npos ||
      html.find("<b>One or more following ERROR(s) occurred.") !=
          string::npos ||
      html.find("<h2>The requested URL could not be retrieved</h2>") !=
          string::npos ||
      html.find("<H1 style=\"COLOR: #1A5CC8\" align=center>"
          "Sign In Your Account</H1>") != string::npos ||
      html.find("PHP: Maximum execution time of") != string::npos ||
      html.find("<DIV>Exercise Is Closed Now!</DIV>") != string::npos)
    return SUBMIT_OTHER_ERROR;
  return SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * HDUJudger::getStatus(Bott * bott) {
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
        ((string) "http://acm.hdu.edu.cn/status.php?first=&pid=" +
            bott->Getvid() + "&user=" + info->GetUsername() +
            "&lang=&status=0").c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (html.find("Connect(0) to MySQL Server failed.") != string::npos ||
        html.find("<b>One or more following ERROR(s) occurred.") !=
            string::npos ||
        html.find("<h2>The requested URL could not be retrieved</h2>") !=
            string::npos ||
        html.find("PHP: Maximum execution time of") != string::npos ||
        html.find("<H1 style=\"COLOR: #1A5CC8\" align=center>"
            "Sign In Your Account</H1>") != string::npos ||
        html.find("<DIV>Exercise Is Closed Now!</DIV>") != string::npos ||
        !RE2::PartialMatch(html, "(?s)<table.*?(<tr align=center.*?</tr>)",
                           &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
                           "(?s)<td.*?>([0-9]*)</td>.*?<font.*?>(.*)</font>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one, get details
      if (!RE2::PartialMatch(status,
                             "(?s)([0-9]*)MS.*?([0-9]*)K",
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
 * Convert result text to local ones, keep consistency
 * @param result Original result
 * @return Converted local result
 */
string HDUJudger::convertResult(string result) {
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output Limit Exceeded") != string::npos)
    return "Output Limit Exceed";
  if (result.find("Compilation Error") != string::npos)
    return "Compile Error";
  if (result.find("Runtime Error") != string::npos)
    return "Runtime Error";
  return trim(result);
}

/**
 * Get compile error info
 * @param bott      Result bott file
 * @return Compile error info
 */
string HDUJudger::getCEinfo(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://acm.hdu.edu.cn/viewerror.php?rid=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  // HDU is in GBK charset
  string info = charsetConvert("GBK", "UTF-8", loadAllFromFile(tmpfilename));
  string result;
  char * buffer = new char[info.length() * 2];

  if (!RE2::PartialMatch(info, "(?s)<pre>(.*?)</pre>", &result)) {
    return "";
  }

  strcpy(buffer, result.c_str());
  decode_html_entities_utf8(buffer, NULL);
  result = buffer;
  delete [] buffer;

  return result;
}
