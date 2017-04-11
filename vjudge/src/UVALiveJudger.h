/* 
 * File:   UVALiveJudger.h
 * Author: payper
 *
 * Created on 2014年3月24日, 下午3:31
 */

#ifndef UVALIVEJUDGER_H
#define UVALIVEJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class UVALiveJudger : public VirtualJudger {
public:
  UVALiveJudger(JudgerInfo *);
  virtual ~UVALiveJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
  string getLoginHiddenParams();

};

#endif /* UVALIVEJUDGER_H */

