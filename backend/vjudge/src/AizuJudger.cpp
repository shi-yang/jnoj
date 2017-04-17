/* 
 * File:   AizuJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年8月13日, 下午2:53
 */

#include "AizuJudger.h"

AizuJudger::AizuJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "C++";
  language_table[CLANG]  = "C";
  language_table[JAVALANG]  = "JAVA";
  language_table[CSLANG]  = "C#";
  language_table[PYLANG]  = "Python";
  language_table[RUBYLANG]  = "Ruby";
}

AizuJudger::~AizuJudger() {
}

void AizuJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nAizu");
}

/**
 * Login to Aizu
 */
void AizuJudger::login() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://judge.u-aizu.ac.jp/onlinejudge/index.jsp");
  string post = (string) "loginUserID=" + info->GetUsername() +
      "&loginPassword=" + escapeURL(info->GetPassword()) + "&submit=Sign+in";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  //cout<<ts;
  if (html.find("<span class=\"line\">Login</span>") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int AizuJudger::submit(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://judge.u-aizu.ac.jp/onlinejudge/servlet/Submit");
  string post = (string) "userID=" + escapeURL(info->GetUsername()) +
      "&password=" + escapeURL(info->GetPassword()) +
      "&problemNO=" + bott->Getvid() +
      "&language=" + escapeURL(convertLanguage(bott->Getlanguage())) +
      "&sourceCode=" + escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());

  try {
    performCurl();
  } catch (Exception & e) {
    return SUBMIT_OTHER_ERROR;
  }

  string html = loadAllFromFile(tmpfilename);
  if (html.find("UserID or Password is Wrong.") != string::npos ||
      html.find("<span class=\"line\">Login</span>") != string::npos)
    return SUBMIT_OTHER_ERROR;
  return SUBMIT_NORMAL;
}
/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * AizuJudger::getStatus(Bott * bott) {
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
        ((string) "http://judge.u-aizu.ac.jp/onlinejudge/status.jsp").c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row of current user
    if (!RE2::PartialMatch(
        html,
        "(?s)(<tr *class=\"dat\".*?>" + info->GetUsername() + "<.*?</tr>)",
        &status)) {
      throw Exception("Failed to get status row.");
    }
    html = status;
    if (!RE2::PartialMatch(
        html,
        "(?s).*(<tr *class=\"dat\".*?>" + info->GetUsername() + "<.*?</tr>)",
        &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(
        status, "(?s)rid=([0-9]*).*status.*?<a href=\"review.*?>(.*?)</a>",
        &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = convertResult(trim(result));
    if (isFinalResult(result)) {
      // result is the final one, get details
      if (result != "Runtime Error" && result != "Compile Error") {
        if (!RE2::PartialMatch(
            status,
            "(?s)<td class=\"text-center\">([0-9\\.]*) s.*?<td.*?>([0-9]*) KB",
            &time_used, &memory_used)) {
          throw Exception("Failed to parse details from status row.");
        }
      } else {
        memory_used = time_used = "0";
      }
      result_bott = new Bott;
      result_bott->Settype(RESULT_REPORT);
      result_bott->Setresult(convertResult(result));
      result_bott->Settime_used(
          (int)(stringToDouble(time_used) * 100 + 0.1));
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
string AizuJudger::convertResult(string result) {
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output Limit Exceeded") != string::npos)
    return "Output Limit Exceed";
  return trim(result);
}

/**
 * Get compile error info
 * @param bott      Result bott file
 * @return Compile error info
 */
string AizuJudger::getCEinfo(Bott * bott) {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
      ((string) "http://judge.u-aizu.ac.jp/onlinejudge/compile_log.jsp?runID=" +
      bott->Getremote_runid()).c_str());
  performCurl();

  // Aizu is in SHIFT_JIS charset
  string info = charsetConvert("SHIFT_JIS", "UTF-8",
      loadAllFromFile(tmpfilename));
  string result;
  char * buffer = new char[info.length() * 2];

  if (!RE2::PartialMatch(info,
                         "(?s)<p style=\"font-size:11pt;\">(.*)</p>",
                         &result)) {
    return "";
  }

  strcpy(buffer, result.c_str());
  decode_html_entities_utf8(buffer, NULL);
  result = buffer;
  delete [] buffer;

  return trim(result);
}
