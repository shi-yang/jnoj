/* 
 * File:   UVAJudger.h
 * Author: payper
 *
 * Created on 2014年3月25日, 上午11:38
 */

#ifndef UVAJUDGER_H
#define UVAJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class UVAJudger : public VirtualJudger {
public:
  UVAJudger(JudgerInfo *);
  virtual ~UVAJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
  string getLoginHiddenParams();

};

#endif /* UVAJUDGER_H */

