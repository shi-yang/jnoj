/* 
 * File:   JudgerThread.cpp
 * Author: 51isoft
 * 
 * Created on 2014年1月13日, 下午10:10
 */

#include "JudgerThread.h"
#include "Bott.h"

/**
 * Initialize a judger handler
 * @param _socket       The client sockfd
 * @param oj            OJ name
 */
JudgerThread::JudgerThread(SocketHandler * _socket, string _oj) {
  db = new DatabaseHandler();
  oj = _oj;
  socket = _socket;
  current_submit = NULL;
}

JudgerThread::~JudgerThread() {
  delete db;
  delete socket;
  // Don't delete it, since it will be requeued
  // if (current_submit) delete current_submit;
}

/**
 * Load infos for a regular run, and store it to a bott file
 * @param bott  The bott file we store infos to
 * @param runid Regular runid
 */
void JudgerThread::prepareBottForRun(Bott * bott, int runid) {
  // load basic info from status table
  map<string, string> info = db->Getall_results("\
            SELECT status.source AS source, \
                   status.runid AS runid, \
                   status.language AS language, \
                   status.pid AS pid, \
                   problem.ignore_noc AS ignore_noc, \
                   problem.is_interactive AS is_interactive \
            FROM status, problem \
            WHERE status.pid = problem.pid \
                  AND runid = '" + intToString(runid) + "' \
    ")[0];

  bott->Setsrc(info["source"]);
  bott->Setrunid(stringToInt(info["runid"]));
  bott->Setlanguage(stringToInt(info["language"]));
  bott->Setpid(stringToInt(info["pid"]));

  // DO_TESTALL will ignore time_limit and just use case_limit
  // NEED_JUDGE will set the time_limit together with case_limit
  // a little bit hacky though
  if (info["ignore_noc"] == "1") {
    bott->Settype(DO_TESTALL);
  } else {
    bott->Settype(NEED_JUDGE);
  }

  if (info["is_interactive"] == "1") {
    bott->Settype(DO_INTERACTIVE);
  }

  // load additional info from problem table
  info = db->Getall_results("\
            SELECT number_of_testcase, time_limit, case_time_limit,\
                   memory_limit, special_judge_status, vname, vid \
            FROM   problem \
            WHERE  pid = '" + info["pid"] + "' \
    ")[0];

  bott->Setnumber_of_testcases(stringToInt(info["number_of_testcase"]));
  bott->Settime_limit(stringToInt(info["time_limit"]));
  bott->Setcase_limit(stringToInt(info["case_time_limit"]));
  bott->Setmemory_limit(stringToInt(info["memory_limit"]));
  bott->Setspj(stringToInt(info["special_judge_status"]));
  bott->Setvname(info["vname"]);
  bott->Setvid(info["vid"]);

  bott->save();
}

/**
 * Load infos for a challenge, and store it to a bott file
 * @param bott  The bott file we store infos to
 * @param id Challenge id
 */
void JudgerThread::prepareBottForChallenge(Bott * bott, int id) {
  // load basic info from challenge table
  map<string, string> info = db->Getall_results("\
            SELECT source, \
                   cha_id, \
                   language, \
                   pid, \
                   data_type, \
                   data_lang, \
                   data_detail \
            FROM   status,challenge \
            WHERE  status.runid = challenge.runid \
                   AND cha_id = '" + intToString(id) + "' \
    ")[0];

  bott->Settype(DO_CHALLENGE);
  bott->Setsrc(info["source"]);
  bott->Setcha_id(stringToInt(info["cha_id"]));
  bott->Setlanguage(stringToInt(info["language"]));
  bott->Setpid(stringToInt(info["pid"]));
  bott->Setdata_type(stringToInt(info["data_type"]));
  bott->Setdata_lang(stringToInt(info["data_lang"]));
  bott->Setdata_detail(info["data_detail"]);

  // load additional info from problem table
  info = db->Getall_results("\
            SELECT case_time_limit, memory_limit, special_judge_status \
            FROM   problem \
            WHERE  pid = '" + info["pid"] + "' \
    ")[0];

  bott->Setcase_limit(stringToInt(info["case_time_limit"]));
  bott->Setmemory_limit(stringToInt(info["memory_limit"]));
  bott->Setspj(stringToInt(info["special_judge_status"]));

  bott->save();
}

