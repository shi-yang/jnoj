/* 
 * File:   FZUJudger.h
 * Author: 51isoft
 *
 * Created on 2014年2月4日, 下午5:00
 */

#ifndef FZUJUDGER_H
#define FZUJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class FZUJudger : public VirtualJudger {
public:
  FZUJudger(JudgerInfo *);
  virtual ~FZUJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
};

#endif /* FZUJUDGER_H */

