#include <cerrno>

#include "Program.h"
#include "program_init.h"
#include "Logger.h"

bool Program::para_inited = false;
extern int GENERAL_COMPILE_TIME, GENERATOR_RUN_TIME, GENERATOR_RUN_MEMORY,
        VMLANG_MULTIPLIER, MAX_OUTPUT_LIMIT, EXTRA_RUNTIME;
extern int lowprivid;

extern string tmpnam();

Program::Program() {
    //ctor
    compiled = false;
    result = "";
    base_filename = "";
    time_used = memory_used = 0;
    compile_time_limit = GENERAL_COMPILE_TIME;
    check_exit_status = false;
    if (!para_inited) {
        init_error();
        init_others();
        para_inited = true;
    }
}

Program::~Program() {
    if (base_filename != "") Deletefile(base_filename + "*");
    if (Checkfile(src_filename)) Deletefile(src_filename);
    if (Checkfile(exc_filename)) Deletefile(exc_filename);
    if (Checkfile(out_filename)) Deletefile(out_filename);
    if (Checkfile(err_filename)) Deletefile(err_filename);
    if (Checkfile(res_filename)) Deletefile(res_filename);
    if (Checkfile(in_filename)) Deletefile(in_filename);
    if (Checkfile(cinfo_filename)) Deletefile(cinfo_filename);
}

void Program::Savetofile(string filename, string content) {
    FILE *fp = fopen(filename.c_str(), "w");
    while (fp == NULL) fp = fopen(filename.c_str(), "w");
    if (fp != NULL) {
        fputs(content.c_str(), fp);
        //fprintf(fp,"%s",content.c_str());
        fclose(fp);
    }
}

string Program::Loadallfromfile(string filename, int limit) {
    int lines = 0, tried = 0;
    string res = "", tmps;
    fstream fin(filename.c_str(), fstream::in);
    while (fin.fail() && tried < 10) {
        tried++;
        fin.open(filename.c_str(), fstream::in);
        return res;
    }
    if (fin.fail()) return res;
    while (getline(fin, tmps)) {
        if (res != "") res += "\n";
        res += tmps;
        lines++;
        if (fin.eof()) break;
        if (limit != -1 && lines > limit) break;
        //getline(fin,tmps);
    }
    fin.close();
    return res;
}

void Program::Deletefile(string filename) {
    if (filename == "*") return;
    system(((string) "rm " + filename).c_str());
}

bool Program::Checkfile(string filename) {
    int tried = 0;
    if (filename == "") return false;
    FILE *fp = fopen(filename.c_str(), "r");
    while (fp == NULL && tried < 5) {
        tried++;
        fp = fopen(filename.c_str(), "r");
    }
    if (fp != NULL) {
        fclose(fp);
        return true;
    }
    return false;
}

string Program::Inttostring(int x) {
    char tt[100];
    sprintf(tt, "%d", x);
    string t = tt;
    return t;
}