/**
 * Update the runid result in status table
 * @param runid         Runid
 * @param result        Current result
 */
void JudgerThread::updateRunResult(int runid, string result) {
  db->query("UPDATE status SET result='" + db->escape(result) +
            "' WHERE runid = '" + intToString(runid) + "'");
}

/**
 * Update the runid status with details in status table
 * @param bott  Result details
 */
void JudgerThread::updateRunStatus(Bott * bott) {
  db->query("\
        UPDATE status \
        SET    result = '" + db->escape(bott->Getresult()) + "', \
               memory_used = '" + intToString(bott->Getmemory_used()) + "', \
               time_used = '" + intToString(bott->Gettime_used()) + "', \
               ce_info = '" + db->escape(bott->Getce_info()) + "' \
        WHERE  runid = '" + intToString(bott->Getrunid()) + "'");
}

/**
 * Update the Challenge status with details in related (challenge and status)
 * tables.
 * @param bott  Result details
 */
void JudgerThread::updateChallengeStatus(Bott * bott) {
  db->query("\
        UPDATE challenge \
        SET    result = '" + db->escape(bott->Getcha_result()) + "', \
               cha_detail = '" + db->escape(bott->Getcha_detail()) + "' \
        WHERE  cha_id = '" + intToString(bott->Getcha_id()) + "'");

  LOG("Challenge result, cha_id: " + intToString(bott->Getcha_id()) +
      ", result: " + bott->Getcha_result());

  if (bott->Getcha_result().find("Challenge Success") != string::npos) {
    // challenge success, update status table
    db->query("\
                UPDATE status, challenge \
                SET    status.result = 'Challenged' \
                WHERE  status.runid = challenge.runid AND \
                       challenge.cha_id = '" +
              intToString(bott->Getcha_id()) + "' \
        ");
  }
}

/**
 * Update the challenge result in challenge table
 * @param id            Challenge id
 * @param result        Current result
 */
void JudgerThread::updateChallengeResult(int id, string result) {
  db->query("UPDATE challenge SET cha_result='" + db->escape(result) +
            "' WHERE cha_id = '" + intToString(id) + "'");
}

/**
 * Update statistics for a regular run
 * @param runid         Runid
 * @param result        Verdict result
 */
