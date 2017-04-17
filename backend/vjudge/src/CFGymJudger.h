/* 
 * File:   CFGymJudger.h
 */

#ifndef CFGYMJUDGER_H
#define CFGYMJUDGER_H

#include "CFJudger.h"

class CFGymJudger : public CFJudger {
public:
  CFGymJudger(JudgerInfo *);
  virtual ~CFGymJudger();
private:
  void initHandShake();
  string getSubmitUrl(string);
  string getVerdictUrl(string, string);
};

#endif /* CFGYMJUDGER_H */