int Program::Compile(string source, int language) {
    cinfo_filename = CONFIG->GetTmpfile_path() + tmpnam() + ".txt";
    if (language != JAVALANG)
        base_filename = CONFIG->GetTmpfile_path() + tmpnam();
    else base_filename = CONFIG->GetTmpfile_path() + "Main";
    exc_filename = base_filename + exc_extension[language];
    src_filename = base_filename + src_extension[language];
    Savetofile(src_filename, source);
    LOG("Compiling " + src_filename);
    struct rlimit compile_limit;
    compile_limit.rlim_max = compile_limit.rlim_cur = compile_time_limit;
    int cpid;
    if ((cpid = fork()) == 0) {
        LOGGER->addIdentifier(getpid(), "Compiler");
        usleep(50000);
        freopen(cinfo_filename.c_str(), "w", stderr);
        setrlimit(RLIMIT_CPU, &compile_limit);
        setuid(lowprivid);
        switch (language) {
            case CPPLANG:
                execl("/usr/bin/g++", "g++", src_filename.c_str(), "-o",
                      exc_filename.c_str(), "-O", "-fno-asm", "-Wall", "-lm", NULL);
                break;
            case CPP11LANG:
                execl("/usr/bin/g++", "g++", src_filename.c_str(), "-o",
                      exc_filename.c_str(), "-O", "-fno-asm", "-Wall", "-lm",
                      "--std=c++11", NULL);
                break;
            case CLANG:
                execl("/usr/bin/gcc", "gcc", src_filename.c_str(), "-o",
                      exc_filename.c_str(), "-O", "-fno-asm", "-Wall", "-lm", NULL);
                break;
            case CLANGPPLANG:
                execl("/usr/bin/clang++", "clang++", src_filename.c_str(), "-o",
                      exc_filename.c_str(), "-O", "-fno-asm", "-Wall", "-lm", NULL);
                break;
            case CLANGLANG:
                execl("/usr/bin/clang", "clang", src_filename.c_str(), "-o",
                      exc_filename.c_str(), "-O", "-fno-asm", "-Wall", "-lm", NULL);
                break;
            case FORTLANG:
                execl("/usr/bin/gfortran", "gfortran", src_filename.c_str(), "-o",
                      exc_filename.c_str(), "-Wall", NULL);
                break;
            case JAVALANG:
                execl("/usr/bin/javac", "javac", "-g:none", "-Xlint",
                      src_filename.c_str(), NULL);
                break;
            case FPASLANG:
                execl("/usr/bin/fpc", "fpc", src_filename.c_str(),
                      ("-o" + exc_filename).c_str(), "-Co", "-Cr", "-Ct", "-Ci", NULL);
                break;
            case PY2LANG:
                execl(
                        "/usr/bin/python2", "python2", "-c",
                        ((string) "import py_compile; py_compile.compile('" +
                         src_filename + "')").c_str(),
                        NULL);
                break;
            case PY3LANG:
                execl(
                        "/usr/bin/python3", "python3", "-c",
                        ((string) "import py_compile; py_compile.compile('" +
                         src_filename + "', cfile='" + exc_filename + "')").c_str(),
                        NULL);
                break;
            case CSLANG:
                execl("/usr/bin/mcs", "mcs", src_filename.c_str(),
                      ((string) "-out:" + exc_filename).c_str(), NULL);
                break;
            case ADALANG:
                execl("/usr/bin/gnatmake", "gnatmake",
                      src_filename.c_str(), NULL);
                break;
            case SMLLANG:
                execl("/usr/bin/mlton", "mlton", src_filename.c_str(), "-output",
                      exc_filename.c_str(), NULL);
                break;
            case PERLLANG:
                break;
            case RUBYLANG:
                break;
            default:
                return -1;
        }
        exit(0);
    } else {
        LOG("Compile Child Process: " + Inttostring(cpid));
        LOG("Compile time limit: " + Inttostring(compile_time_limit));
        int cstat, tused;
        struct timeval case_startv, case_nowv;
        struct timezone case_startz, case_nowz;
        gettimeofday(&case_startv, &case_startz);
        int cnt = -1;
        cstat = -1;
        while (1) {
            usleep(50000);
            cnt++;
            gettimeofday(&case_nowv, &case_nowz);
            tused = case_nowv.tv_sec - case_startv.tv_sec;
            if (cnt % 20 == 0) LOG("Compiling Used: " + Inttostring(tused));
            if (waitpid(cpid, &cstat, WNOHANG) == 0) {
                if (tused > compile_time_limit) {
                    LOG("Time too much!");
                    LOG("kill `pstree -p " + Inttostring(cpid) +
                        " | sed 's/(/\\n(/g' | grep '(' | "
                                "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`");
                    system(((string) "kill `pstree -p " + Inttostring(cpid) +
                            " | sed 's/(/\\n(/g' | grep '(' | "
                                    "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`").c_str());
                    waitpid(cpid, &cstat, 0);
                    return 2;
                }
            } else if (WIFSIGNALED(cstat) && WTERMSIG(cstat) != 0) {
                LOG("Something is wrong.");
                LOG("kill `pstree -p " + Inttostring(cpid) +
                    " | sed 's/(/\\n(/g' | grep '(' | "
                            "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`");
                system(((string) "kill `pstree -p " + Inttostring(cpid) +
                        " | sed 's/(/\\n(/g' | grep '(' | "
                                "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`").c_str());
                waitpid(cpid, &cstat, 0);
                return 2;
            } else if (WIFEXITED(cstat)) {
                waitpid(cpid, &cstat, 0);
                LOG("Compiled");
                break;
            }
        }
        system(((string) "kill `pstree -p " + Inttostring(cpid) +
                " | sed 's/(/\\n(/g' | grep '(' | "
                        "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`").c_str());
    }

    if (!Checkfile(exc_filename) && (language == CPPLANG || language == CLANG ||
                                     language == FPASLANG || language == FORTLANG || language == SMLLANG ||
                                     language == CSLANG || language == JAVALANG || language == PY2LANG ||
                                     language == PY3LANG || language == CPP11LANG || language == CLANGPPLANG ||
                                     language == CLANGLANG || language == ADALANG)) {
        return 1;
    }
    return 0;
}

