/* 
 * File:   OpenJudgeJudger.h
 * Author: payper
 *
 * Created on 2014年3月26日, 下午12:28
 */

#ifndef OPENJUDGEJUDGER_H
#define OPENJUDGEJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class OpenJudgeJudger : public VirtualJudger {
public:
  OpenJudgeJudger(JudgerInfo *);
  virtual ~OpenJudgeJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* OPENJUDGEJUDGER_H */

