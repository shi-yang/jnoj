/*
 * File:   SCUJudger.cpp
 * Author: payper
 *
 * Created on 2014年3月26日, 下午1:03
 */

#include "SCUJudger.h"
#include <jpeglib.h>

/**
 * Create a SCU Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
SCUJudger::SCUJudger(JudgerInfo * _info) : VirtualJudger(_info) {
  language_table[CPPLANG] = "C++ (G++-3)";
  language_table[CLANG] = "C (GCC-3)";
  language_table[JAVALANG] = "JAVA";
  language_table[FPASLANG] = "PASCAL (GPC)";
}

SCUJudger::~SCUJudger() {
}

void SCUJudger::initHandShake(){
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nSCU");
}

/**
 * Login to SCU
 */
void SCUJudger::login() {

  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.scu.edu.cn/soj/login.action");
  string post = "back=2&submit=login&id=" + escapeURL(info->GetUsername()) +
      "&password=" + escapeURL(info->GetPassword());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check login status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("<title>ERROR</title>") != string::npos) {
    throw Exception("Login failed!");
  }
}

/**
 * Submit a run
 * @param bott      Bott file for Run info
 * @return Submit status
 */
int SCUJudger::submit(Bott * bott) {
  string code = getCode();
  log("Validation Code: " + code);
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.scu.edu.cn/soj/submit.action");
  string post = (string) "problemId=" + bott->Getvid() +
      "&submit=Submit&validation=" + code +
      "&language=" + convertLanguage(bott->Getlanguage()) +
      "&source=" + escapeURL(bott->Getsrc());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post.c_str());
  performCurl();

  // check submit status
  string html = loadAllFromFile(tmpfilename);
  if (html.find("<title>ERROR</title>") != string::npos ||
      html.find("The page is temporarily unavailable") != string::npos) {
    return SUBMIT_OTHER_ERROR;
  }
  return VirtualJudger::SUBMIT_NORMAL;
}

/**
 * Get result and related info
 * @param bott  Original Bott info
 * @return Result Bott file
 */
