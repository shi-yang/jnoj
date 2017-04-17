#include "UESTCJudger.h"

/**
 * Create a UESTC Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
UESTCJudger::UESTCJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG]  = "2";
  language_table[CLANG]  = "1";
  language_table[JAVALANG]  = "3";
}

UESTCJudger::~UESTCJudger() {
}

void UESTCJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nUESTC");
}

/**
 * Custom verison of prepareCurl, add Accept and Content-Type in header
 */
void UESTCJudger::prepareCurl() {
  struct curl_slist *header = NULL;
  header = curl_slist_append(header, "application/json, text/plain, */*");
  header = curl_slist_append(header,
      "Content-Type:application/json;charset=UTF-8");
  VirtualJudger::prepareCurl();
  curl_easy_setopt(curl, CURLOPT_HTTPHEADER, header);
}

/**
 * Login to UESTC
 */
void UESTCJudger::login() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL, "http://acm.uestc.edu.cn/user/login");
  string post = (string) "{\"userName\":\"" + info->GetUsername() + "\"," +
      "\"password\":\"" + sha1String(info->GetPassword()) + "\"}";
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  if (html.find("\"result\":\"success\"") == string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int UESTCJudger::submit(Bott * bott) {
  string post = (string) "{\"codeContent\":\"" + escapeString(bott->Getsrc()) +
      "\",\"problemId\":" + bott->Getvid() + ",\"contestId\":null," +
      "\"languageId\":" + convertLanguage(bott->Getlanguage()) + "}";
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
      "http://acm.uestc.edu.cn/status/submit");
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  string html = loadAllFromFile(tmpfilename);
  if (html.find("\"result\":\"success\"") == string::npos)
    return SUBMIT_OTHER_ERROR;
  return SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * UESTCJudger::getStatus(Bott * bott) {
  time_t begin_time = time(NULL);
  string post = (string) "{\"currentPage\":1,\"userName\":\"" + 
      info->GetUsername() + "\",\"problemId\":" + bott->Getvid() + 
      ",\"contestId\":-1,\"result\":0,\"orderFields\":\"statusId\",\
      \"orderAsc\":\"false\"}";

  Bott * result_bott;
  while (true) {
    usleep(500000);
    // check wait time
    if (time(NULL) - begin_time > info->GetMax_wait_time()) {
      throw Exception("Failed to get current result, judge time out.");
    }

    prepareCurl();
    curl_easy_setopt(curl, CURLOPT_URL,
        "http://acm.uestc.edu.cn/status/search");
    curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
    performCurl();

    string json = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (json.find("\"result\":\"success\"") == string::npos ||
        !RE2::PartialMatch(json, "\"list\":\\[\\{(.*?)\\}", &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status,
        "\"returnType\":\"(.*?)\".*\"statusId\":([0-9]+)",
        &result, &runid)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      if (result == "Accepted") {
        // only accepted run has details
        if (!RE2::PartialMatch(status,
            "\"memoryCost\":([0-9]+).*\"timeCost\":([0-9]+)", &memory_used,
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
string UESTCJudger::convertResult(string result) {
  if (result.find("Time Limit Exceeded") != string::npos)
    return "Time Limit Exceed";
  if (result.find("Memory Limit Exceeded") != string::npos)
    return "Memory Limit Exceed";
  if (result.find("Output Limit Exceeded") != string::npos)
    return "Output Limit Exceed";
  if (result.find("System Error") != string::npos)
    return "Judge Error";
  if (result.find("Compilation Error") != string::npos)
    return "Compile Error";
  // It contains testcase number for the following result
  if (result.find("Wrong Answer") != string::npos)
    return "Wrong Answer";
  if (result.find("Restricted Function") != string::npos)
    return "Restricted Function";
  if (result.find("Presentation Error") != string::npos)
    return "Presentation Error";
  if (result.find("Runtime Error") != string::npos)
    return "Runtime Error";
  return trim(result);
}

/**
 * Get compile error info
 * @param bott      Result bott file
 * @return Compile error info
 */
string UESTCJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://acm.uestc.edu.cn/status/info/" +
          bott->Getremote_runid()).c_str());
  performCurl();

  string info = loadAllFromFile(tmpfilename);
  string result;
  if (!RE2::PartialMatch(info,
      "\"compileInfo\":\"(.*)\",\"result\":\"success\"}", &result)) {
    return "";
  }

  return unescapeString(result);
}
