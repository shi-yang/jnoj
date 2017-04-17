#ifndef UESTCJUDGER_H
#define UESTCJUDGER_H

#include "vjudge.h"
#include "VirtualJudger.h"

class UESTCJudger : public VirtualJudger {
public:
  UESTCJudger(JudgerInfo *);
  virtual ~UESTCJudger();
private:
  void initHandShake();
  void login();
  int submit(Bott *);
  Bott * getStatus(Bott *);
  string getCEinfo(Bott *);
  string convertResult(string);
  void prepareCurl();
};

#endif /* UESTCJUDGER_H */
