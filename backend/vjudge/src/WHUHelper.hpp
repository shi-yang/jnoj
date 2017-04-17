/* 
 * File:   WHUHelper.hpp
 * Author: payper
 *
 * Created on 2014年3月26日, 上午10:10
 */

#ifndef WHUHELPER_HPP
#define WHUHELPER_HPP


#include <string.h>
#include <stdlib.h>
#include <string>

using namespace std;

namespace WHUHelper {

  int safe_add(int a, int d) {
    int c = (a & 65535) + (d & 65535);
    int b = (a >> 16) + (d >> 16) + (c >> 16);
    return (b << 16) | (c & 65535);
  }

  int bit_rol(int a, int b) {
    return (a << b) | (((unsigned int) a) >> (32 - b));
  }

  int md5_cmn(int h, int e, int d, int c, int g, int f) {
    return safe_add(bit_rol(safe_add(safe_add(e, h), safe_add(c, f)), g), d);
  }

  int md5_ff(int g, int f, int k, int j, int e, int i, int h) {
    return md5_cmn((f & k) | ((~f) & j), g, f, e, i, h);
  }

  int md5_gg(int g, int f, int k, int j, int e, int i, int h) {
    return md5_cmn((f & j) | (k & (~j)), g, f, e, i, h);
  }

  int md5_hh(int g, int f, int k, int j, int e, int i, int h) {
    return md5_cmn(f ^ k ^ j, g, f, e, i, h);
  }

  int md5_ii(int g, int f, int k, int j, int e, int i, int h) {
    return md5_cmn(k ^ (f | (~j)), g, f, e, i, h);
  }

  int mya[1000];
  int myres[10];
  int mylen;

  int * rstr2binl(string b) {
    mylen = b.length() >> 2;
    for (int c = 0; c < mylen; c++) {
      mya[c] = 0;
    }
    for (int c = 0; c < b.length() * 8; c += 8) {
      mya[c >> 5] |= (b[c / 8] & 255) << (c % 32);
    }
    return mya;
  }

