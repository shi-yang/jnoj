/* 
 * File:   ACdreamJudger.h
 * Author: 51isoft
 *
 * Created on 2014年8月13日, 下午9:52
 */

#ifndef ACDREAMJUDGER_H
#define ACDREAMJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class ACdreamJudger : public VirtualJudger {
public:
  ACdreamJudger(JudgerInfo *);
  virtual ~ACdreamJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* ACDREAMJUDGER_H */

