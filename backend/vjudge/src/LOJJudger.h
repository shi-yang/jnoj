/* 
 * File:   LOJJudger.h
 * Author: payper
 *
 * Created on 2014年3月25日, 下午12:50
 */

#ifndef LOJJUDGER_H
#define LOJJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class LOJJudger : public VirtualJudger {
public:
  LOJJudger(JudgerInfo *);
  virtual ~LOJJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);

};

#endif /* LOJJUDGER_H */

