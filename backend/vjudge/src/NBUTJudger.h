/* 
 * File:   NBUTJudger.h
 * Author: 51isoft
 *
 * Created on 2014年8月5日, 下午4:47
 */

#ifndef NBUTJUDGER_H
#define NBUTJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class NBUTJudger : public VirtualJudger {
public:
  NBUTJudger(JudgerInfo *);
  virtual ~NBUTJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};
#endif /* NBUTJUDGER_H */

