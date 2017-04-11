/* 
 * File:   CFJudger.h
 * Author: 51isoft
 *
 * Created on 2014年2月1日, 上午12:02
 */

#ifndef CFJUDGER_H
#define CFJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class CFJudger : public VirtualJudger {
public:
  CFJudger(JudgerInfo *);
  virtual ~CFJudger();
protected:
  virtual string getSubmitUrl(string);
  virtual string getVerdictUrl(string, string);
  virtual void initHandShake();
private:
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string getCsrfParams(string);
  string getttaValue();
  string convertResult(string);
  int calculatetta(string);
  string getVerdict(string, string);
};

#endif /* CFJUDGER_H */