Bott * SCUJudger::getStatus(Bott * bott) {
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
        ((string) "http://acm.scu.edu.cn/soj/solutions.action?userId=" +
            escapeURL(info->GetUsername()) + "&problemId=" +
            bott->Getvid()).c_str());
    performCurl();

    string html = loadAllFromFile(tmpfilename);
    string status;
    string runid, result, time_used, memory_used;

    // get first row
    if (!RE2::PartialMatch(
        html, "(?s).*<table.*?<tr.*?(<tr.*?<td height=\"44\">.*?</tr>)",
        &status)) {
      throw Exception("Failed to get status row.");
    }

    // get result
    if (!RE2::PartialMatch(status, "(?s)<td.*?>([0-9]*).*?<font.*?>(.*)</font>",
                           &runid, &result)) {
      throw Exception("Failed to get current result.");
    }
    result = trim(result);
    if (isFinalResult(result)) {
      // result is the final one
      result = convertResult(result);
      if (!RE2::PartialMatch(
          status,
          "(?s)<td.*?>([0-9]*).*?<font.*?>.*</font>.*?"
              "<td>(.*?)</td>.*?<td>(.*?)</td>",
          &runid, &time_used, &memory_used)) {
        throw Exception("Failed to parse details from status row.");
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
 * Get compile error info from SCU
 * @param bott      Result bott file
 * @return Compile error info
 */
string SCUJudger::getCEinfo(Bott * bott) {
  prepareCurl();
  curl_easy_setopt(
      curl, CURLOPT_URL,
      ((string) "http://acm.scu.edu.cn/soj/judge_message.action?id=" +
          bott->Getremote_runid()).c_str());
  performCurl();

  // SCU is in GBK charset
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

/**
 * Check whether the result is final
 * @param result        Current result
 * @return Is final one or not
 */
string SCUJudger::convertResult(string result) {
  if (result.find("Compilation") != string::npos) return "Compile Error";
  if (result.find("Accepted") != string::npos) return "Accepted";
  if (result.find("Wrong") != string::npos) return "Wrong Answer";
  if (result.find("Runtime") != string::npos) return "Runtime Error";
  if (result.find("Time Limit") != string::npos) return "Time Limit Exceed";
  if (result.find("Presentation") != string::npos) return "Presentation Error";
  if (result.find("Memory") != string::npos) return "Memory Limit Exceed";
  return trim(result);
}

/**
 * Load JPEG image from File
 * @param filename  Filename to be loaded
 * @param width     Image width
 * @param height    Image height
 * @param bitmap    Image storage
 */
void SCUJudger::loadImage(string filename, int &width, int &height,
                          int *& bitmap) {
  unsigned char a, r, g, b;
  struct jpeg_decompress_struct cinfo;
  struct jpeg_error_mgr jerr;

  FILE * infile; /* source file */
  JSAMPARRAY pJpegBuffer; /* Output row buffer */
  int row_stride; /* physical row width in output buffer */
  if ((infile = fopen(filename.c_str(), "rb")) == NULL) {
    throw Exception("Image file not found.");
  }
  cinfo.err = jpeg_std_error(&jerr);
  jpeg_create_decompress(&cinfo);
  jpeg_stdio_src(&cinfo, infile);
  jpeg_read_header(&cinfo, TRUE);
  jpeg_start_decompress(&cinfo);
  width = cinfo.output_width;
  height = cinfo.output_height;

  unsigned char * pDummy = new unsigned char [width * height * 4];
  unsigned char * pTest = pDummy;
  if (!pDummy) {
    throw Exception("Cannot allocate memory for image processing");
  }
  row_stride = width * cinfo.output_components;
  pJpegBuffer = (*cinfo.mem->alloc_sarray)
      ((j_common_ptr) & cinfo, JPOOL_IMAGE, row_stride, 1);

  while (cinfo.output_scanline < cinfo.output_height) {
    jpeg_read_scanlines(&cinfo, pJpegBuffer, 1);
    for (int x = 0; x < width; x++) {
      a = 0; // alpha value is not supported on jpg
      r = pJpegBuffer[0][cinfo.output_components * x];
      if (cinfo.output_components > 2) {
        g = pJpegBuffer[0][cinfo.output_components * x + 1];
        b = pJpegBuffer[0][cinfo.output_components * x + 2];
      } else {
        g = r;
        b = r;
      }
      *(pDummy++) = b;
      *(pDummy++) = g;
      *(pDummy++) = r;
      *(pDummy++) = a;
    }
  }
  fclose(infile);
  jpeg_finish_decompress(&cinfo);
  jpeg_destroy_decompress(&cinfo);

  bitmap = (int*) pTest;
}

/**
 * Get the (x, y) for the n-th number in the image
 * @param n         Which number to see
 * @param x         X coordinate
 * @param y         Y coordinate
 * @param width     Image width
 * @param height    Image height
 * @param bitmap    Image storage
 * @return 'x' represents black, ' ' represents white
 */
char SCUJudger::getNXY(int n, int x, int y, int width, int height,
                       int * bitmap) {
  int v = bitmap[x * width + (y + n * 8 + 3)];
  // threshold, convert to black or white
  if (v > 0x600000) return ' ';
  else return 'x';
}

/**
 * Get validation code from file
 * @return validation code
 */
string SCUJudger::getCode() {
  prepareCurl();
  curl_easy_setopt(curl, CURLOPT_URL,
                   "http://acm.scu.edu.cn/soj/validation_code");
  performCurl();

  int *jpg, width, height;
  jpg = NULL;
  loadImage(tmpfilename, width, height, jpg);
  if (jpg == NULL) return "";

  //    for (int i = 0; i < height; ++i, printf("\n")) {
  //        for (int j = 0; j < width; ++j) {
  //            printf("%c", jpg[i * width + j] > 0x600000 ? ' ' : 'x');
  //        }
  //    }

  string code;
  for (int n = 0; n < 4; ++n) {
    if (getNXY(n, 5, 1, width, height, jpg) == 'x' &&
        getNXY(n, 5, 2, width, height, jpg) == ' ' &&
        getNXY(n, 5, 5, width, height, jpg) == ' ' &&
        getNXY(n, 5, 6, width, height, jpg) == 'x') code += '0';

    else if (getNXY(n, 2, 2, width, height, jpg) == ' ' &&
        getNXY(n, 2, 3, width, height, jpg) == 'x' &&
        getNXY(n, 2, 4, width, height, jpg) == ' ') code += '1';

    else if (getNXY(n, 9, 1, width, height, jpg) == 'x' &&
        getNXY(n, 9, 2, width, height, jpg) == 'x' &&
        getNXY(n, 9, 3, width, height, jpg) == 'x' &&
        getNXY(n, 9, 4, width, height, jpg) == 'x' &&
        getNXY(n, 9, 5, width, height, jpg) == 'x') code += '2';

    else if (getNXY(n, 5, 1, width, height, jpg) == ' ' &&
        getNXY(n, 5, 2, width, height, jpg) == ' ' &&
        getNXY(n, 5, 3, width, height, jpg) == 'x' &&
        getNXY(n, 5, 4, width, height, jpg) == 'x' &&
        getNXY(n, 5, 6, width, height, jpg) == ' ') code += '3';

    else if (getNXY(n, 2, 3, width, height, jpg) == ' ' &&
        getNXY(n, 2, 4, width, height, jpg) == 'x' &&
        getNXY(n, 2, 5, width, height, jpg) == 'x') code += '4';

    else if (getNXY(n, 4, 1, width, height, jpg) == 'x' &&
        getNXY(n, 4, 2, width, height, jpg) == 'x' &&
        getNXY(n, 4, 3, width, height, jpg) == 'x' &&
        getNXY(n, 4, 6, width, height, jpg) == ' ') code += '5';

    else if (getNXY(n, 4, 1, width, height, jpg) == 'x' &&
        getNXY(n, 4, 2, width, height, jpg) == ' ' &&
        getNXY(n, 4, 3, width, height, jpg) == 'x' &&
        getNXY(n, 4, 4, width, height, jpg) == 'x') code += '6';

    else if (getNXY(n, 1, 1, width, height, jpg) == 'x' &&
        getNXY(n, 1, 6, width, height, jpg) == 'x') code += '7';

    else if (getNXY(n, 5, 2, width, height, jpg) == 'x' &&
        getNXY(n, 5, 3, width, height, jpg) == 'x' &&
        getNXY(n, 5, 4, width, height, jpg) == 'x' &&
        getNXY(n, 5, 5, width, height, jpg) == 'x') code += '8';

    else if (getNXY(n, 6, 3, width, height, jpg) == 'x' &&
        getNXY(n, 6, 4, width, height, jpg) == 'x' &&
        getNXY(n, 6, 5, width, height, jpg) == ' ' &&
        getNXY(n, 6, 6, width, height, jpg) == 'x') code += '9';

    else code += '-';
  }
  //    cout << code << endl;
  delete [] jpg;
  return code;
}
