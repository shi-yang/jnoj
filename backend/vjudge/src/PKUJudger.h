/* 
 * File:   PKUJudger.h
 * Author: 51isoft
 *
 * Created on 2014年2月4日, 下午2:41
 */

#ifndef PKUJUDGER_H
#define PKUJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class PKUJudger : public VirtualJudger {
public:
  PKUJudger(JudgerInfo *);
  virtual ~PKUJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* PKUJUDGER_H */

