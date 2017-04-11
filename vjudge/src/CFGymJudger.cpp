/* 
 * File:   CFGymJudger.cpp
 */

#include "CFGymJudger.h"

/**
 * Create a CF Gym Judger
 * @param _info Should be a pointer of a JudgerInfo
 */
CFGymJudger::CFGymJudger(JudgerInfo * _info) : CFJudger(_info) {
}

CFGymJudger::~CFGymJudger() {
}

void CFGymJudger::initHandShake() {
  socket->sendMessage(CONFIG->GetJudge_connect_string() + "\nCodeForcesGym");
}

/**
 * Get submit url, extracted from submit for reuse
 * @param contest   Contest ID
 * @return Submit url
 */
string CFGymJudger::getSubmitUrl(string contest) {
  return "http://codeforces.com/gym/" + contest + "/submit";
}

/**
 * Get url for the verdict
 * @param contest       Contest ID
 * @param runid         Remote runid
 * @return Verdict url
 */
string CFGymJudger::getVerdictUrl(string contest, string runid) {
  return "http://codeforces.com/gym/" + contest + "/submission/" + runid;
}
