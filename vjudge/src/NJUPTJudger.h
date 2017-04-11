/* 
 * File:   NJUPTJudger.h
 * Author: 51isoft
 *
 * Created on 2014年8月12日, 下午9:24
 */

#ifndef NJUPTJUDGER_H
#define NJUPTJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class NJUPTJudger : public VirtualJudger {
public:
  NJUPTJudger(JudgerInfo *);
  virtual ~NJUPTJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* NJUPTJUDGER_H */

