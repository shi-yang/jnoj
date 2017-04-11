#ifndef DECODE_HTML_ENTITIES_UTF8
#define DECODE_HTML_ENTITIES_UTF8

#include <stddef.h>

extern size_t decode_html_entities_utf8(char *dest, const char *src);
/*  if `src` is `NULL`, input will be taken from `dest`, decoding
    the entities in-place

    otherwise, the output will be placed in `dest`, which should point
    to a buffer big enough to hold `strlen(src) + 1` characters, while
    `src` remains unchanged

    the function returns the length of the decoded string
 */

#endif

