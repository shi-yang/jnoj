#include "Comparator.h"
#include "Logger.h"

extern int CHECKER_RUN_TIME, CHECKER_RUN_MEMORY;
extern string tmpnam();

Comparator::Comparator() {
  //ctor
  detail = "";
  spj = NULL;
}

Comparator::~Comparator() {
  //dtor
  system(((string) "rm " + infile).c_str());
  if (isspj) delete spj;
}

int Comparator::Compare() {
  LOG("Comparing " + out_filename + " and " + stdout_filename);
  if (isspj == 0) {
    bool aced = true, peed = true;
    FILE *program_out, *standard_out;
    int eofp = EOF, eofs = EOF;
    program_out = fopen(out_filename.c_str(), "r");
    standard_out = fopen(stdout_filename.c_str(), "r");
    char po_char, so_char;
    while (1) {
      while ((eofs = fscanf(standard_out, "%c", &so_char)) != EOF &&
          so_char == '\r');
      while ((eofp = fscanf(program_out, "%c", &po_char)) != EOF &&
          po_char == '\r');
      if (eofs == EOF || eofp == EOF) {
        break;
      }
      if (so_char != po_char) {
        detail = (string) "Expected " + so_char + ", Found " + po_char +
            " Not AC.";
        LOG((string) "Expected " + so_char + ", Found " + po_char +
            " Not AC.");
        aced = false;
        break;
      }
    }
    while ((so_char == '\n' || so_char == '\r') &&
        ((eofs = fscanf(standard_out, "%c", &so_char)) != EOF))
      ;
    while ((po_char == '\n' || po_char == '\r') &&
        ((eofp = fscanf(program_out, "%c", &po_char)) != EOF))
      ;
    if (eofp != eofs) {
      aced = false;
    }
    fclose(program_out);
    fclose(standard_out);
    if (!aced) {
      program_out = fopen(out_filename.c_str(), "r");
      standard_out = fopen(stdout_filename.c_str(), "r");
      while (1) {
        while ((eofs = fscanf(standard_out, "%c", &so_char)) != EOF &&
            (so_char == ' ' || so_char == '\n' || so_char == '\r'));
        while ((eofp = fscanf(program_out, "%c", &po_char)) != EOF &&
            (po_char == ' ' || po_char == '\n' || po_char == '\r'));
        if (eofs == EOF || eofp == EOF) break;
        if (so_char != po_char) {
          detail += (string) "\nExpected " + so_char + ", Found " + po_char +
              " Not PE.";
          LOG((string) "Expected " + so_char + ", Found " + po_char +
              " Not PE.");
          peed = false;
          break;
        }
      }
      while ((so_char == ' ' || so_char == '\n' || so_char == '\r') &&
          ((eofs = fscanf(standard_out, "%c", &so_char)) != EOF));
      while ((po_char == ' ' || po_char == '\n' || po_char == '\r') &&
          ((eofp = fscanf(program_out, "%c", &po_char)) != EOF));
      if (eofp != eofs) {
        peed = false;
        detail += (string) "\nExpected " + so_char + ", Found " + po_char +
            " Not PE.";
        LOG((string) "Expected " + so_char + ", Found " + po_char +
            " Not PE.");
      }
      fclose(program_out);
      fclose(standard_out);
    }
    if (aced) return AC_STATUS;
    else if (peed) return PE_STATUS;
    else return WA_STATUS;
  } else {
    if (spj == NULL) {
      spj = new Program;
      if (isspj == 1) {
        LOG((string) "Do SPJ, using " + "spj/" + spj->Inttostring(pid) +
            ".cpp");
        if (!spj->Checkfile("spj/" + spj->Inttostring(pid) + ".cpp")) {
          LOG("No SPJ.");
          return JE_STATUS;
        }
        spj->Setsource(spj->Loadallfromfile("spj/" + spj->Inttostring(pid) +
        ".cpp"));
        spj->Setlanguage(CPPLANG);
      } else if (isspj == 2) {
        LOG((string) "Do SPJ, using " + "spj/" + spj->Inttostring(pid) +
            ".java");
        if (!spj->Checkfile("spj/" + spj->Inttostring(pid) + ".java")) {
          LOG("No SPJ.");
          return JE_STATUS;
        }
        spj->Setsource(spj->Loadallfromfile("spj/" + spj->Inttostring(pid) +
            ".java"));
        spj->Setlanguage(JAVALANG);
      }
      spj->Sethas_input(true);
      infile = tmpnam();
      LOG("In file: " + in_filename);
      LOG("Stdout file: " + stdout_filename);
      LOG("Out file: " + out_filename);
      LOG("Src file: " + src_filename);
      spj->Savetofile(infile, in_filename + "\n" + stdout_filename + "\n" +
          out_filename + "\n" + src_filename);
      spj->Setout_filename(tmpnam());
      spj->Setcheck_exit_status(true);
      spj->Settotal_time_limit(CHECKER_RUN_TIME * 1000);
      spj->Setcase_time_limit(CHECKER_RUN_TIME * 1000);
      spj->Setmemory_limit(CHECKER_RUN_MEMORY);
    }
    spj->Setin_filename(infile);
    LOG("SPJ Run!");
    spj->Run();
    //LOG(spj->Loadallfromfile(out_filename));
    //LOG(spj->Loadallfromfile(src_filename));
    LOG("SPJ result: " + spj->Getresult());
    detail = spj->Loadallfromfile(spj->Getout_filename());
    LOG(detail);
    if (spj->Getresult() != "Normal") {
      return WA_STATUS;
    }
    return AC_STATUS;
  }
}
