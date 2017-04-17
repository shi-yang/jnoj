/* 
 * File:   SGUJudger.h
 * Author: payper
 *
 * Created on 2014年3月25日, 下午12:10
 */

#ifndef SGUJUDGER_H
#define SGUJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class SGUJudger : public VirtualJudger {
public:
  SGUJudger(JudgerInfo *);
  virtual ~SGUJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);

};

#endif /* SGUJUDGER_H */

