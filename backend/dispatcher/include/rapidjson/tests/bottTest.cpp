/* 
 * File:   bottTest.cpp
 * Author: 51isoft
 *
 * Created on 2015-5-17, 14:27:35
 */

#include <stdlib.h>
#include <iostream>

#include "Bott.h"

/*
 * Simple C++ Test Suite
 */

void test1() {
  std::cout << "bottTest test 1" << std::endl;
  Bott bott("tests/run.bott");
  if (bott.Gettype() != 2) {
    std::cout << "%TEST_FAILED% time=0 testname=test1 (bottTest) message=Type failed" << std::endl;
  }
  bott.Setout_filename("tests/run_gen.bott");
  bott.toFile();
}

void test2() {
//  std::cout << "bottTest test 2" << std::endl;
//  std::cout << "%TEST_FAILED% time=0 testname=test2 (bottTest) message=error message sample" << std::endl;
}

int main(int argc, char** argv) {
  std::cout << "%SUITE_STARTING% bottTest" << std::endl;
  std::cout << "%SUITE_STARTED%" << std::endl;

  std::cout << "%TEST_STARTED% test1 (bottTest)" << std::endl;
  test1();
  std::cout << "%TEST_FINISHED% time=0 test1 (bottTest)" << std::endl;

  std::cout << "%TEST_STARTED% test2 (bottTest)\n" << std::endl;
  test2();
  std::cout << "%TEST_FINISHED% time=0 test2 (bottTest)" << std::endl;

  std::cout << "%SUITE_FINISHED% time=0" << std::endl;

  return (EXIT_SUCCESS);
}

