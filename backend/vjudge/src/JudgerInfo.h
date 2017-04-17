/* 
 * File:   JudgerInfo.h
 * Author: 51isoft
 *
 * Created on 2014年1月31日, 下午10:23
 */

#ifndef JUDGERINFO_H
#define JUDGERINFO_H

#include <string>

using namespace std;

class JudgerInfo {
public:
  JudgerInfo();

  /**
   * Generate a new judger config
   * @param _oj               OJ name
   * @param _id               Identifier, for logging purpose and cookies store
   * @param _username         Login username
   * @param _password         Login password
   * @param _max_wait_time    How long it will wait for a run
   */
  JudgerInfo(string _oj, string _id, string _username, string _password, int _max_wait_time) :
  oj(_oj), id(_id), username(_username), password(_password), max_wait_time(_max_wait_time) {
  }
  virtual ~JudgerInfo();

  int GetMax_wait_time() const {
    return max_wait_time;
  }

  void SetMax_wait_time(int max_wait_time) {
    this->max_wait_time = max_wait_time;
  }

  string GetPassword() const {
    return password;
  }

  string GetId() const {
    return id;
  }

  void SetId(string id) {
    this->id = id;
  }

  string GetOj() const {
    return oj;
  }

  void SetOj(string oj) {
    this->oj = oj;
  }

  void SetPassword(string password) {
    this->password = password;
  }

  string GetUsername() const {
    return username;
  }

  void SetUsername(string username) {
    this->username = username;
  }

private:
  string oj;
  string id;
  string username;
  string password;
  int max_wait_time;

};

#endif /* JUDGERINFO_H */

