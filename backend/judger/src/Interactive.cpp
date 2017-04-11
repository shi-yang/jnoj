/*
 * File:   Interactive.cpp
 * Author: payper
 *
 * Created on 2014年4月24日, 下午5:41
 */

#include "Interactive.h"
#include "Logger.h"

extern bool syscalls_other[500];
extern bool syscalls_java[500];
extern bool syscalls_csharp[500];
extern int GENERAL_COMPILE_TIME, GENERATOR_RUN_TIME, GENERATOR_RUN_MEMORY,
    VMLANG_MULTIPLIER, MAX_OUTPUT_LIMIT, EXTRA_RUNTIME;
extern int lowprivid;
extern string tmpnam();

Interactive::Interactive() : Program() {
  validator_compiled = false;
}

Interactive::~Interactive() {
  if (validator_base_filename != "") Deletefile(validator_base_filename + "*");
  if (Checkfile(validator_src_filename)) Deletefile(validator_src_filename);
  if (Checkfile(validator_exc_filename)) Deletefile(validator_exc_filename);
  if (Checkfile(validator_err_filename)) Deletefile(validator_err_filename);
  if (Checkfile(validator_cinfo_filename)) Deletefile(validator_cinfo_filename);
}

int Interactive::Excution() {
  system(((string) "chmod +x " + exc_filename).c_str());
  system(((string) "chmod +x " + validator_exc_filename).c_str());
  res_filename = tmpnam();
  Savetofile(res_filename, "");
  struct rlimit runtime;
  runtime.rlim_max = runtime.rlim_cur = CONFIG->GetInteractive_max_run_time() +
      EXTRA_RUNTIME;
  pid_t wid;
  if ((wid = fork()) == 0) {
    setpgid(0, 0);
    pid_t pgrp = getpid();
    pid_t vpid;
    int vtorPipe[2];
    int rtovPipe[2];
    pipe(vtorPipe);
    pipe(rtovPipe);
    if ((vpid = fork()) == 0) {
      setpgid(0, pgrp);
      LOGGER->addIdentifier(getpid(), "Sandbox");

      string exc_command;
      pid_t pid;
      int runstat;
      bool excuted = false;
      struct rusage rinfo;
      setrlimit(RLIMIT_CPU, &runtime);
      struct user_regs_struct reg;
      struct rlimit time_limit, output_limit, nproc_limit;

      time_limit.rlim_cur = total_time_limit;
      time_limit.rlim_cur = (time_limit.rlim_cur + 999) / 1000;
      if (time_limit.rlim_cur <= 0) time_limit.rlim_cur = 1;
      time_limit.rlim_max = time_limit.rlim_cur + 1;

      nproc_limit.rlim_cur = nproc_limit.rlim_max = 1;

      if ((pid = fork()) == 0) {
        LOGGER->addIdentifier(getpid(), "Runner");
        dup2(vtorPipe[0], STDIN_FILENO);
        dup2(rtovPipe[1], STDOUT_FILENO);
        close(vtorPipe[0]);
        close(vtorPipe[1]);
        close(rtovPipe[0]);
        close(rtovPipe[1]);
        setpgid(0, pgrp);
        LOG((string) "Time limit for this program is " +
            Inttostring(time_limit.rlim_cur));
        setrlimit(RLIMIT_CPU, &time_limit);
        setrlimit(RLIMIT_NPROC, &nproc_limit);
        signal(SIGPIPE, SIG_IGN);

        setuid(lowprivid);
        ptrace(PTRACE_TRACEME, 0, NULL, NULL);

        switch (language) {
          case CPPLANG:
          case CLANG:
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
        close(vtorPipe[0]);
        close(vtorPipe[1]);
        close(rtovPipe[0]);
        close(rtovPipe[1]);
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
          time_used = (rinfo.ru_utime.tv_sec + rinfo.ru_stime.tv_sec)*1000 +
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
            break;
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
            break;
          }
          if (WIFSIGNALED(runstat) && WTERMSIG(runstat) != SIGTRAP &&
              WTERMSIG(runstat) != SIGPIPE) {
            int signal = WTERMSIG(runstat);
            LOG((string) "Used time: " + Inttostring(time_used));
            LOG((string) "Used Memory: " + Inttostring(memory_used));
            LOG((string) "Run status: " + Inttostring(runstat));
            LOG((string) "Signal: " + Inttostring(signal));
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
            break;
          } else if (WIFSTOPPED(runstat) && WSTOPSIG(runstat) != SIGTRAP &&
              WSTOPSIG(runstat) != SIGPIPE) {
            int signal = WSTOPSIG(runstat);
            LOG((string) "Used time: " + Inttostring(time_used));
            LOG((string) "Used Memory: " + Inttostring(memory_used));
            LOG((string) "Run status: " + Inttostring(runstat));
            LOG((string) "Stopped, Signal: " + Inttostring(signal));
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
            break;
          } else if ((runstat >> 8) != SIGTRAP && (runstat >> 8) != SIGPIPE &&
              (runstat >> 8) > 0) {
            LOG((string) "Used time: " + Inttostring(time_used));
            LOG((string) "Used Memory: " + Inttostring(memory_used));
            LOG((string) "Run status: " + Inttostring(runstat));
            LOG((string) "Run status to signal: " + Inttostring(runstat >> 8));
            result = "Runtime Error";
            result += "\n" + Inttostring(time_used) + "\n" +
                Inttostring(memory_used);
            Savetofile(res_filename, result);
            ptrace(PTRACE_KILL, pid, NULL, NULL);
            break;
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
          if (reg.orig_rax == SYS_execve&&!excuted) excuted = true;
          else {
            if (language == JAVALANG) {
              if (syscalls_java[reg.orig_rax]) {
                LOG((string) "Invalid system call: " +
                    Inttostring(reg.orig_rax));
                result = "Restricted Function";
                result += "\n" + Inttostring(time_used) + "\n" +
                    Inttostring(memory_used);
                Savetofile(res_filename, result);
                ptrace(PTRACE_KILL, pid, NULL, NULL);
                break;
              }
            } else if (language == CSLANG) {
              if (syscalls_csharp[reg.orig_rax]) {
                LOG((string) "Invalid system call: " +
                    Inttostring(reg.orig_rax));
                result = "Restricted Function";
                result += "\n" + Inttostring(time_used) + "\n" +
                    Inttostring(memory_used);
                Savetofile(res_filename, result);
                ptrace(PTRACE_KILL, pid, NULL, NULL);
                break;
              }
            } else if (syscalls_other[reg.orig_rax]) {
              LOG((string) "Invalid system call: " + Inttostring(reg.orig_rax));
              result = "Restricted Function";
              result += "\n" + Inttostring(time_used) + "\n" +
                  Inttostring(memory_used);
              Savetofile(res_filename, result);
              ptrace(PTRACE_KILL, pid, NULL, NULL);
              break;
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
            break;
          }
          ptrace(PTRACE_SYSCALL, pid, NULL, NULL);
        }
        exit(0);
      }
      exit(0);
    } else {
      pid_t pvid;
      if ((pvid = fork()) == 0) {
        LOGGER->addIdentifier(getpid(), "Validator");
        LOG("Executing Validator");
        setpgid(0, pgrp);
        dup2(rtovPipe[0], STDIN_FILENO);
        dup2(vtorPipe[1], STDOUT_FILENO);
        close(vtorPipe[0]);
        close(vtorPipe[1]);
        close(rtovPipe[0]);
        close(rtovPipe[1]);
        signal(SIGPIPE, SIG_IGN);
        freopen(Getout_filename().c_str(), "w", stderr);

        string exc_command;
        setuid(lowprivid);

        switch (language) {
          case CPPLANG:
          case CLANG:
          case FORTLANG:
          case FPASLANG:
          case SMLLANG:
          case ADALANG:
            exc_command = (string) "./" + validator_exc_filename;
            execl(exc_command.c_str(), exc_command.c_str(), NULL);
            break;
          case JAVALANG:
            execl("/usr/bin/java", "java", "-Djava.security.manager",
                  "-Djava.security.policy=java.policy", "-client", "-cp",
                  CONFIG->GetTmpfile_path().c_str(), "Validator", NULL);
            break;
          case CSLANG:
            execl("/usr/bin/mono", "mono", validator_exc_filename.c_str(),
                  NULL);
            break;
          case PERLLANG:
            execl("/usr/bin/perl", "perl", validator_src_filename.c_str(), "-W",
                  NULL);
          case RUBYLANG:
            execl("/usr/bin/ruby", "ruby", validator_src_filename.c_str(), "-W",
                  NULL);
            break;
          case PY2LANG:
            execl("/usr/bin/python2", "python2", validator_exc_filename.c_str(),
                  NULL);
            break;
          case PY3LANG:
            execl("/usr/bin/python3", "python3", validator_exc_filename.c_str(),
                  NULL);
            break;
        }
        exit(0);
      } else {
        LOGGER->addIdentifier(getpid(), "ValidatorWatcher");
        LOG("Watching Validator...");
        close(vtorPipe[0]);
        close(vtorPipe[1]);
        close(rtovPipe[0]);
        close(rtovPipe[1]);
        int rstat, cstat, waitcnt;
        while (1) {
          usleep(50000);
          waitpid(pvid, &rstat, WNOHANG);
          if (WIFEXITED(rstat)) {
            waitpid(pvid, &rstat, 0);
            LOG("Validator runned.");
            LOG("Validator exit status: " + Inttostring(WEXITSTATUS(rstat)));
            waitcnt = 10;
            while (waitcnt--) {
              usleep(50000);
              waitpid(vpid, &cstat, WNOHANG);
              if (WIFEXITED(cstat)) {
                waitpid(vpid, &cstat, 0);
                LOG("User program runned.");
                LOG("User program exit status: " +
                    Inttostring(WEXITSTATUS(cstat)));
                exit(WEXITSTATUS(rstat));
              }
            }
            LOG("User not exit");
            kill(-pgrp, SIGKILL);
            exit(1);
          }

          waitpid(vpid, &cstat, WNOHANG);
          if (WIFEXITED(cstat)) {
            waitpid(vpid, &cstat, 0);
            LOG("User program runned.");
            LOG("User program exit status: " + Inttostring(WEXITSTATUS(cstat)));
            waitcnt = 10;
            while (waitcnt--) {
              usleep(50000);
              waitpid(pvid, &rstat, WNOHANG);
              if (WIFEXITED(rstat)) {
                waitpid(pvid, &rstat, 0);
                LOG("Validator runned.");
                LOG("Validator exit status: " + Inttostring(WEXITSTATUS(rstat)));
                exit(WEXITSTATUS(rstat));
              }
            }
            LOG("Validator not exit");
            kill(-pgrp, SIGKILL);
            exit(1);
          }
        }
        exit(0);
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
    while (1) {
      usleep(50000);
      cnt++;
      gettimeofday(&case_nowv, &case_nowz);
      tused = case_nowv.tv_sec - case_startv.tv_sec;
      if (cnt % 20 == 0) LOG("Running Used: " + Inttostring(tused));
      pid_t exit_pid;
      if ((exit_pid = waitpid(wid, &rstat, WNOHANG)) == 0) {
        if (tused > runtime.rlim_max) {
          result = "Time Limit Exceed";
          LOG("Time too much!");
          kill(-wid, SIGKILL);
          waitpid(wid, &rstat, 0);
          return 1;
        }
      } else if (WIFSIGNALED(rstat) && WTERMSIG(rstat) != 0) {
        result = "Wrong Answer";
        LOG("Something is wrong.");
        kill(-wid, SIGKILL);
        waitpid(wid, &rstat, 0);
        return 1;
      }
      if (exit_pid == wid && WIFEXITED(rstat)) {
        LOG("Exited. Status: " + Inttostring(rstat));
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
    fin >> case_time_used>>case_memory_used;
    time_used += case_time_used;
    memory_used = max(memory_used, case_memory_used);
    fin.close();
    if (result == "" || result == " ") {
      result = "Judge Error";
      LOG("Failed to get result.");
    } else if (result == "Normal") {
      LOG((string) "Validator raw exit status: " + Inttostring(rstat));
      LOG((string) "Validator exit status: " + Inttostring(WEXITSTATUS(rstat)));
      if (WEXITSTATUS(rstat) == 0) result = "Accepted";
      else result = "Wrong Answer";
    }
    system(("rm " + res_filename).c_str());
  }
  return 0;
}

void Interactive::Run() {
  LOG("Check validator");
  if (!validator_compiled) {
    LOG("Compile validator");
    Setresult("");
    // reuse compile function
    Trytocompile(validator_source, validator_language);
    if (Getresult() != "") {
      Setresult("Judge Error");
      return;
    }

    // copy compile result of validator
    validator_base_filename = Getbase_filename();
    validator_src_filename = Getsrc_filename();
    validator_exc_filename = Getexc_filename();
    validator_err_filename = Geterr_filename();
    validator_cinfo_filename = Getcinfo_filename();
    Setcompiled(false);
  }
  LOG("Check program");
  if (!Getcompiled()) {
    LOG("Compile program");
    Setresult("");
    Trytocompile(source, language);
    if (Getresult() != "") return;
  }
  Excution();
  Setce_info(Loadallfromfile(Getout_filename()));
  if (total_time_limit < time_used) result = "Time Limit Exceed";
}
