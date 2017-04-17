/* 
 * File:   GlobalHelpers.h
 * Author: 51isoft
 *
 * Created on 2014年1月14日, 上午12:17
 */

#ifndef GLOBALHELPERS_H
#define GLOBALHELPERS_H

#include <string.h>
#include <stdio.h>

#include <string>
#include <vector>
#include <istream>
#include <sstream>
#include <fstream>

#include <iconv.h>
#include <glib.h>
#include <openssl/sha.h>

using namespace std;

#include "Config.h"
#include "Logger.h"

string trim(string);
string loadAllFromFile(string);
string intToString(int);
string escapeString(string);
string unescapeString(string);
string escapeURL(const string &);
int stringToInt(string);
double stringToDouble(string);
string capitalize(string);
string toLowerCase(string);
const string currentDateTime();
const string currentDate();
vector<string> split(const string &, char, bool);
vector<string> split(const string &, char);
string charsetConvert(const string &, const string &, const string &);
string replaceAll(string, const string&, const string&);
string sha1String(string);
string base64Encode(string);
#define CONFIG Config::Getinstance()
#define LOG Logger::Getinstance()->log

extern "C" size_t decode_html_entities_utf8(char *dest, const char *src);

#endif /* GLOBALHELPERS_H */

