/* 
 * File:   Interactive.h
 * Author: payper
 *
 * Created on 2014年4月24日, 下午5:41
 */

#ifndef INTERACTIVE_H
#define	INTERACTIVE_H

#include "Program.h"

class Interactive : public Program {
public:
  Interactive();
  virtual ~Interactive();

  string GetValidator_base_filename() const {
    return validator_base_filename;
  }

  void SetValidator_base_filename(string validator_base_filename) {
    this->validator_base_filename = validator_base_filename;
  }

  bool IsValidator_compiled() const {
    return validator_compiled;
  }

  void SetValidator_compiled(bool validator_compiled) {
    this->validator_compiled = validator_compiled;
  }

  string GetValidator_exc_filename() const {
    return validator_exc_filename;
  }

  void SetValidator_exc_filename(string validator_exc_filename) {
    this->validator_exc_filename = validator_exc_filename;
  }

  int GetValidator_language() const {
    return validator_language;
  }

  void SetValidator_language(int validator_language) {
    this->validator_language = validator_language;
  }

  string GetValidator_source() const {
    return validator_source;
  }

  void SetValidator_source(string validator_source) {
    this->validator_source = validator_source;
  }

  string GetValidator_src_filename() const {
    return validator_src_filename;
  }

  void SetValidator_src_filename(string validator_src_filename) {
    this->validator_src_filename = validator_src_filename;
  }

  void Run();

private:
  int Excution();

  string validator_base_filename;
  string validator_src_filename;
  string validator_exc_filename;
  string validator_in_filename;
  string validator_out_filename;
  string validator_err_filename;
  string validator_res_filename;
  string validator_cinfo_filename;

  bool validator_compiled;

  string validator_source;
  int validator_language;
};

#endif	/* INTERACTIVE_H */