  int * binl_md5(int p[], int k) {
    p[k >> 5] |= 128 << ((k) % 32);
    p[((((unsigned int) (k + 64)) >> 9) << 4) + 14] = k;
    int o = 1732584193;
    int n = -271733879;
    int m = -1732584194;
    int l = 271733878;
    for (int g = 0; g < mylen; g += 16) {
      int j = o;
      int h = n;
      int f = m;
      int e = l;
      o = md5_ff(o, n, m, l, p[g + 0], 7, -680876936);
      l = md5_ff(l, o, n, m, p[g + 1], 12, -389564586);
      m = md5_ff(m, l, o, n, p[g + 2], 17, 606105819);
      n = md5_ff(n, m, l, o, p[g + 3], 22, -1044525330);
      o = md5_ff(o, n, m, l, p[g + 4], 7, -176418897);
      l = md5_ff(l, o, n, m, p[g + 5], 12, 1200080426);
      m = md5_ff(m, l, o, n, p[g + 6], 17, -1473231341);
      n = md5_ff(n, m, l, o, p[g + 7], 22, -45705983);
      o = md5_ff(o, n, m, l, p[g + 8], 7, 1770035416);
      l = md5_ff(l, o, n, m, p[g + 9], 12, -1958414417);
      m = md5_ff(m, l, o, n, p[g + 10], 17, -42063);
      n = md5_ff(n, m, l, o, p[g + 11], 22, -1990404162);
      o = md5_ff(o, n, m, l, p[g + 12], 7, 1804603682);
      l = md5_ff(l, o, n, m, p[g + 13], 12, -40341101);
      m = md5_ff(m, l, o, n, p[g + 14], 17, -1502002290);
      n = md5_ff(n, m, l, o, p[g + 15], 22, 1236535329);
      o = md5_gg(o, n, m, l, p[g + 1], 5, -165796510);
      l = md5_gg(l, o, n, m, p[g + 6], 9, -1069501632);
      m = md5_gg(m, l, o, n, p[g + 11], 14, 643717713);
      n = md5_gg(n, m, l, o, p[g + 0], 20, -373897302);
      o = md5_gg(o, n, m, l, p[g + 5], 5, -701558691);
      l = md5_gg(l, o, n, m, p[g + 10], 9, 38016083);
      m = md5_gg(m, l, o, n, p[g + 15], 14, -660478335);
      n = md5_gg(n, m, l, o, p[g + 4], 20, -405537848);
      o = md5_gg(o, n, m, l, p[g + 9], 5, 568446438);
      l = md5_gg(l, o, n, m, p[g + 14], 9, -1019803690);
      m = md5_gg(m, l, o, n, p[g + 3], 14, -187363961);
      n = md5_gg(n, m, l, o, p[g + 8], 20, 1163531501);
      o = md5_gg(o, n, m, l, p[g + 13], 5, -1444681467);
      l = md5_gg(l, o, n, m, p[g + 2], 9, -51403784);
      m = md5_gg(m, l, o, n, p[g + 7], 14, 1735328473);
      n = md5_gg(n, m, l, o, p[g + 12], 20, -1926607734);
      o = md5_hh(o, n, m, l, p[g + 5], 4, -378558);
      l = md5_hh(l, o, n, m, p[g + 8], 11, -2022574463);
      m = md5_hh(m, l, o, n, p[g + 11], 16, 1839030562);
      n = md5_hh(n, m, l, o, p[g + 14], 23, -35309556);
      o = md5_hh(o, n, m, l, p[g + 1], 4, -1530992060);
      l = md5_hh(l, o, n, m, p[g + 4], 11, 1272893353);
      m = md5_hh(m, l, o, n, p[g + 7], 16, -155497632);
      n = md5_hh(n, m, l, o, p[g + 10], 23, -1094730640);
      o = md5_hh(o, n, m, l, p[g + 13], 4, 681279174);
      l = md5_hh(l, o, n, m, p[g + 0], 11, -358537222);
      m = md5_hh(m, l, o, n, p[g + 3], 16, -722521979);
      n = md5_hh(n, m, l, o, p[g + 6], 23, 76029189);
      o = md5_hh(o, n, m, l, p[g + 9], 4, -640364487);
      l = md5_hh(l, o, n, m, p[g + 12], 11, -421815835);
      m = md5_hh(m, l, o, n, p[g + 15], 16, 530742520);
      n = md5_hh(n, m, l, o, p[g + 2], 23, -995338651);
      o = md5_ii(o, n, m, l, p[g + 0], 6, -198630844);
      l = md5_ii(l, o, n, m, p[g + 7], 10, 1126891415);
      m = md5_ii(m, l, o, n, p[g + 14], 15, -1416354905);
      n = md5_ii(n, m, l, o, p[g + 5], 21, -57434055);
      o = md5_ii(o, n, m, l, p[g + 12], 6, 1700485571);
      l = md5_ii(l, o, n, m, p[g + 3], 10, -1894986606);
      m = md5_ii(m, l, o, n, p[g + 10], 15, -1051523);
      n = md5_ii(n, m, l, o, p[g + 1], 21, -2054922799);
      o = md5_ii(o, n, m, l, p[g + 8], 6, 1873313359);
      l = md5_ii(l, o, n, m, p[g + 15], 10, -30611744);
      m = md5_ii(m, l, o, n, p[g + 6], 15, -1560198380);
      n = md5_ii(n, m, l, o, p[g + 13], 21, 1309151649);
      o = md5_ii(o, n, m, l, p[g + 4], 6, -145523070);
      l = md5_ii(l, o, n, m, p[g + 11], 10, -1120210379);
      m = md5_ii(m, l, o, n, p[g + 2], 15, 718787259);
      n = md5_ii(n, m, l, o, p[g + 9], 21, -343485551);
      o = safe_add(o, j);
      n = safe_add(n, h);
      m = safe_add(m, f);
      l = safe_add(l, e);
    }
    myres[0] = o;
    myres[1] = n;
    myres[2] = m;
    myres[3] = l;
    return myres;
    //return Array(o, n, m, l)
  }

  string binl2rstr(int b[]) {
    string a = "";
    for (int c = 0; c < 4 * 32; c += 8) {
      a += (char) ((((unsigned int) b[c >> 5]) >> (c % 32)) & 255);
    }
    return a;
  }

  string rstr_md5(string a) {
    return binl2rstr(binl_md5(rstr2binl(a), a.length() * 8));
  }

  string rstr2hex(string c) {
    string f = "0123456789abcdef";
    string b = "";
    int a;
    for (int d = 0; d < c.length(); d++) {
      a = c[d];
      b += f[(a >> 4) & 15];
      b += f[(a & 15)];
    }
    return b;
  }

  string hex_md5(string a) {
    memset(mya, 0, sizeof (mya));
    memset(myres, 0, sizeof (myres));
    mylen = 0;
    return rstr2hex(rstr_md5(a));
  }

}



#endif /* WHUHELPER_HPP */

