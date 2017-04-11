/* 
 * File:   WHUJudger.h
 * Author: payper
 *
 * Created on 2014年3月25日, 下午4:01
 */

#ifndef WHUJUDGER_H
#define WHUJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class WHUJudger : public VirtualJudger {
public:
  WHUJudger(JudgerInfo *);
  virtual ~WHUJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
  string getSeed();
};

#endif /* WHUJUDGER_H */

