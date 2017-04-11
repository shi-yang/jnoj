/* 
 * File:   vjudge.cpp
 * Author: 51isoft
 *
 * Created on 2014年1月31日, 下午3:41
 */

#include "vjudge.h"
#include "JudgerInfo.h"
#include "Bott.h"

#include "JudgerFactory.h"

/**
 * Start a virtual judger
 * @param arg   Should be a JudgerInfo, contains oj specific contents
 * @return NULL
 */
void * start_judger(void * arg) {
  JudgerInfo * judger_info = (JudgerInfo *) arg;
  VirtualJudger * judger = NULL;

  while (true) {
    try {
      judger = JudgerFactory::createJudger(judger_info);
      judger->run();
    } catch (Exception & e) {
      LOG((string) "Judger failed, reason: " + e.what(), judger_info->GetId());
    }
    if (judger) {
      delete judger;
      judger = NULL;
    }
    LOG("Trying to recreate a judger, wait 5 seconds first.",
        judger_info->GetId());
    sleep(5);
  }

  pthread_exit(NULL);
}

int main() {
  // boost io performance
  ios::sync_with_stdio(false);

  curl_global_init(CURL_GLOBAL_ALL);
  vector<JudgerInfo> judgers = CONFIG->GetJudger_info();
  for (vector<JudgerInfo>::iterator it = judgers.begin(); it != judgers.end();
      ++it) {
    pthread_t thread_id;
    pthread_create(&thread_id, NULL, start_judger, (void *) (& (*it)));
    pthread_detach(thread_id);
  }

  // Infinite loop
  while (true) {
    sleep(100000);
  }

  curl_global_cleanup();
  return 0;
}

