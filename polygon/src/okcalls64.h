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
int LANG_CV[256] = {0,1,2,3,5,8,9,10,11,12,13,14,16,20,21,39,56,59,63,89,99,158,186,218,231,234,257,262,268,275,292,302,318,334,511,        
	SYS_write, SYS_mprotect, SYS_munmap, SYS_brk, SYS_arch_prctl, SYS_pread64, SYS_open, SYS_writev,
        SYS_time, SYS_futex, SYS_set_thread_area, SYS_access, SYS_clock_gettime, SYS_exit_group, SYS_mq_open,
        SYS_ioprio_get, SYS_unshare, SYS_set_robust_list, SYS_splice, SYS_close, SYS_stat, SYS_fstat, SYS_execve,
        SYS_uname, SYS_lseek, SYS_readlink, SYS_mmap, SYS_sysinfo, 0 };

//java
int LANG_JV[256] = {
        0, 262,318,334,435, SYS_mprotect, SYS_getuid, SYS_getgid, SYS_geteuid, SYS_getegid, SYS_munmap, SYS_getppid, SYS_getpgrp,
        SYS_brk, SYS_rt_sigaction, SYS_rt_sigprocmask, SYS_prctl, SYS_arch_prctl, SYS_ioctl, SYS_pread64, SYS_open,
        SYS_futex, SYS_set_thread_area, SYS_access, SYS_getdents64, SYS_set_tid_address, SYS_pipe, SYS_exit_group,
        SYS_openat, SYS_set_robust_list, SYS_close, SYS_prlimit64, SYS_dup2, SYS_getpid, SYS_stat, SYS_fstat, SYS_clone,
        SYS_execve, SYS_lstat, SYS_wait4, SYS_uname, SYS_fcntl, SYS_getcwd, SYS_lseek, SYS_readlink, SYS_mmap,
        SYS_getrlimit, 0 };

//python
int LANG_YV[256] = {0,2,3,4,5,6,8,9,10,11,12,13,14,16,21,32,59,72,78,79,89,97,99,102,104,107,108,131,158,218,228,231,272,273,318,39,99,302,99,32,72,131,1,202,257,41, 42, 146, SYS_mremap, 158, 117, 60, 39, 102, 191,
                    		SYS_access, SYS_arch_prctl, SYS_brk, SYS_close, SYS_execve,
                    		SYS_exit_group, SYS_fcntl, SYS_fstat, SYS_futex, SYS_getcwd,
                    		SYS_getdents, SYS_getegid, SYS_geteuid, SYS_getgid, SYS_getrlimit,
                    		SYS_getuid, SYS_ioctl, SYS_lseek, SYS_lstat, SYS_mmap, SYS_mprotect,
                    		SYS_munmap, SYS_open, SYS_read, SYS_readlink, SYS_rt_sigaction,
                    		SYS_rt_sigprocmask, SYS_set_robust_list, SYS_set_tid_address, SYS_stat,
                    		SYS_write, 0 };
//pascal
int LANG_PAS[256] = {
        0, SYS_write, SYS_mprotect, SYS_munmap, SYS_brk, SYS_rt_sigaction, SYS_arch_prctl, SYS_ioctl,
        SYS_pread64, SYS_getxattr, SYS_open, SYS_time, SYS_set_thread_area, SYS_exit_group, SYS_ioprio_get, SYS_close,
        SYS_stat, SYS_execve, SYS_uname, SYS_readlink, SYS_mmap, SYS_getrlimit, 0 };

struct ok_call {
	int * call;
};
struct ok_call ok_calls[] = {
	{LANG_CV},
	{LANG_CV},
	{LANG_JV},
	{LANG_YV},
	{LANG_PAS}
};