int Program::Excution() {
    if (has_input && !Checkfile(in_filename)) return -1;
    system(((string) "chmod +x " + exc_filename).c_str());
    res_filename = tmpnam();
    struct rlimit runtime;
    runtime.rlim_max = runtime.rlim_cur =
            (total_time_limit - time_used + 999) / 1000 + EXTRA_RUNTIME;
    pid_t wid;
    if ((wid = fork()) == 0) {
        LOGGER->addIdentifier(getpid(), "Sandbox");
        string exc_command;
        pid_t pid;
        int runstat;
        bool excuted = false;
        struct rusage rinfo;
        setrlimit(RLIMIT_CPU, &runtime);
        struct user_regs_struct reg;
        struct rlimit time_limit, output_limit, nproc_limit;

        time_limit.rlim_cur = case_time_limit < total_time_limit - time_used ?
                              case_time_limit : total_time_limit - time_used;
        time_limit.rlim_cur = (time_limit.rlim_cur + 999) / 1000;
        if (time_limit.rlim_cur <= 0) time_limit.rlim_cur = 1;
        time_limit.rlim_max = time_limit.rlim_cur + 1;

        nproc_limit.rlim_cur = nproc_limit.rlim_max = 1;

        if ((pid = fork()) == 0) {
            LOGGER->addIdentifier(getpid(), "Runner");
            if (has_input) freopen(in_filename.c_str(), "r", stdin);
            freopen(out_filename.c_str(), "w", stdout);
            freopen(err_filename.c_str(), "w", stderr);
            LOG((string) "Time limit for this program is " +
                Inttostring(time_limit.rlim_cur));
            setrlimit(RLIMIT_CPU, &time_limit);
            output_limit.rlim_max = output_limit.rlim_cur =
                    MAX_OUTPUT_LIMIT * 1024 * 1024;
            setrlimit(RLIMIT_FSIZE, &output_limit);
            setrlimit(RLIMIT_NPROC, &nproc_limit);

            setuid(lowprivid);
            ptrace(PTRACE_TRACEME, 0, NULL, NULL);

            switch (language) {
                case CPPLANG:
                case CPP11LANG:
                case CLANG:
                case CLANGLANG:
                case CLANGPPLANG:
                case FORTLANG:
                case FPASLANG:
                case SMLLANG:
                case ADALANG:
                    exc_command = (string) "./" + exc_filename;
                    execl(exc_command.c_str(), exc_command.c_str(), NULL);
                    break;
                case JAVALANG:
                    execl("/usr/bin/java", "java", "-Djava.security.manager",
                          "-Djava.security.policy=java.policy", "-client", "-cp",
                          CONFIG->GetTmpfile_path().c_str(), "Main", NULL);
                    break;
                case CSLANG:
                    execl("/usr/bin/mono", "mono", exc_filename.c_str(), NULL);
                    break;
                case PERLLANG:
                    execl("/usr/bin/perl", "perl", src_filename.c_str(), "-W", NULL);
                case RUBYLANG:
                    execl("/usr/bin/ruby", "ruby", src_filename.c_str(), "-W", NULL);
                    break;
                case PY2LANG:
                    execl("/usr/bin/python2", "python2", exc_filename.c_str(), NULL);
                    break;
                case PY3LANG:
                    execl("/usr/bin/python3", "python3", exc_filename.c_str(), NULL);
                    break;
            }
            exit(0);
        } else {
            if (language < MIN_LANG_NUM || language > MAX_LANG_NUM ||
                language == VCLANG || language == VCPPLANG) {
                result = "Invalid Language";
                LOG("Invalid Language Detected");
                result += "\n" + Inttostring(time_used) + "\n" +
                          Inttostring(memory_used);
                Savetofile(res_filename, result);
                exit(0);
            }
            LOG("Program Child Process: " + Inttostring(pid));
            LOG("Running program");
            runstat = 0;
            struct timeval case_startv;
            struct timezone case_startz;
            gettimeofday(&case_startv, &case_startz);
            while (1) {
                wait4(pid, &runstat, 0, &rinfo);
                time_used = (rinfo.ru_utime.tv_sec + rinfo.ru_stime.tv_sec) * 1000 +
                            (rinfo.ru_utime.tv_usec + rinfo.ru_stime.tv_usec) / 1000;
                if (total_time_limit < time_used) {
                    LOG("Dectect TLE, type:1, LOOP found. Time used: " +
                        Inttostring(time_used) + ", Limit: " +
                        Inttostring(total_time_limit));
                    ptrace(PTRACE_KILL, pid, NULL, NULL);
                    result = "Time Limit Exceed";
                    result += "\n" + Inttostring(time_used) + "\n" +
                              Inttostring(memory_used);
                    Savetofile(res_filename, result);
                    exit(0);
                }
                if (memory_used < getpagesize() * rinfo.ru_minflt)
                    memory_used = getpagesize() * rinfo.ru_minflt;
                if (WIFEXITED(runstat)) {
                    LOG((string) "Used time: " + Inttostring(time_used));
                    LOG((string) "Used Memory: " + Inttostring(memory_used));
                    LOG((string) "Run status: " + Inttostring(WEXITSTATUS(runstat)));
                    if (check_exit_status && WEXITSTATUS(runstat) != 0)
                        result = "Runtime Error";
                    else result = "Normal";
                    result += "\n" + Inttostring(time_used) + "\n" +
                              Inttostring(memory_used);
                    Savetofile(res_filename, result);
                    exit(0);
                }
                if (WIFSIGNALED(runstat) && WTERMSIG(runstat) != SIGTRAP) {
                    int signal = WTERMSIG(runstat);
                    LOG((string) "Used time: " + Inttostring(time_used));
                    LOG((string) "Used Memory: " + Inttostring(memory_used));
                    LOG((string) "Run status: " + Inttostring(runstat));
                    switch (signal) {
                        case SIGXCPU:
                            LOG("Dectect TLE, type:2, signaled");
                            result = "Time Limit Exceed";
                            time_used = time_limit.rlim_cur * 1000 + 4;
                            break;
                        case SIGXFSZ:
                            result = "Output Limit Exceed";
                            break;
                        default:
                            result = "Runtime Error";
                    }
                    result += "\n" + Inttostring(time_used) + "\n" +
                              Inttostring(memory_used);
                    Savetofile(res_filename, result);
                    ptrace(PTRACE_KILL, pid, NULL, NULL);
                    exit(0);
                } else if (WIFSTOPPED(runstat) && WSTOPSIG(runstat) != SIGTRAP) {
                    int signal = WSTOPSIG(runstat);
                    LOG((string) "Used time: " + Inttostring(time_used));
                    LOG((string) "Used Memory: " + Inttostring(memory_used));
                    LOG((string) "Run status: " + Inttostring(runstat));
                    switch (signal) {
                        case SIGXCPU:
                            result = "Time Limit Exceed";
                            LOG("Dectect TLE, type:2, signaled");
                            time_used = time_limit.rlim_cur * 1000 + 4;
                            break;
                        case SIGXFSZ:
                            result = "Output Limit Exceed";
                            break;
                        default:
                            result = "Runtime Error";
                    }
                    result += "\n" + Inttostring(time_used) + "\n" +
                              Inttostring(memory_used);
                    Savetofile(res_filename, result);
                    ptrace(PTRACE_KILL, pid, NULL, NULL);
                    exit(0);
                } else if ((runstat >> 8) != 5 && (runstat >> 8) > 0) {
                    LOG((string) "Used time: " + Inttostring(time_used));
                    LOG((string) "Used Memory: " + Inttostring(memory_used));
                    LOG((string) "Run status: " + Inttostring(runstat));
                    result = "Runtime Error";
                    result += "\n" + Inttostring(time_used) + "\n" +
                              Inttostring(memory_used);
                    Savetofile(res_filename, result);
                    ptrace(PTRACE_KILL, pid, NULL, NULL);
                    exit(0);
                }
                ptrace(PTRACE_GETREGS, pid, NULL, &reg);
#ifdef __i386__
                //printf("System call:%ld\n",reg.orig_eax);
                if (reg.orig_eax == SYS_execve&&!excuted) excuted = true;
                else {
                  if (language == JAVALANG) {
                    if (syscalls_java[reg.orig_eax]) {
                      LOG((string) "Invalid system call: " +
                          Inttostring(reg.orig_eax));
                      result = "Restricted Function";
                      result += "\n" + Inttostring(time_used) + "\n" +
                          Inttostring(memory_used);
                      Savetofile(res_filename, result);
                      ptrace(PTRACE_KILL, pid, NULL, NULL);
                      exit(0);
                    }
                  } else if (language == CSLANG) {
                    if (syscalls_csharp[reg.orig_eax]) {
                      LOG((string) "Invalid system call: " +
                          Inttostring(reg.orig_eax));
                      result = "Restricted Function";
                      result += "\n" + Inttostring(time_used) + "\n" +
                          Inttostring(memory_used);
                      Savetofile(res_filename, result);
                      ptrace(PTRACE_KILL, pid, NULL, NULL);
                      exit(0);
                    }
                  } else if (syscalls_other[reg.orig_eax]) {
                    LOG((string) "Invalid system call: " + Inttostring(reg.orig_eax));
                    result = "Restricted Function";
                    result += "\n" + Inttostring(time_used) + "\n" +
                        Inttostring(memory_used);
                    Savetofile(res_filename, result);
                    ptrace(PTRACE_KILL, pid, NULL, NULL);
                    exit(0);
                  }
                }
#else
                //printf("System call:%ld\n",reg.orig_rax);
                if (reg.orig_rax == SYS_execve && !excuted) excuted = true;
                else {
                    if (language == JAVALANG) {
                        if (syscalls_java[reg.orig_rax]) {
                            LOG((string) "Invalid system call: " + Inttostring(reg.orig_rax));
                            result = "Restricted Function";
                            result += "\n" + Inttostring(time_used) + "\n" +
                                      Inttostring(memory_used);
                            Savetofile(res_filename, result);
                            ptrace(PTRACE_KILL, pid, NULL, NULL);
                            exit(0);
                        }
                    } else if (language == CSLANG) {
                        if (syscalls_csharp[reg.orig_rax]) {
                            LOG((string) "Invalid system call: " + Inttostring(reg.orig_rax));
                            result = "Restricted Function";
                            result += "\n" + Inttostring(time_used) + "\n" +
                                      Inttostring(memory_used);
                            Savetofile(res_filename, result);
                            ptrace(PTRACE_KILL, pid, NULL, NULL);
                            exit(0);
                        }
                    } else if (syscalls_other[reg.orig_rax]) {
                        LOG((string) "Invalid system call: " + Inttostring(reg.orig_rax));
                        result = "Restricted Function";
                        result += "\n" + Inttostring(time_used) + "\n" +
                                  Inttostring(memory_used);
                        Savetofile(res_filename, result);
                        ptrace(PTRACE_KILL, pid, NULL, NULL);
                        exit(0);
                    }
                }
#endif
                if (memory_used / 1024 > memory_limit) {
                    LOG((string) "Used time: " + Inttostring(time_used));
                    LOG((string) "Used Memory: " + Inttostring(memory_used));
                    LOG((string) "Run status: " + Inttostring(runstat));
                    result = "Memory Limit Exceed";
                    result += "\n" + Inttostring(time_used) + "\n" +
                              Inttostring(memory_used);
                    Savetofile(res_filename, result);
                    ptrace(PTRACE_KILL, pid, NULL, NULL);
                    exit(0);
                }
                ptrace(PTRACE_SYSCALL, pid, NULL, NULL);
            }
            exit(0);
        }
        exit(0);
    } else {
        LOG("Watch Child Process: " + Inttostring(wid));
        int rstat, tused;
        struct timeval case_startv, case_nowv;
        struct timezone case_startz, case_nowz;
        gettimeofday(&case_startv, &case_startz);
        int cnt = -1;
        rstat = -1;
        while (1) {
            usleep(50000);
            cnt++;
            gettimeofday(&case_nowv, &case_nowz);
            tused = case_nowv.tv_sec - case_startv.tv_sec;
            if (cnt % 20 == 0) LOG("Running Used: " + Inttostring(tused));
            if (waitpid(wid, &rstat, WNOHANG) == 0) {
                if (tused > runtime.rlim_max) {
                    result = "Judge Error";
                    LOG("Time too much!");
                    LOG("kill `pstree -p " + Inttostring(wid) +
                        " | sed 's/(/\\n(/g' | grep '(' | "
                                "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`");
                    system(("kill `pstree -p " + Inttostring(wid) +
                            " | sed 's/(/\\n(/g' | grep '(' | "
                                    "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`").c_str());
                    waitpid(wid, &rstat, 0);
                    return 1;
                }
            } else if (WIFSIGNALED(rstat) && WTERMSIG(rstat) != 0) {
                result = "Judge Error";
                LOG("Something is wrong.");
                LOG("kill `pstree -p " + Inttostring(wid) +
                    " | sed 's/(/\\n(/g' | grep '(' | "
                            "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`");
                system(("kill `pstree -p " + Inttostring(wid) +
                        " | sed 's/(/\\n(/g' | grep '(' | "
                                "sed 's/(\\(.*\\)).*/\\1/' | tr \"\\n\" \" \"`").c_str());
                waitpid(wid, &rstat, 0);
                return 1;
            } else if (WIFEXITED(rstat)) {
                waitpid(wid, &rstat, 0);
                LOG("Runned.");
                break;
            }
        }
        string res = "";
        fstream fin(res_filename.c_str(), fstream::in);
        while (fin.fail()) fin.open(res_filename.c_str(), fstream::in);
        //system(("kill -9 "+Inttostring(wid)).c_str());
        int case_time_used, case_memory_used;
        getline(fin, result);
        fin >> case_time_used >> case_memory_used;
        time_used += case_time_used;
        memory_used = max(memory_used, case_memory_used);
        fin.close();
        if (result == "" || result == " ") {
            result = "Judge Error";
            LOG("Failed to get result.");
        }
        system(("rm " + res_filename).c_str());
    }
    return 0;
}