void JudgerThread::updateStatistics(int runid, string result) {

  // load basic info of this run
  map<string, string> run_info = db->Getall_results("\
      SELECT username, status.pid as pid, vname \
      FROM   status, problem \
      WHERE  runid = '" + intToString(runid) + "' AND problem.pid = status.pid \
  ")[0];

  LOG("Updating statistics, runid: " + intToString(runid) + ", user: " +
      run_info["username"] + ", result: " + result + ", pid: " +
      run_info["pid"]);

  if (result.find("Accept") != string::npos) {
    map<string, string> info = db->Getall_results("\
            SELECT count(*) \
            FROM   status \
            WHERE  username = '" + db->escape(run_info["username"]) + "' AND \
                   pid = '" + db->escape(run_info["pid"]) + "' AND \
                   result = 'Accepted' \
        ")[0];
    if (info["0"] == "1") {
      // first time AC, add total_ac to user
      db->query("UPDATE user SET total_ac = total_ac + 1 WHERE username='" +
                db->escape(run_info["username"]) + "'");
      if (run_info["vname"] == CONFIG->Getlocal_identifier()) {
        // if it's local problem, update local_ac
        db->query("UPDATE user SET local_ac = local_ac + 1 WHERE username='" +
                  db->escape(run_info["username"]) + "'");
      }
    }
    // update problem stats
    db->query("UPDATE problem SET total_ac = total_ac + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  } else if (result.find("Wrong Answer") != string::npos) {
    db->query("UPDATE problem SET total_wa = total_wa + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  } else if (result.find("Runtime Error") != string::npos) {
    db->query("UPDATE problem SET total_re = total_re + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  } else if (result.find("Presentation Error") != string::npos) {
    db->query("UPDATE problem SET total_pe = total_pe + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  } else if (result.find("Time Limit Exceed") != string::npos) {
    db->query("UPDATE problem SET total_tle = total_tle + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  } else if (result.find("Memory Limit Exceed") != string::npos) {
    db->query("UPDATE problem SET total_mle = total_mle + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  } else if (result.find("Output Limit Exceed") != string::npos) {
    db->query("UPDATE problem SET total_ole = total_ole + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  } else if (result.find("Restricted Function") != string::npos) {
    db->query("UPDATE problem SET total_rf = total_rf + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  } else if (result.find("Compile Error") != string::npos) {
    db->query("UPDATE problem SET total_ce = total_ce + 1 WHERE pid='" +
              db->escape(run_info["pid"]) + "'");
  }
}

/**
 * Main loop of the judger handler
 */
void JudgerThread::run() {
  while (true) {
    if (!socket->checkAlive()) {
      LOG("Connection lost. OJ: " + oj);
      return;
    }
    usleep(50000); // sleep 50ms
    if (current_submit) {
      // got a new judge task
      string filename;
      Bott * bott;
      if (current_submit->Gettype() == NEED_JUDGE ||
          current_submit->Gettype() == DO_PRETEST ||
          current_submit->Gettype() == DO_TESTALL) {
        // A regular run
        LOG("Load infos of Runid: " + intToString(current_submit->Getid()));

        // prepare file for judger
        filename = Bott::RAW_FILES_DIRECTORY +
            intToString(current_submit->Getid()) + Bott::EXTENTION;
        bott = new Bott();
        bott->Setout_filename(filename);
        prepareBottForRun(bott, current_submit->Getid());
        delete bott;

        // set runid result to Judging
        updateRunResult(current_submit->Getid(), "Judging");

        try {
          LOG("Sending to judger...");
          socket->sendFile(filename);
          filename = Bott::RESULTS_DIRECTORY +
              intToString(current_submit->Getid()) + "res" + Bott::EXTENTION;
          socket->receiveFile(filename);
          LOG("Got result back from judger.");
        } catch (Exception & e) {
          LOG("Connection lost, requeue Runid: " + current_submit->Getid());
          updateRunResult(current_submit->Getid(), "Judge Error & Requeued");
          return;
        }

        // parse and process result from judger
        bott = new Bott(filename);
        updateRunStatus(bott);
        updateStatistics(bott->Getrunid(), bott->Getresult());
      } else if (current_submit->Gettype() == DO_CHALLENGE) {
        // A challenge
        LOG("Load infos of Challenge id: " + current_submit->Getid());
        filename = Bott::CHA_RAW_FILES_DIRECTORY +
            intToString(current_submit->Getid()) + Bott::EXTENTION;
        bott = new Bott(filename);
        prepareBottForChallenge(bott, current_submit->Getid());
        delete bott;

        // set challenge id result to Testing
        updateChallengeResult(current_submit->Getid(), "Testing");

        try {
          socket->sendFile(filename);
          filename = Bott::CHA_RESULTS_DIRECTORY +
              intToString(current_submit->Getid()) + "res" + Bott::EXTENTION;
          socket->receiveFile(filename);
        } catch (Exception & e) {
          LOG("Connection lost, requeue Challenge id: " +
              current_submit->Getid());
          updateChallengeResult(current_submit->Getid(),
                                "Test Error & Requeued");
          return;
        }

        // parse and process result from judger
        bott = new Bott(filename);
        updateChallengeStatus(bott);
      }

      // judge finished
      delete bott;
      delete current_submit;
      current_submit = NULL;
    }
  }
}
