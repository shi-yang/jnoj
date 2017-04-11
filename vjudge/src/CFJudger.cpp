/* 
 * File:   CFJudger.cpp
 * Author: 51isoft
 * 
 * Created on 2014年2月1日, 上午12:02
 */

#include "CFJudger.h"

/**
 * Create a CF Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
CFJudger::CFJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "1";
  language_table[CLANG]  = "10";
  language_table[JAVALANG]  = "23";
  language_table[FPASLANG]  = "4";
  language_table[PYLANG]  = "7";
  language_table[CSLANG]  = "9";
  language_table[RUBYLANG]  = "8";
  language_table[VCLANG] = "2";
}

CFJudger::~CFJudger() {
}

void CFJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nCodeForces");
}

/**
 * Get Csrf token
 * @param url   URL you need to get csrf token
 * @return Csrf token for current session
 */
string CFJudger::getCsrfParams(string url) {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
  performCurl();

  // Format:
  // <meta name="X-Csrf-Token" content="21d5gfa7bceebhe5f45d6f5eb7930ea7"/>
  string html = loadAllFromFile(tmpfilename);
  string csrf;
  if (!RE2::PartialMatch(html, "X-Csrf-Token.*content=\"(.*?)\"", &csrf)) {
    throw Exception("Failed to get Csrf token.");
  }
  return csrf;
}

/**
 * Copied from CF's js
 */
int CFJudger::calculatetta(string a) {
  int b = 0;
  for (int c = 0; c < a.length(); ++c) {
    b = (b + (c + 1) * (c + 2) * a[c]) % 1009;
    if (c % 3 == 0) ++b;
    if (c % 2 == 0) b *= 2;
    if (c > 0) b -= ((int) (a[c / 2] / 2)) * (b % 5);
    while (b < 0) b += 1009;
    while (b >= 1009) b -= 1009;
  }
  return b;
}

/**
 * CF needs a _tta for every request, calculated according to _COOKIE[39ce7]
 * @return _tta
 */
string CFJudger::getttaValue() {
  string cookies = loadAllFromFile(cookiefilename);
  string magic;

  if (!RE2::PartialMatch(cookies, "39ce7\\t(.*)", &magic)) {
    throw Exception("Failed to get magic cookie for tta.");
  }
  string tta = intToString(calculatetta(magic));
  log("tta Value: " + tta);
  return tta;
}

/**
 * Login to CodeForces
 */
void CFJudger::login() {
  string csrf = getCsrfParams("http://codeforces.com/enter");

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_REFERER, "http://codeforces.com/enter");
  curl_easy_setopt(curl, CURLOPT_URL, "http://codeforces.com/enter");
  string post = (string) "csrf_token=" + csrf +
      "&action=enter&handle=" + info->GetUsername() +
      "&password=" + info->GetPassword() +
      "&_tta=" + getttaValue();
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("Invalid handle or password") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Get submit url, extracted from submit for reuse
 * @param contest   Contest ID
 * @return Submit url
 */
