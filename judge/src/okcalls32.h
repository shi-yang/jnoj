/*
 * 
 *
 * This file is part of HUSTOJ.
 *
 * HUSTOJ is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * HUSTOJ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HUSTOJ. if not, see <http://www.gnu.org/licenses/>.
 */
//c & c++
int LANG_CV[256] = { 85, 8,140, SYS_time, SYS_read, SYS_uname, SYS_write, SYS_open,
		SYS_close, SYS_execve, SYS_access, SYS_brk, SYS_munmap, SYS_mprotect,
		SYS_mmap2, SYS_fstat64, SYS_set_thread_area, 252, 0 };
//java
int LANG_JV[256] = { 295, SYS_fcntl64, SYS_getdents64, SYS_ugetrlimit,
		SYS_rt_sigprocmask, SYS_futex, SYS_read, SYS_mmap2, SYS_stat64,
		SYS_open, SYS_close, SYS_execve, SYS_access, SYS_brk, SYS_readlink,
		SYS_munmap, SYS_close, SYS_uname, SYS_clone, SYS_uname, SYS_mprotect,
		SYS_rt_sigaction, SYS_sigprocmask, SYS_getrlimit, SYS_fstat64,
		SYS_getuid32, SYS_getgid32, SYS_geteuid32, SYS_getegid32,
		SYS_set_thread_area, SYS_set_tid_address, SYS_set_robust_list,
		SYS_exit_group, 0 };
//python
int LANG_YV[256]={3,4,5,6,11,33,45,54,85,116,122,125,140,174,175,183,
		191,192,195,196,197,199,200,201,202,220,243,252,258,
		311,13,41,91,102,186,221,240,295,0};
//php
int LANG_PHV[256] = {3,4,5,6,11,13,33,45,54,78,91,122,125,140,174,175,183,191,192,195,
		     196,197,240,243,252,258,295,311,146, 158, 117, 60, 39, 102, SYS_access, SYS_brk,
		SYS_clone, SYS_close, SYS_execve, SYS_exit_group, SYS_fcntl64,
		SYS_fstat64, SYS_futex, SYS_getcwd, SYS_getdents64, SYS_getrlimit,
		SYS_gettimeofday, SYS_ioctl, SYS__llseek, SYS_lstat64, SYS_mmap2,
		SYS_mprotect, SYS_munmap, SYS_open, SYS_read, SYS_readlink,
		SYS_rt_sigaction, SYS_rt_sigprocmask, SYS_set_robust_list,
		SYS_set_thread_area, SYS_set_tid_address, SYS_stat64, SYS_time,
		SYS_uname, SYS_write, 0 };
		
struct ok_call {
	int * call;
};
struct ok_call ok_calls[] = {
	{LANG_CV},
	{LANG_CV},
	{LANG_JV},
	{LANG_YV}
};