/* 
 * File:   Submit.h
 * Author: 51isoft
 *
 * Created on 2014年1月13日, 下午10:44
 */

#ifndef SUBMIT_H
#define	SUBMIT_H

#include "dispatcher.h"

class Submit {
public:

  /**
   * Create a submit
   * @param _type     Type of submit
   * @param _id       RunID/ChallengeID according to type
   * @param _oj       Indicate which judger should be used
   */
  Submit(int _type, int _id, string _oj) : type(_type), id(_id), oj(_oj) {
  }
  virtual ~Submit();

  string Getoj() const {
    return oj;
  }

  int Getid() const {
    return id;
  }

  int Gettype() const {
    return type;
  }


private:
  /*
   * type represents what kind of submit this is
   * examples: NEED_JUDGE, DO_CHALLENGE, DO_TESTALL ...
   */
  int type;
  /*
   * according to type, id can have different meanings
   * examples: when type is NEED_JUDGE, id means runid
   *            when type is DO_CHALLENGE, id means challenge_id (cha_id)
   */
  int id;
  /*
   * indicate which judger should this submit be processed by
   */
  string oj;
};

#endif	/* SUBMIT_H */