string CFJudger::getSubmitUrl(string contest){
  return "http://codeforces.com/problemset/submit";
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int CFJudger::submit(Bott * bott) {
  string csrf = getCsrfParams("http://codeforces.com/problemset/submit");

  // prepare cid and pid from vid
  string contest, problem;
  if (!RE2::PartialMatch(bott->Getvid(), "^([0-9]{1,6})(.*)",
      &contest, &problem)) {
    throw Exception("Invalid vid.");
  }
  string source = bott->Getsrc();
  // add random extra spaces in the end to avoid same code error
  srand(time(NULL));
  source += '\n';
  while (rand() % 120) source += ' ';

  // prepare form for post
  struct curl_httppost * formpost = NULL;
  struct curl_httppost * lastptr = NULL;
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "action",
               CURLFORM_COPYCONTENTS, "submitSolutionFormSubmitted",
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "contestId",
               CURLFORM_COPYCONTENTS, contest.c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "submittedProblemIndex",
               CURLFORM_COPYCONTENTS, problem.c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "programTypeId",
               CURLFORM_COPYCONTENTS,
                   convertLanguage(bott->Getlanguage()).c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "source",
               CURLFORM_COPYCONTENTS, source.c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "sourceCodeConfirmed",
               CURLFORM_COPYCONTENTS, "true",
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "_tta",
               CURLFORM_COPYCONTENTS, getttaValue().c_str(),
               CURLFORM_END);

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, (getSubmitUrl(contest) + "?csrf_token=" +
      csrf).c_str());
  curl_easy_setopt(curl, CURLOPT_HTTPPOST, formpost);
  performCurl();
  curl_formfree(formpost);

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find(
      "You have submitted exactly the same code before") != string::npos) {
    return VirtualJudger::SUBMIT_SAME_CODE;
  } else if (html.find("Choose valid language") != string::npos) {
    return VirtualJudger::SUBMIT_INVALID_LANGUAGE;
  } else if (html.find("<span class=\"error for__source\">") != string::npos) {
    return VirtualJudger::SUBMIT_COMPILE_ERROR;
  } else if (html.find("<a href=\"/enter\">Enter</a>") != string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * CFJudger::getStatus(Bott * bott) {
  time_t begin_time = time(NULL);

  Bott * result_bott;
  while (true) {
    // check wait time
    if (time(NULL) - begin_time > info->GetMax_wait_time()) {
      throw Exception("Failed to get current result, judge time out.");
    }

    prepareCurl();
    curl_easy_setopt(curl, CURLOPT_URL,
                     ((string) "http://codeforces.com/submissions/" +
                        info->GetUsername()).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;
    // get first result row
    if (!RE2::PartialMatch(html,
                           "(?s)<tr.*first-row.*?(<tr.*?</tr>)", &status)) {
      throw Exception("Failed to get status row.");
    }
    // get result
    if (!RE2::PartialMatch(status, "(?s)status-cell.*?>(.*?)</td>", &result)) {
      throw Exception("Failed to get current result.");
    }

    if (isFinalResult(result)) {
      // if result if final, get details
      if (!RE2::PartialMatch(
          status, "(?s)data-submission-id=\"([0-9]*)\".*submissionVerdict"
              "=\"(.*?)\".*time-consumed-cell.*?>(.*?)&nbsp;ms.*memory-consumed"
              "-cell.*?>(.*?)&nbsp;KB",
          &runid, &result, &time_used,
          &memory_used)) {
        // try api when failed
        log("Failed to parse details from status row, try API.");
        prepareCurl();
        curl_easy_setopt(
            curl, CURLOPT_URL,
            ((string) "http://codeforces.com/api/user.status?handle=" +
                info->GetUsername() + "&from=1&count=1").c_str());
        performCurl();

        string json = loadAllFromFile(tmpfilename);
        if (!RE2::PartialMatch(
            json,
            "(?s)\"id\":([0-9]*)\"verdict\":\"(.*?)\".*\"timeConsumed"
                "Millis\":([0-9]*),\"memeryConsumedBytes\":([0-9]*)",
            &runid, &result, &time_used, &memory_used)) {
          throw Exception("Failed to parse details from API.");
        }
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

  // for CodeForces, we can store extra infos in ce_info column
  string contest;
  // no need to check fail or not, since submit function has already done it
  RE2::PartialMatch(bott->Getvid(), "(^[0-9]{1,6})", &contest);
  if (result_bott->Getresult() != "Accepted" &&
      result_bott->Getresult() != "Compile Error") {
    result_bott->Setce_info(
        getVerdict(contest, result_bott->Getremote_runid()));
  }
  return result_bott;
}

/**
 * Convert CF result text to local ones, keep consistency
 * @param result Original result
 * @return Converted local result
 */
string CFJudger::convertResult(string result) {
  if (result.find("OK") != string::npos)
    return "Accepted";
  if (result.find("COMPILATION_ERROR") != string::npos)
    return "Compile Error";
  if (result.find("WRONG_ANSWER") != string::npos)
    return "Wrong Answer";
  if (result.find("RUNTIME_ERROR") != string::npos)
    return "Runtime Error";
  if (result.find("TIME_LIMIT_EXCEEDED") != string::npos)
    return "Time Limit Exceed";
  if (result.find("IDLENESS_LIMIT_EXCEEDED") != string::npos ||
      result.find("MEMORY_LIMIT_EXCEEDED") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("CRASHED") != string::npos ||
      result.find("FAILED") != string::npos)
    return "Judge Error";
  return trim(result);
}

/**
 * Get compile error info from CodeForces
 * @param bott      Result bott file
 * @return Compile error info
 */
string CFJudger::getCEinfo(Bott * bott) {
  string csrf = getCsrfParams("http://codeforces.com/problemset/submit");

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://codeforces.com/data/judgeProtocol");
  string post = (string) "submissionId=" + bott->Getremote_runid() +
      "&csrf_token=" + csrf;
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::FullMatch(info, "\"(.*)\"", &result)) {
    return "";
  }
  return unescapeString(result);
}

/**
 * Get url for the verdict, extracted from getVerdict() for reuse
 * @param contest       Contest ID
 * @param runid         Remote runid
 * @return Verdict url
 */
string CFJudger::getVerdictUrl(string contest, string runid) {
  return "http://codeforces.com/contest/" + contest + "/submission/" + runid;
}

/**
 * Get run details from Codeforces
 * @param contest       Contest ID
 * @param runid         Remote runid
 * @return Verdict details
 */
string CFJudger::getVerdict(string contest, string runid) {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, getVerdictUrl(contest, runid).c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  htmlcxx::HTML::ParserDom parser;
  tree<htmlcxx::HTML::Node> dom = parser.parseTree(html);
  hcxselect::Selector selector(dom);

  // load all roundbox in verdict page
  try {
    selector = selector.select("#content .roundbox");
  } catch (...) {
    log("Parse verdict error, use empty result instead.");
    return "";
  }

  // find the one contains error message
  for (hcxselect::Selector::const_iterator it = selector.begin();
      it != selector.end(); ++it) {
    string content = html.substr((*it)->data.offset(), (*it)->data.length());
    if (content.find("<div  class=\"error\">") != string::npos) return content;
  }

  return "";
}
