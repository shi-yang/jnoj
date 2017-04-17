/* 
 * File:   JudgerFactory.cpp
 * Author: 51isoft
 * 
 * Created on 2014年2月1日, 上午10:04
 */

#include "JudgerFactory.h"

JudgerFactory::JudgerFactory() {
}

JudgerFactory::~JudgerFactory() {
}

VirtualJudger * JudgerFactory::createJudger(JudgerInfo * judger_info) {
  string oj = judger_info->GetOj();
  if (oj == "CodeForces") {
    return new CFJudger(judger_info);
  } else if (oj == "CodeForcesGym") {
    return new CFGymJudger(judger_info);
  } else if (oj == "PKU") {
    return new PKUJudger(judger_info);
  } else if (oj == "HDU") {
    return new HDUJudger(judger_info);
  } else if (oj == "HUST") {
    return new HUSTJudger(judger_info);
  } else if (oj == "FZU") {
    return new FZUJudger(judger_info);
  } else if (oj == "UVALive") {
    return new UVALiveJudger(judger_info);
  } else if (oj == "UVA") {
    return new UVAJudger(judger_info);
  } else if (oj == "SGU") {
    return new SGUJudger(judger_info);
  } else if (oj == "LightOJ") {
    return new LOJJudger(judger_info);
  } else if (oj == "Ural") {
    return new UralJudger(judger_info);
  } else if (oj == "ZJU") {
    return new ZJUJudger(judger_info);
  } else if (oj == "SPOJ") {
    return new SPOJJudger(judger_info);
  } else if (oj == "WHU") {
    return new WHUJudger(judger_info);
  } else if (oj == "SYSU") {
    return new SYSUJudger(judger_info);
  } else if (oj == "OpenJudge") {
    return new OpenJudgeJudger(judger_info);
  } else if (oj == "SCU") {
    return new SCUJudger(judger_info);
  } else if (oj == "NBUT") {
    return new NBUTJudger(judger_info);
  } else if (oj == "NJUPT") {
    return new NJUPTJudger(judger_info);
  } else if (oj == "Aizu") {
    return new AizuJudger(judger_info);
  } else if (oj == "ACdream") {
    return new ACdreamJudger(judger_info);
  } else if (oj == "CodeChef") {
    return new CCJudger(judger_info);
  } else if (oj == "HRBUST") {
    return new HRBUSTJudger(judger_info);
  } else if (oj == "UESTC") {
    return new UESTCJudger(judger_info);
  } else {
    throw Exception("Unknown OJ type: " + oj);
  }
}
