#ifndef RESTRICT_SYSCALLS_H_INCLUDED
#define RESTRICT_SYSCALLS_H_INCLUDED

bool syscalls_other[500] = {false};
bool syscalls_java[500] = {false};
bool syscalls_csharp[500] = {false};
bool vmlang[500] = {false};
string src_extension[500] = {""};
string exc_extension[500] = {""};

void init_error() {
    syscalls_other[SYS__sysctl] = true;
    /*syscalls_other[SYS_access]=true;*/syscalls_other[SYS_chdir] = true;
    syscalls_other[SYS_chmod] = true;
    syscalls_other[SYS_chown] = true;
    syscalls_other[SYS_chroot] = true;
    syscalls_other[SYS_clone] = true; /*syscalls_other[SYS_close]=true;*/
    syscalls_other[SYS_creat] = true;
    syscalls_other[SYS_create_module] = true;
    syscalls_other[SYS_delete_module] = true;
    syscalls_other[SYS_fork] = true;
    /*syscalls_other[SYS_getuid]=true;syscalls_other[SYS_getpid]=true;*/
    syscalls_other[SYS_getpgrp] = true;
    syscalls_other[SYS_kill] = true;
    syscalls_other[SYS_mkdir] = true;
    syscalls_other[SYS_mknod] = true;
    syscalls_other[SYS_mount] = true; /*syscalls_other[SYS_open]=true;*/
    syscalls_other[SYS_rmdir] = true;
    syscalls_other[SYS_ptrace] = true;
    syscalls_other[SYS_reboot] = true;
    syscalls_other[SYS_rename] = true;
    syscalls_other[SYS_restart_syscall] = true;
    syscalls_other[SYS_select] = true;
    syscalls_other[SYS_setgid] = true;
    syscalls_other[SYS_setitimer] = true;
    syscalls_other[SYS_setgroups] = true;
    syscalls_other[SYS_sethostname] = true;
    syscalls_other[SYS_setrlimit] = true;
    syscalls_other[SYS_setuid] = true;
    syscalls_other[SYS_settimeofday] = true;
    syscalls_other[SYS_tkill] = true;
    syscalls_other[SYS_setrlimit] = true;
    syscalls_other[SYS_setuid] = true;
    syscalls_other[SYS_vfork] = true;
    syscalls_other[SYS_vhangup] = true;
    syscalls_other[SYS_vserver] = true;
    syscalls_other[SYS_wait4] = true;
    syscalls_other[SYS_clock_nanosleep] = true;
    syscalls_other[SYS_nanosleep] = true;
    syscalls_other[SYS_pause] = true; /*syscalls_other[SYS_arch_prctl]=true;*/
    /*syscalls_other[SYS_write]=true;syscalls_other[SYS_writev]=true;*/
#ifndef __i386__
    syscalls_other[SYS_accept] = true;
    syscalls_other[SYS_bind] = true;
    syscalls_other[SYS_connect] = true;
    syscalls_other[SYS_listen] = true;
    syscalls_other[SYS_socket] = true;
#else
    syscalls_other[SYS_signal] = true;
    syscalls_other[SYS_waitpid] = true;
    syscalls_other[SYS_nice] = true;
    syscalls_other[SYS_waitpid] = true;
    syscalls_other[SYS_umount] = true;
    syscalls_other[SYS_socketcall] = true;
#endif

    syscalls_csharp[SYS__sysctl] = true;
    /*syscalls_csharp[SYS_access]=true;*/
    syscalls_csharp[SYS_chdir] = true;
    syscalls_csharp[SYS_chmod] = true;
    syscalls_csharp[SYS_chown] = true;
    syscalls_csharp[SYS_chroot] = true;
    /*syscalls_csharp[SYS_clone]=true;syscalls_csharp[SYS_close]=true;*/
    syscalls_csharp[SYS_creat] = true;
    syscalls_csharp[SYS_create_module] = true;
    syscalls_csharp[SYS_delete_module] = true;
    syscalls_csharp[SYS_fork] = true;
    /*syscalls_csharp[SYS_getuid]=true;syscalls_csharp[SYS_getpid]=true;*/
    syscalls_csharp[SYS_getpgrp] = true;
    syscalls_csharp[SYS_kill] = true;
    /*syscalls_csharp[SYS_mkdir]=true;*/
    syscalls_csharp[SYS_mknod] = true;
    syscalls_csharp[SYS_mount] = true; /*syscalls_csharp[SYS_open]=true;*/
    syscalls_csharp[SYS_rmdir] = true;
    syscalls_csharp[SYS_ptrace] = true;
    syscalls_csharp[SYS_reboot] = true;
    syscalls_csharp[SYS_rename] = true;
    syscalls_csharp[SYS_restart_syscall] = true;
    syscalls_csharp[SYS_select] = true;
    syscalls_csharp[SYS_setgid] = true;
    syscalls_csharp[SYS_setitimer] = true;
    syscalls_csharp[SYS_setgroups] = true;
    syscalls_csharp[SYS_sethostname] = true;
    syscalls_csharp[SYS_setrlimit] = true;
    syscalls_csharp[SYS_setuid] = true;
    syscalls_csharp[SYS_settimeofday] = true;
    syscalls_csharp[SYS_tkill] = true;
    syscalls_csharp[SYS_setrlimit] = true;
    syscalls_csharp[SYS_setuid] = true;
    syscalls_csharp[SYS_vfork] = true;
    syscalls_csharp[SYS_vhangup] = true;
    syscalls_csharp[SYS_vserver] = true;
    syscalls_csharp[SYS_wait4] = true;
    syscalls_csharp[SYS_clock_nanosleep] = true;
    syscalls_csharp[SYS_nanosleep] = true;
    syscalls_csharp[SYS_pause] = true; /*syscalls_csharp[SYS_arch_prctl]=true;*/
    /*syscalls_csharp[SYS_write]=true;syscalls_csharp[SYS_writev]=true;*/
#ifndef __i386__
    syscalls_csharp[SYS_accept] = true;
    syscalls_csharp[SYS_bind] = true;
    /*syscalls_csharp[SYS_connect]=true;*/syscalls_csharp[SYS_listen] = true;
    /*syscalls_csharp[SYS_socket]=true;*/
#else
    syscalls_csharp[SYS_signal] = true;
    syscalls_csharp[SYS_waitpid] = true;
    syscalls_csharp[SYS_nice] = true;
    syscalls_csharp[SYS_waitpid] = true;
    syscalls_csharp[SYS_umount] = true;
    syscalls_csharp[SYS_socketcall] = true;
#endif

    syscalls_java[SYS__sysctl] = true;
    syscalls_java[SYS_chdir] = true;
    syscalls_java[SYS_chmod] = true;
    syscalls_java[SYS_chown] = true;
    syscalls_java[SYS_chroot] = true;
    syscalls_java[SYS_creat] = true;
    syscalls_java[SYS_create_module] = true;
    syscalls_java[SYS_delete_module] = true;
    syscalls_java[SYS_fork] = true;
    /*syscalls_java[SYS_getuid]=true;syscalls_java[SYS_getpid]=true;*/
    syscalls_java[SYS_getpgrp] = true;
    syscalls_java[SYS_kill] = true;
    syscalls_java[SYS_mkdir] = true;
    syscalls_java[SYS_mknod] = true;
    syscalls_java[SYS_mount] = true;
    syscalls_java[SYS_rmdir] = true;
    syscalls_java[SYS_ptrace] = true;
    syscalls_java[SYS_reboot] = true;
    syscalls_java[SYS_rename] = true;
    syscalls_java[SYS_restart_syscall] = true;
    syscalls_java[SYS_select] = true;
    syscalls_java[SYS_setgid] = true;
    syscalls_java[SYS_setitimer] = true;
    syscalls_java[SYS_setgroups] = true;
    syscalls_java[SYS_sethostname] = true;
    syscalls_java[SYS_setrlimit] = true;
    syscalls_java[SYS_setuid] = true;
    syscalls_java[SYS_settimeofday] = true;
    syscalls_java[SYS_tkill] = true;
    syscalls_java[SYS_setrlimit] = true;
    syscalls_java[SYS_setuid] = true;
    syscalls_java[SYS_vfork] = true;
    syscalls_java[SYS_vhangup] = true;
    syscalls_java[SYS_vserver] = true;
    syscalls_java[SYS_wait4] = true;
    syscalls_java[SYS_clock_nanosleep] = true;
    syscalls_java[SYS_nanosleep] = true;
    syscalls_java[SYS_pause] = true; /*syscalls_java[SYS_arch_prctl]=true;*/
    /*syscalls_java[SYS_write]=true;syscalls_java[SYS_writev]=true;*/
#ifndef __i386__
    syscalls_java[SYS_accept] = true;
    syscalls_java[SYS_bind] = true;
    syscalls_java[SYS_connect] = true;
    syscalls_java[SYS_listen] = true;
    syscalls_java[SYS_socket] = true;
#else
    syscalls_java[SYS_signal] = true;
    syscalls_java[SYS_waitpid] = true;
    syscalls_java[SYS_nice] = true;
    syscalls_java[SYS_waitpid] = true;
    syscalls_java[SYS_umount] = true;
    syscalls_java[SYS_socketcall] = true;
#endif
}

