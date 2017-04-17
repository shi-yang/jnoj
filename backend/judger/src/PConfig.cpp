#include "PConfig.h"

PConfig::PConfig() {
    //ctor
}

string PConfig::Inttostring(int x) {
    char tt[100];
    sprintf(tt, "%d", x);
    string t = tt;
    return t;
}

PConfig::PConfig(int pid, string prefix) {
    basedir = (string) "testdata/" + Inttostring(pid) + "/" + prefix + "/";

    try {
        INI::Parser ini((basedir + "config.ini").c_str());
        data_checker_filename = basedir + ini.top()["data_checker_filename"];
        data_checker_language = atoi(ini.top()["data_checker_language"].c_str());
        solution_filename = basedir + ini.top()["solution_filename"];
        solution_language = atoi(ini.top()["solution_language"].c_str());
        validator_filename = basedir + ini.top()["validator_filename"];
        validator_language = atoi(ini.top()["validator_language"].c_str());
        error = false;
    } catch (runtime_error &e) {
        error = true;
    }
}

PConfig::~PConfig() {
    //dtor
}
