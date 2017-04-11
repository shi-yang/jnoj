/*
 * File:   CCJudger.cpp
 * Author: 51isoft
 *
 * Created on 2014年8月20日, 下午3:06
 */

#include "CCJudger.h"

/**
 * Create a CodeChef Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
CCJudger::CCJudger(JudgerInfo * _info) : VirtualJudger(_info) {
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

CCJudger::~CCJudger() {
}

void CCJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nCodeChef");
}

/**
 * Get input=hidden stuffs for login
 * @return Hidden params in string
 */
string CCJudger::getLoginHiddenParams() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "https://www.codechef.com/");
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  string form;
  // login form
  if (!RE2::PartialMatch(html,
                         "(?s)(<form.*?user-login-form.*?</form>)",
                         &form)) {
    throw Exception("Failed to get hidden params.");
  }
  string key, value, result = "";
  // get all hidden params
  re2::StringPiece formString(form);
  while (RE2::FindAndConsume(
      &formString,
      "(?s)<input type=\"hidden\".*?name=\"(.*?)\".*?value=\"(.*?)\"", &key,
      &value)) {
    result += escapeURL(key) + "=" + escapeURL(value) + "&";
  }
  return result;
}

/**
 * Login to CodeChef
 */
void CCJudger::login() {
  string hiddenParams = getLoginHiddenParams();

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "https://www.codechef.com/node?destination=node");
  string post = hiddenParams + "name=" + escapeURL(info->GetUsername()) +
      "&pass=" + escapeURL(info->GetPassword()) + "&submit.x=0&submit.y=0";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("<a class=\"login-link\"") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Get input=hidden stuffs for submit
 * @param code  Problem code
 * @return Hidden params in pairs
 */
vector< pair<string, string> > CCJudger::getSubmitHiddenParams(string code) {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   ((string) "https://www.codechef.com/submit/" + code).c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  string form;
  // login form
  if (!RE2::PartialMatch(html, "(?s)(<form.*?submit-form.*?</form>)", &form)) {
    throw Exception("Failed to get hidden params.");
  }
  string key, value;
  vector< pair<string, string> > result;
  // get all hidden params
  re2::StringPiece formString(form);
  while (RE2::FindAndConsume(
      &formString,
      "(?s)<input type=\"hidden\".*?name=\"(.*?)\".*?value=\"(.*?)\"",
      &key, &value)) {
    result.push_back(make_pair(key, value));
  }
  return result;
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int CCJudger::submit(Bott * bott) {
  vector< pair<string, string> > hiddenParams;
  try {
    hiddenParams = getSubmitHiddenParams(bott->Getvid());
  } catch (Exception & e) {
    // mostly because frequecy limit
    return SUBMIT_OTHER_ERROR;
  }

  // prepare form for post
  struct curl_httppost * formpost = NULL;
  struct curl_httppost * lastptr = NULL;

  for (vector< pair<string, string> >::iterator it = hiddenParams.begin();
      it != hiddenParams.end(); ++it) {
    curl_formadd(&formpost, &lastptr,
                 CURLFORM_COPYNAME, it->first.c_str(),
                 CURLFORM_COPYCONTENTS, it->second.c_str(),
                 CURLFORM_END);
  }
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "language",
               CURLFORM_COPYCONTENTS,
                   convertLanguage(bott->Getlanguage()).c_str(),
               CURLFORM_END);
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "program",
               CURLFORM_COPYCONTENTS, bott->Getsrc().c_str(),
               CURLFORM_END);

  // WTF moment... CodeChef checks the filename of the uploaded file to
  // determine whether to read source from text area or uploaded file, so we
  // must upload something, otherwise it'll stop you from submitting...
  curl_formadd(&formpost, &lastptr,
               CURLFORM_COPYNAME, "files[program_file]",
               CURLFORM_FILE, "/dev/null",
               CURLFORM_FILENAME, "",
               CURLFORM_END);

  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "https://www.codechef.com/submit/" + bott->Getvid()).c_str());
  curl_easy_setopt(curl, CURLOPT_HTTPPOST, formpost);
  performCurl();
  curl_formfree(formpost);

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find(
      "<li>You can\'t submit in this language for this problem!</li>") !=
      string::npos) {
    return SUBMIT_INVALID_LANGUAGE;
  }
  if (html.find("<div class=\"messages error\">") != string::npos ||
      html.find("<a class=\"login-link\"") != string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  // parse remote runid from submit page
  string runid;
  if (!RE2::PartialMatch(html, "(?s)var submission_id = (.*?);", &runid)) {
    return SUBMIT_OTHER_ERROR;
  }
  bott->Setremote_runid(runid);
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * CCJudger::getStatus(Bott * bott) {
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
        ((string) "https://www.codechef.com/get_submission_status/" +
            bott->Getremote_runid()).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string result, time_used, memory_used;

    // get result
    if (!RE2::PartialMatch(html, "\"result_code\":\"(.*?)\"", &result)) {
      throw Exception("Failed to get current result.");
    }
    result = convertResult(result);
    if (isFinalResult(result)) {
      // if result if final, get details
      if (!RE2::PartialMatch(html,
                             "(?s)\"time\":\"(.*?)\"",
                             &time_used)) {
        throw Exception("Failed to parse details from status row.");
      }
      int time_ms = stringToDouble(time_used) * 1000 + 0.001;
      int memory_kb;
      result_bott = new Bott;
      result_bott->Settype(RESULT_REPORT);
      result_bott->Setresult(result);
      result_bott->Settime_used(time_ms);

      if (result != "Compile Error") {
        // CodeChef will update Memory usage later in submission table
        // Weird... why don't they put in get_submission_status api...
        memory_used = "0";
        prepareCurl();
        curl_easy_setopt(
            curl, CURLOPT_URL,
            ((string) "https://www.codechef.com/submissions?handle=" +
                escapeURL(info->GetUsername()) + "&pcode=" +
                escapeURL(bott->Getvid())).c_str());
        performCurl();
        html = loadAllFromFile(tmpfilename);
        string status;
        if (!RE2::PartialMatch(
            html, "(?s)(<tr class=\"kol.*?<td width=\"60\">" +
                bott->Getremote_runid() + "</td>.*?</tr>)", &status)) {
          // Status row is not updated in time...
          log("Memory data not ready yet... Never mind, use 0 instead.");
          memory_used = "0";
        } else if (!RE2::PartialMatch(status, ">([0-9\\.]*)M", &memory_used)) {
          memory_used = "0";
        }
        memory_kb = stringToDouble(memory_used) * 1024 + 0.001;
      }
      result_bott->Setmemory_used(memory_kb);
      result_bott->Setremote_runid(bott->Getremote_runid());
      break;
    }
  }
  return result_bott;
}

/**
 * Get compile error info from CodeChef
 * @param bott      Result bott file
 * @return Compile error info
 */
string CCJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   ((string) "https://www.codechef.com/view/error/" +
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

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string CCJudger::convertResult(string result) {
  if (result.find("wait") != string::npos) return "Running";
  if (result.find("wrong") != string::npos) return "Wrong Answer";
  if (result.find("time") != string::npos) return "Time Limit Exceed";
  if (result.find("runtime") != string::npos) return "Runtime Error";
  if (result.find("compile") != string::npos) return "Compile Error";
  if (result.find("accepted") != string::npos) return "Accepted";
  return "Judge Error";
}