void init_others() {
    vmlang[JAVALANG] = vmlang[PY2LANG] = vmlang[PY3LANG] =
    vmlang[PERLLANG] = vmlang[RUBYLANG] = true;

    src_extension[CPPLANG] = src_extension[CPP11LANG] = ".cpp";
    src_extension[CLANG] = ".c";
    src_extension[JAVALANG] = ".java";
    src_extension[FPASLANG] = ".pas";
    src_extension[PY2LANG] = src_extension[PY3LANG] = ".py";
    src_extension[CSLANG] = ".cs";
    src_extension[FORTLANG] = ".f";
    src_extension[PERLLANG] = ".pl";
    src_extension[RUBYLANG] = ".rb";
    src_extension[ADALANG] = ".ada";
    src_extension[SMLLANG] = ".sml";
    src_extension[CLANGLANG] = ".c";
    src_extension[CLANGPPLANG] = ".cpp";


    exc_extension[CPPLANG] = exc_extension[CPP11LANG] = ".out";
    exc_extension[CLANG] = ".out";
    exc_extension[JAVALANG] = ".class";
    exc_extension[FPASLANG] = ".out";
    exc_extension[PY2LANG] = exc_extension[PY3LANG] = ".pyc";
    exc_extension[CSLANG] = ".exe";
    exc_extension[FORTLANG] = ".out";
    exc_extension[SMLLANG] = ".out";
    exc_extension[CLANGPPLANG] = ".out";
    exc_extension[CLANGLANG] = ".out";


}

#endif // RESTRICT_SYSCALLS_H_INCLUDED
