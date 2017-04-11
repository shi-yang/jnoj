/* 
 * File:   SCUJudger.h
 * Author: payper
 *
 * Created on 2014年3月26日, 下午1:03
 */

#ifndef SCUJUDGER_H
#define SCUJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class SCUJudger : public VirtualJudger {
public:
  SCUJudger(JudgerInfo *);
  virtual ~SCUJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
  string getCode();
  void loadImage(string, int &, int &, int *&);
  char getNXY(int, int, int, int, int, int *);
};

#endif /* SCUJUDGER_H */

