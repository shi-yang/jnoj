/* 
 * File:   HRBUSTJudger.h
 * Author: 51isoft
 *
 * Created on 2014年8月22日, 下午10:26
 */

#ifndef HRBUSTJUDGER_H
#define HRBUSTJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class HRBUSTJudger : public VirtualJudger {
public:
  HRBUSTJudger(JudgerInfo *);
  virtual ~HRBUSTJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* HRBUSTJUDGER_H */

