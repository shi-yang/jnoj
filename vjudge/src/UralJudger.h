/* 
 * File:   UralJudger.h
 * Author: payper
 *
 * Created on 2014年3月25日, 下午1:22
 */

#ifndef URALJUDGER_H
#define URALJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class UralJudger : public VirtualJudger {
public:
  UralJudger(JudgerInfo *);
  virtual ~UralJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
  string author_id;

};

#endif /* URALJUDGER_H */

