/* 
 * File:   SYSUJudger.h
 * Author: payper
 *
 * Created on 2014年3月26日, 上午11:49
 */

#ifndef SYSUJUDGER_H
#define SYSUJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class SYSUJudger : public VirtualJudger {
public:
  SYSUJudger(JudgerInfo *);
  virtual ~SYSUJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* SYSUJUDGER_H */

