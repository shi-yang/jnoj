#include "Config.h"

Config *Config::instance = new Config;

Config *Config::Getinstance() {
    return instance;
}

Config::Config() {
    try {
        INI::Parser ini("config.ini");
        database_ip = ini.top()["database_ip"];
        database_port = atoi(ini.top()["database_port"].c_str());
        database_user = ini.top()["database_user"];
        database_password = ini.top()["database_password"];
        database_table = ini.top()["database_table"];
        judger_string = ini.top()["judge_connect_string"];
        submit_string = ini.top()["submit_string"];
        rejudge_string = ini.top()["rejudge_string"];
        error_rejudge_string = ini.top()["error_rejudge_string"];
        challenge_string = ini.top()["challenge_string"];
        pretest_string = ini.top()["pretest_string"];
        testall_string = ini.top()["test_all_string"];
        local_identifier = ini.top()["local_identifier"];
        port_listen = atoi(ini.top()["port_listen"].c_str());
    } catch (runtime_error &e) {
        cerr << e.what();
        exit(1);
    }
    //ctor
}

Config::~Config() {
    //dtor
}
