/* 
 * File:   VirtualJudger.h
 * Author: 51isoft
 *
 * Created on 2014年1月31日, 下午9:08
 */

#ifndef VIRTUALJUDGER_H
#define VIRTUALJUDGER_H

#include "vjudge.h"
#include "SocketHandler.h"
#include "JudgerInfo.h"
#include "Bott.h"

class VirtualJudger {
public:
  VirtualJudger(JudgerInfo *);
  virtual ~VirtualJudger();
  virtual void judge(Bott * bott, string filename);
  void run();

  static const int MIN_SOURCE_LENGTH;
  static const int SLEEP_INTERVAL;

  // submit status code
  static const int SUBMIT_NORMAL;
  static const int SUBMIT_SAME_CODE;
  static const int SUBMIT_INVALID_LANGUAGE;
  static const int SUBMIT_COMPILE_ERROR;
  static const int SUBMIT_OTHER_ERROR;
protected:
  void log(string);
  void generateSpecialResult(Bott *, string);
  void clearCookies();
  bool isFinalResult(string);

  /**
   * Send handshake message to dispatcher
   */
  virtual void initHandShake() = 0;
  /**
   * Login to remote OJ
   */
  virtual void login() = 0;
  /**
   * Submit run to remote OJ
   * @param bott      Bott file for Run info
   */
  virtual int submit(Bott * bott) = 0;
  /**
   * Get result and related info
   * @param bott      Original Bott info
   * @return Result Bott file
   */
  virtual Bott * getStatus(Bott * bott) = 0;

  /**
   * Get compile error info from remote OJ
   * @param bott      Result bott file
   * @return Compile error info
   */
  virtual string getCEinfo(Bott * bott) = 0;

  void prepareCurl();
  void performCurl();
  string convertLanguage(int);
  SocketHandler * socket;
  JudgerInfo * info;
  string tmpfilename;
  string cookiefilename;
  // language convertion table, convert local language to remote ones
  map <int, string> language_table;
  bool logged_in;

  CURL * curl;
  FILE * curl_file;
};

#endif /* VIRTUALJUDGER_H */