void Program::Trytocompile(string source, int language) {
    if (vmlang[language]) compile_time_limit = GENERAL_COMPILE_TIME * 3;
    if (source == "") {
        source = this->source;
    }
    if (language == -1) {
        language = this->language;
    }
    int res = Compile(source, language);
    compiled = true;
    if (res == -1) {
        //JUDGE ERROR
        result = "Invalid Language";
        return;
    } else if (res == 2) {
        //COMPILE ERROR
        result = "Compile Error";
        return;
    }
    int cnt = 0;
    while (res == 1) {
        cnt++;
        ce_info = Loadallfromfile(cinfo_filename, 200);
        if (ce_info.length() > 0 || cnt > 2) {
            result = "Compile Error";
            return;
        } else {
            usleep(50000);
            res = Compile(source, language);
        }
    }
    if (vmlang[language]) {
        total_time_limit *= VMLANG_MULTIPLIER;
        case_time_limit *= VMLANG_MULTIPLIER;
        memory_limit *= VMLANG_MULTIPLIER;
    }
}

void Program::Run() {
    if (!compiled) {
        result = "";
        Trytocompile(source, language);
        if (result != "") return;
    }
    if (exc_filename != src_filename) {
        string tmps = CONFIG->GetTmpfile_path() + tmpnam();
        //Deletefile(src_filename);
        system(((string) "mv " + src_filename + " " + tmps).c_str());
        src_filename = tmps;
    }
    Excution();
    if (total_time_limit < time_used) result = "Time Limit Exceed";
}
