/* 
 * File:   CCJudger.h
 * Author: 51isoft
 *
 * Created on 2014年8月20日, 下午3:06
 */

#ifndef CCJUDGER_H
#define CCJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class CCJudger : public VirtualJudger {
public:
  CCJudger(JudgerInfo *);
  virtual ~CCJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
  string getLoginHiddenParams();
  vector< pair<string, string> > getSubmitHiddenParams(string);

};

#endif /* CCJUDGER_H */

