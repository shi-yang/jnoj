/* 
 * File:   SPOJJudger.h
 * Author: payper
 *
 * Created on 2014年3月25日, 下午3:10
 */

#ifndef SPOJJUDGER_H
#define SPOJJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class SPOJJudger : public VirtualJudger {
public:
  SPOJJudger(JudgerInfo *);
  virtual ~SPOJJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* SPOJJUDGER_H */

