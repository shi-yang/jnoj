/* 
 * File:   HUSTJudger.h
 * Author: 51isoft
 *
 * Created on 2014年2月4日, 下午9:05
 */

#ifndef HUSTJUDGER_H
#define HUSTJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class HUSTJudger : public VirtualJudger {
public:
  HUSTJudger(JudgerInfo *);
  virtual ~HUSTJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* HUSTJUDGER_H */

