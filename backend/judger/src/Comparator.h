#ifndef COMPARATOR_H
#define COMPARATOR_H

#include "chaclient.h"

#include "Program.h"

class Comparator {
public:
  Comparator();
  virtual ~Comparator();

  string Getstdout_filename() {
    return stdout_filename;
  }

  void Setstdout_filename(string val) {
    stdout_filename = val;
  }

  string Getout_filename() {
    return out_filename;
  }

  void Setout_filename(string val) {
    out_filename = val;
  }

  string Getin_filename() {
    return in_filename;
  }

  void Setin_filename(string val) {
    in_filename = val;
  }

  string Getsrc_filename() {
    return src_filename;
  }

  void Setsrc_filename(string val) {
    src_filename = val;
  }

  int Getisspj() {
    return isspj;
  }

  void Setisspj(int val) {
    isspj = val;
  }

  Program * Getspj() {
    return spj;
  }

  void Setspj(Program * val) {
    spj = val;
  }

  string Getdetail() {
    return detail;
  }

  void Setdetail(string val) {
    detail = val;
  }
  int Compare();

  void Setpid(int val) {
    pid = val;
  }
protected:
private:
  string src_filename;
  string in_filename;
  string stdout_filename;
  string out_filename;
  string infile;
  string detail;
  int isspj;
  int pid;
  Program * spj;
};

#endif // COMPARATOR_H
