/* 
 * File:   HDUJudger.h
 * Author: 51isoft
 *
 * Created on 2014年2月4日, 下午6:07
 */

#ifndef HDUJUDGER_H
#define HDUJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class HDUJudger : public VirtualJudger {
public:
  HDUJudger(JudgerInfo *);
  virtual ~HDUJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* HDUJUDGER_H */

