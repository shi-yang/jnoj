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
int LANG_CV[256] = {0,1,2,3,4,5,8,9,11,12,20,21,59,63,89,99,158,202,231,240,272,511, SYS_time, SYS_read, SYS_uname, SYS_write, SYS_open,
                    		SYS_close, SYS_execve, SYS_access, SYS_brk, SYS_munmap, SYS_mprotect,
                    		SYS_mmap, SYS_fstat, SYS_set_thread_area, 252, SYS_arch_prctl, 0 };
//java
int LANG_JV[256] = {0,2,3,4,5,9,10,11,12,13,14,21,56,59,89,97,104,158,202,218,231,273,257, 
		61, 22, 6, 33, 8, 13, 16, 111, 110, 39, 79, 302, SYS_fcntl,
		SYS_getdents64, SYS_getrlimit, SYS_rt_sigprocmask, SYS_futex, SYS_read,
		SYS_mmap, SYS_stat, SYS_open, SYS_close, SYS_execve, SYS_access,
		SYS_brk, SYS_readlink, SYS_munmap, SYS_close, SYS_uname, SYS_clone,
		SYS_uname, SYS_mprotect, SYS_rt_sigaction, SYS_getrlimit, SYS_fstat,
		SYS_getuid, SYS_getgid, SYS_geteuid, SYS_getegid, SYS_set_thread_area,
		SYS_set_tid_address, SYS_set_robust_list, SYS_exit_group, 158, 0 };
//python
int LANG_YV[256] = {0,2,3,4,5,6,8,9,10,11,12,13,14,16,21,32,59,72,78,79,89,97,99,102,104,107,108,131,158,218,228,231,272,273,318,39,99,302,99,32,72,131,1,202,257,41, 42, 146, SYS_mremap, 158, 117, 60, 39, 102, 191,
                    		SYS_access, SYS_arch_prctl, SYS_brk, SYS_close, SYS_execve,
                    		SYS_exit_group, SYS_fcntl, SYS_fstat, SYS_futex, SYS_getcwd,
                    		SYS_getdents, SYS_getegid, SYS_geteuid, SYS_getgid, SYS_getrlimit,
                    		SYS_getuid, SYS_ioctl, SYS_lseek, SYS_lstat, SYS_mmap, SYS_mprotect,
                    		SYS_munmap, SYS_open, SYS_read, SYS_readlink, SYS_rt_sigaction,
                    		SYS_rt_sigprocmask, SYS_set_robust_list, SYS_set_tid_address, SYS_stat,
                    		SYS_write, 0 };

struct ok_call {
	int * call;
};
struct ok_call ok_calls[] = {
	{LANG_CV},
	{LANG_CV},
	{LANG_JV},
	{LANG_YV}
};