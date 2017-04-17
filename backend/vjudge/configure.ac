#                                               -*- Autoconf -*-
# Process this file with autoconf to produce a configure script.

AC_PREREQ([2.69])
AC_INIT([bnuoj-vjudge], [2.0.0], [crccw@moonux.org])
AC_CONFIG_SRCDIR([src/vjudge.cpp])
AC_CONFIG_HEADER([config.h])
AM_MAINTAINER_MODE([disable])
AM_INIT_AUTOMAKE

# Checks for programs.
AC_PROG_CXX
AC_PROG_CC

# Checks for libraries.
AC_CHECK_LIB([pthread], [pthread_create], , AC_MSG_ERROR([pthread not found]))
AC_CHECK_LIB([re2], [main], , AC_MSG_ERROR([re2 not found]))
AC_CHECK_LIB([curl], [curl_easy_init], , AC_MSG_ERROR([curl not found]))
AC_CHECK_LIB([jpeg], [jpeg_stdio_src], , AC_MSG_ERROR([jpeg not found]))
AC_CHECK_LIB([crypto], [SHA1], , AC_MSG_ERROR([crypto not found]))
PKG_CHECK_MODULES([DEPENDENCIES], [glib-2.0 htmlcxx])
AC_SUBST([DEPENDENCIES_LIBS])
AC_SUBST([DEPENDENCIES_CFLAGS])


# Checks for header files.
AC_CHECK_HEADERS([arpa/inet.h inttypes.h limits.h netdb.h netinet/in.h stddef.h\
                  stdint.h stdlib.h string.h strings.h sys/socket.h sys/time.h\
                  unistd.h wchar.h])

# Checks for typedefs, structures, and compiler characteristics.
AC_CHECK_HEADER_STDBOOL
AC_C_INLINE
AC_TYPE_INT16_T
AC_TYPE_INT32_T
AC_TYPE_INT64_T
AC_TYPE_INT8_T
AC_TYPE_SIZE_T
AC_TYPE_SSIZE_T
AC_TYPE_UINT16_T
AC_TYPE_UINT32_T
AC_TYPE_UINT64_T
AC_TYPE_UINT8_T
AC_CHECK_TYPES([ptrdiff_t])

# Checks for library functions.
AC_FUNC_MALLOC
AC_FUNC_REALLOC
AC_CHECK_FUNCS([bzero memmove memset select socket strcasecmp strchr strncasecmp strtoul])

AC_OUTPUT([Makefile src/Makefile])
