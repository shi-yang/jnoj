#                                               -*- Autoconf -*-
# Process this file with autoconf to produce a configure script.

AC_PREREQ([2.69])
AC_INIT([bnuoj-judger], [2.0.0], [crccw@moonux.org])
AC_CONFIG_SRCDIR([src/chaclient.h])
AC_CONFIG_HEADERS([config.h])
AM_MAINTAINER_MODE([disable])
AM_INIT_AUTOMAKE

# Checks for programs.
AC_PROG_CXX
AC_PROG_CC

# Checks for libraries.

# Checks for header files.
AC_CHECK_HEADERS([arpa/inet.h inttypes.h limits.h netdb.h netinet/in.h stdint.h\
                  stdlib.h string.h strings.h sys/socket.h sys/time.h unistd.h\
                  wchar.h])

# Checks for typedefs, structures, and compiler characteristics.
AC_CHECK_HEADER_STDBOOL
AC_C_INLINE
AC_TYPE_INT16_T
AC_TYPE_INT32_T
AC_TYPE_INT64_T
AC_TYPE_INT8_T
AC_TYPE_PID_T
AC_TYPE_SIZE_T
AC_TYPE_SSIZE_T
AC_TYPE_UINT16_T
AC_TYPE_UINT32_T
AC_TYPE_UINT64_T
AC_TYPE_UINT8_T
AC_CHECK_TYPES([ptrdiff_t])

# Checks for library functions.
AC_FUNC_FORK
AC_FUNC_MALLOC
AC_FUNC_REALLOC
AC_CHECK_FUNCS([bzero dup2 getpagesize gettimeofday memmove memset socket\
                strchr])

AC_OUTPUT([Makefile src/Makefile])
