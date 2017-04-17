#ifndef PCONFIG_H
#define PCONFIG_H

#include "chaclient.h"
#include "ini.hpp"

class PConfig {
public:
    PConfig();

    PConfig(int, string);

    virtual ~PConfig();

    string Getdata_checker_filename() {
        return data_checker_filename;
    }

    void Setdata_checker_filename(string val) {
        data_checker_filename = val;
    }

    string Getsolution_filename() {
        return solution_filename;
    }

    void Setsolution_filename(string val) {
        solution_filename = val;
    }

    int Getdata_checker_language() {
        return data_checker_language;
    }

    void Setdata_checker_language(int val) {
        data_checker_language = val;
    }

    int Getsolution_language() {
        return solution_language;
    }

    void Setsolution_language(int val) {
        solution_language = val;
    }

    bool Geterror() {
        return error;
    }

    void Seterror(bool val) {
        error = val;
    }

    string Getvalidator_filename() const {
        return validator_filename;
    }

    void Setvalidator_filename(string validator_filename) {
        this->validator_filename = validator_filename;
    }

    int Getvalidator_language() const {
        return validator_language;
    }

    void Setvalidator_language(int validator_language) {
        this->validator_language = validator_language;
    }

protected:
private:
    string data_checker_filename;
    string solution_filename;
    string validator_filename;
    int data_checker_language;
    int solution_language;
    int validator_language;
    string basedir;
    map<string, string> config;
    bool error;

    string Inttostring(int);
};

#endif // PCONFIG_H
