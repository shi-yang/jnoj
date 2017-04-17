/* 
 * File:   JudgerFactory.h
 * Author: 51isoft
 *
 * Created on 2014年2月1日, 上午10:04
 */

#ifndef JUDGERFACTORY_H
#define JUDGERFACTORY_H

#include "JudgerInfo.h"

#include "VirtualJudger.h"
#include "CFJudger.h"
#include "CFGymJudger.h"
#include "PKUJudger.h"
#include "HDUJudger.h"
#include "HUSTJudger.h"
#include "FZUJudger.h"
#include "UVALiveJudger.h"
#include "UVAJudger.h"
#include "SGUJudger.h"
#include "LOJJudger.h"
#include "UralJudger.h"
#include "ZJUJudger.h"
#include "SPOJJudger.h"
#include "WHUJudger.h"
#include "SYSUJudger.h"
#include "OpenJudgeJudger.h"
#include "SCUJudger.h"
#include "NBUTJudger.h"
#include "NJUPTJudger.h"
#include "AizuJudger.h"
#include "ACdreamJudger.h"
#include "CCJudger.h"
#include "HRBUSTJudger.h"
#include "UESTCJudger.h"

class JudgerFactory {
public:
  JudgerFactory();
  virtual ~JudgerFactory();
  static VirtualJudger * createJudger(JudgerInfo *);
private:

};

#endif /* JUDGERFACTORY_H */

