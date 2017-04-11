#include "GlobalHelpers.h"

/**
 * Convert an integer to a string
 * @param i     The integer
 * @return      The converted string
 */
string intToString(int i) {
  char str[15];
  sprintf(str, "%d", i);
  return (string) str;
}

/**
 * Convert a string to an integer
 * WARNING: did not check if it's valid
 * @param str   The string
 * @return      The converted interger
 */
int stringToInt(string str) {
  return atoi(str.c_str());
}

/**
 * Trim leading and trailing spaces
 * @param str   Original string
 * @return Trimmed string
 */
string trim(string str) {
  string spaces = " \t\n\r";
  size_t start = str.find_first_not_of(spaces);
  size_t end = str.find_last_not_of(spaces);
  return str.substr(start, end - start + 1);
}

/**
 * Get current date/time, format is YYYY-MM-DD HH:mm:ss
 * @return Current datetime in YYYY-MM-DD HH:mm:ss
 */
const string currentDateTime() {
  time_t now = time(NULL);
  struct tm tstruct;
  char buf[80];
  tstruct = *localtime(&now);
  strftime(buf, sizeof (buf), "%Y-%m-%d %H:%M:%S", &tstruct);

  return buf;
}

/**
 * Get current date
 * @return Current date in YYYY-MM-DD
 */
const string currentDate() {
  time_t now = time(NULL);
  struct tm tstruct;
  char buf[80];
  tstruct = *localtime(&now);
  strftime(buf, sizeof (buf), "%Y-%m-%d", &tstruct);

  return buf;
}

/**
 * Split the string into pieces by the delimeter
 * taken from https://stackoverflow.com/a/236803
 * @param str                   The original string
 * @param delim                 Delimeter
 * @param removeAppendedNull    Where to remove the appended empty strings
 * @return Splitted string
 */
vector<string> split(const string &str, char delim, bool removeAppendedNull) {
  vector<string> elems;
  stringstream ss(str);
  string item;
  while (getline(ss, item, delim)) {
    elems.push_back(item);
  }
  if (removeAppendedNull) {
    while (!elems.empty() && elems.back().empty()) {
      elems.pop_back();
    }
  }
  if (elems.empty()) {
    elems.push_back(str);
  }
  return elems;
}

/**
 * Split the string into pieces by the delimeter, ignore appended empty strings
 * @param str   The original string
 * @param delim Delimeter
 * @return Splitted string
 */
vector<string> split(const string &str, char delim) {
  return split(str, delim, true);
}

/**
 * Load the whole text file content to a string
 * @param filename      File to load
 * @return File content
 */
string loadAllFromFile(string filename) {
  int tried = 0;
  string res = "", tmps;
  fstream fin(filename.c_str(), fstream::in);

  while (fin.fail() && tried++ < 10) {
    fin.open(filename.c_str(), fstream::in);
  }

  if (fin.fail()) {
    throw Exception("File not found");
  }

  while (getline(fin, tmps)) {
    if (res != "") res += "\n";
    res += tmps;
    if (fin.eof()) break;
  }
  fin.close();
  return res;
}
