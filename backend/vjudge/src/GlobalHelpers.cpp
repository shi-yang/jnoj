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
 * Convert a string to a float number
 * WARNING: did not check if it's valid
 * @param str   The string
 * @return      The converted float
 */
double stringToDouble(string str) {
  return atof(str.c_str());
}

/**
 * Capitalize a string, eg. "aaa bb c" to "Aaa Bb C"
 * @param str   Original string
 * @return      Capitalized string
 */
string capitalize(string str) {
  for (size_t i = 1; i < str.length(); ++i) {
    if (i == 0 || str[i - 1] == ' ') {
      if (str[i] >= 'a' && str[i] <= 'z') str[i] += 'A' - 'a';
    }
  }
  return str;
}

/**
 * Transfer a string to lower case, eg. "A bCd E" to "a bcd e"
 * @param str   Original string
 * @return      Lower case string
 */
string toLowerCase(string str) {
  for (size_t i = 0; i < str.length(); ++i) str[i] = tolower(str[i]);
  return str;
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
    return res;
  }

  if (fin.fail()) return res;
  while (getline(fin, tmps)) {
    if (res != "") res += "\n";
    res += tmps;
    if (fin.eof()) break;
  }
  fin.close();
  return res;
}

char dec2hexChar(short int n) {
  if (0 <= n && n <= 9) return char( short('0') + n);
  else if (10 <= n && n <= 15)return char( short('A') + n - 10);
  else return char(0);
}

short int hexChar2dec(char c) {
  if ('0' <= c && c <= '9') return short(c - '0');
  else if ('a' <= c && c <= 'f') return (short(c - 'a') + 10);
  else if ('A' <= c && c <= 'F') return (short(c - 'A') + 10);
  else return -1;
}

/**
 * URL escape a string
 * @param URL   Orignal string
 * @return URL escaped string
 */
string escapeURL(const string &URL) {
  string result = "";
  for (unsigned int i = 0; i < URL.size(); i++) {
    char c = URL[i];
    if (
        ('0' <= c && c <= '9') ||
        ('a' <= c && c <= 'z') ||
        ('A' <= c && c <= 'Z') ||
        c == '/' || c == '.'
        ) result += c;
    else {
      int j = (short int) c;
      if (j < 0) j += 256;
      int i1, i0;
      i1 = j / 16;
      i0 = j - i1 * 16;
      result += '%';
      result += dec2hexChar(i1);
      result += dec2hexChar(i0);
    }
  }
  return result;
}

/**
 * Unescape a URL escaped string
 * @param URL   URL escaped string
 * @return Orignal string
 */
string unescapeURL(const string &URL) {
  string result = "";
  for (unsigned int i = 0; i < URL.size(); i++) {
    char c = URL[i];
    if (c != '%') result += c;
    else {
      char c1 = URL[++i];
      char c0 = URL[++i];
      int num = 0;
      num += hexChar2dec(c1) * 16 + hexChar2dec(c0);
      result += char(num);
    }
  }
  return result;
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
  if (start == string::npos || end == string::npos) {
    return str;
  }
  return str.substr(start, end - start + 1);
}

/**
 * escape "\n", "\t" to "\\n", "\\t"
 * @param str   Orignal string
 * @return Escaped string
 */
string escapeString(string str) {
  string result = "";
  size_t length = str.length();
  for (size_t pos = 0; pos < length; ++pos) {
    switch (str[pos]) {
      case '"':
        result += "\\\"";
        break;
      case '\\':
        result += "\\\\";
        break;
      case '/':
        result += "\\/";
        break;
      case '\n':
        result += "\\n";
        break;
      case '\r':
        result += "\\r";
        break;
      case '\t':
        result += "\\t";
        break;
      default:
        result += str[pos];
    }
  }
  return result;
}

/**
 * Unescape "\\n", "\\t" etc to actual \n and \t
 * also convert "\\uXXYY" to two char with ASCII XX and YY
 * @param str   Original string
 * @return Unescaped string
 */
string unescapeString(string str) {
  string result = "";
  size_t pos = 0, length = str.length();
  while (pos < length) {
    if (str[pos] == '\\') {
      ++pos;
      if (pos >= length) {
        throw Exception("Invalid string");
      }
      switch (str[pos]) {
        case '\\':
          result += '\\';
          break;
        case '\'':
          result += '\'';
          break;
        case '\"':
          result += '\"';
          break;
        case 't':
          result += '\t';
          break;
        case 'n':
          result += '\n';
          break;
        case 'r':
          result += '\r';
          break;
        case 'u':
        case 'U':
        {
          if (pos + 4 >= length) {
            throw Exception("Invalid string");
          }
          string xx = str.substr(pos + 1, 2);
          string yy = str.substr(pos + 3, 2);
          int tx, ty;
          sscanf(xx.c_str(), "%x", &tx);
          sscanf(yy.c_str(), "%x", &ty);
          if (tx) result += (unsigned char) tx;
          result += (unsigned char) ty;
          pos += 4;
          break;
        }
        default:
          throw Exception("Invalid string");
      }
    } else {
      result += str[pos];
    }
    ++pos;
  }
  return result;
}

/**
 * Use iconv to convert string between different charsets
 * @param from_charset  Initial charset
 * @param to_charset    Target charset
 * @param text          String to convert
 */
string charsetConvert(const string &from_charset, const string &to_charset,
                      const string &text) {
  iconv_t cd;
  size_t in_length= text.length() + 1;
  size_t out_length = in_length * 2;
  char * in_buffer = new char[in_length];
  char * out_buffer = new char[out_length];
  char * pin = in_buffer;
  char * pout = out_buffer;
  strcpy(in_buffer, text.c_str());

  cd = iconv_open(to_charset.c_str(), from_charset.c_str());
  if (cd == 0) {
    throw Exception("Invalid charset conversion");
  }
  memset(out_buffer, 0, out_length);
  if (iconv(cd, &pin, &in_length, &pout, &out_length) == -1) {
    throw Exception("Charset conversion Failed");
  }
  iconv_close(cd);
  string result = out_buffer;
  delete [] in_buffer;
  delete [] out_buffer;
  return result;
}

/**
 * Replace all occurencies of search to replace in subject.
 * @param subject   Original string
 * @param search    Patterns to be replaced
 * @param replace   replace string
 * @return 
 */
string replaceAll(string subject, const string& search, const string& replace) {
  size_t pos = 0;
  while ((pos = subject.find(search, pos)) != string::npos) {
    subject.replace(pos, search.length(), replace);
    pos += replace.length();
  }
  return subject;
}

string sha1String(string msg) {
  unsigned char hash[SHA_DIGEST_LENGTH];
  string hexhash;
  SHA1(reinterpret_cast<const unsigned char *>(msg.c_str()), msg.size(), hash);
  for (int i = 0; i < SHA_DIGEST_LENGTH; i++) {
    hexhash += dec2hexChar(hash[i]/16);
    hexhash += dec2hexChar(hash[i]%16);
  }
  return toLowerCase(hexhash);
}

string base64Encode(string msg) {
  gchar * base64msg = g_base64_encode(
      reinterpret_cast<const unsigned char *>(msg.c_str()), msg.size());
  string ret(base64msg);
  g_free(base64msg);
  return ret;
}
