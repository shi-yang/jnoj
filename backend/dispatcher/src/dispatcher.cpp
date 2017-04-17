#include "dispatcher.h"
#include "JudgerThread.h"
#include "DatabaseHandler.h"
#include "SocketHandler.h"
#include "Submit.h"

// Global config
list<Submit *> runs;
list<JudgerThread *> judgers;
pthread_mutex_t runs_mutex = PTHREAD_MUTEX_INITIALIZER;
pthread_mutex_t judgers_mutex = PTHREAD_MUTEX_INITIALIZER;
int main_sockfd;

/**
 * Init the network connections
 */
void init_network() {

    if ((main_sockfd = socket(AF_INET, SOCK_STREAM, 0)) == -1) {
        perror("socket() error\n");
        exit(1);
    }

    struct sockaddr_in my_addr;
    my_addr.sin_family = AF_INET;
    my_addr.sin_port = htons(CONFIG->Getport_listen());
    my_addr.sin_addr.s_addr = INADDR_ANY;
    bzero(&(my_addr.sin_zero), 8);

    if (bind(main_sockfd, (struct sockaddr *) &my_addr,
             sizeof(struct sockaddr)) == -1) {
        perror("bind() error");
        exit(1);
    }
    if (listen(main_sockfd, 5) == -1) {
        perror("listen() error");
        exit(1);
    }
}

/**
 * Get a DB handler
 * @return A DB handler
 */
DatabaseHandler *get_db_instance() {
    try {
        return new DatabaseHandler();
    } catch (Exception &e) {
        perror(e.what());
        exit(1);
    }
}

/**
 * Inset a submit into runs list
 * @param submit        A Submit to be inserted
 */
void insert_run(Submit *submit) {
    pthread_mutex_lock(&runs_mutex);
    runs.push_back(submit);
    pthread_mutex_unlock(&runs_mutex);
}

struct JudgerArgs {
    SocketHandler *socket;
    string oj;

    JudgerArgs(SocketHandler *_socket, string _oj) : socket(_socket), oj(_oj) {
    }
};

/**
 * Judger handler
 * @param arg   should be (JudgerArgs *), passes the socket handler and the oj
 *    name
 * @return NULL
 */
void *judger_handler(void *arg) {
    JudgerArgs *judger_info = (JudgerArgs *) arg;

    LOGGER->addIdentifier(pthread_self(), judger_info->oj);
    JudgerThread *judger = new JudgerThread(judger_info->socket,
                                            judger_info->oj);
    delete judger_info;

    pthread_mutex_lock(&judgers_mutex);
    judgers.push_back(judger);
    pthread_mutex_unlock(&judgers_mutex);

    judger->run();

    pthread_mutex_lock(&judgers_mutex);
    judgers.remove(judger);
    pthread_mutex_unlock(&judgers_mutex);

    // check if there's unfinished submit
    Submit *submit = judger->Getcurrent_submit();
    if (submit) {
        insert_run(submit);
    }

    delete judger;
    LOGGER->eraseIdentifier(pthread_self());
    pthread_exit(NULL);
}

/**
 * Connection dealer
 * @param arg   should be (int *), indicates the socket_fd
 * @return NULL
 */
void *connection_handler(void *arg) {
    int *client_fd = (int *) arg;
    SocketHandler *socket = new SocketHandler(*client_fd);
    delete client_fd;

    string message = socket->getConnectionMessage();
    vector<string> details = split(message, '\n');

    if (details[0] == CONFIG->Getjudger_string()) {
        // format: judger_string\nOJ
        LOG("Judger connected, OJ: " + details[1]);
        if (judgers.size() < MAX_JUDGER_NUMBER) {
            pthread_t thread_id;
            JudgerArgs *arg = new JudgerArgs(socket, details[1]);
            pthread_create(&thread_id, NULL, judger_handler, (void *) arg);
            pthread_detach(thread_id);
        } else {
            LOG("Too many judgers, refuse to setup new handler.");
            // because it won't be deleted afterwards
            delete socket;
        }
    } else if (details[0] == CONFIG->Getsubmit_string()) {
        // format: submit_string\nrunid\nOJ
        LOG("Received a submit, runid: " + details[1] + ", oj: " + details[2]);
        insert_run(new Submit(NEED_JUDGE, stringToInt(details[1]), details[2]));
    } else if (details[0] == CONFIG->Getpretest_string()) {
        // format: pretest_string\nrunid\nOJ
        LOG("Received a pretest request, runid: " + details[1] + ", oj: " +
            details[2]);
        insert_run(new Submit(DO_PRETEST, stringToInt(details[1]), details[2]));
    } else if (details[0] == CONFIG->Geterror_rejudge_string()) {
        // format: error_rejudge_string\nrunid\nOJ
        LOG("Received an error rejudge request, runid: " + details[1] + ", oj: " +
            details[2]);
        insert_run(new Submit(NEED_JUDGE, stringToInt(details[1]), details[2]));
    } else if (details[0] == CONFIG->Getchallenge_string()) {
        // format: challenge_string\nchallenge_id\nOJ
        LOG("Received a challenge request, runid: " + details[1] + ", oj: " +
            details[2]);
        insert_run(new Submit(DO_CHALLENGE, stringToInt(details[1]), details[2]));
    } else if (details[0] == CONFIG->Getrejudge_string()) {
        // format: rejudge_string\nproblem_id\ncontest_id
        LOG("Received a rejudge request, pid: " + details[1] + ", cid: " +
            details[2]);
        DatabaseHandler *db = get_db_instance();
        vector<map<string, string> > result = db->Getall_results("\
            SELECT runid, vname \
            FROM   status, problem \
            WHERE  result='Rejudging' AND \
                   contest_belong='" + db->escape(details[2]) + "' AND \
                   status.pid='" + db->escape(details[1]) + "' AND \
                   status.pid=problem.pid \
            ORDER BY runid \
        ");
        delete db;
        for (vector<map<string, string> >::iterator it = result.begin();
             it != result.end(); ++it) {
            insert_run(new Submit(NEED_JUDGE, stringToInt((*it)["runid"]),
                                  (*it)["vname"]));
        }
    } else if (details[0] == CONFIG->Gettestall_string()) {
        // format: rejudge_string\ncontest_id
        LOG("Received a test all request, cid: " + details[1]);
        DatabaseHandler *db = get_db_instance();
        vector<map<string, string> > result = db->Getall_results("\
            SELECT runid, vname \
            FROM   status, problem \
            WHERE  result='Testing' AND \
                   contest_belong='" + db->escape(details[1]) + "' AND \
                   status.pid=problem.pid \
            ORDER BY runid \
        ");
        delete db;
        for (vector<map<string, string> >::iterator it = result.begin();
             it != result.end(); ++it) {
            insert_run(new Submit(NEED_JUDGE, stringToInt((*it)["runid"]),
                                  (*it)["vname"]));
        }
    } else {
        LOG("Illegal connection recieved: " + message);
    }

    // judger will reuse the socket
    if (details[0] != CONFIG->Getjudger_string()) {
        delete socket;
    }

    pthread_exit(NULL);
}

/**
 * Start the event loop, watch the connections
 */
void start_listener() {
    int client_fd;
    struct sockaddr_in remote_addr;
    socklen_t sin_size = sizeof(struct sockaddr_in);
    while (true) {
        if ((client_fd = accept(main_sockfd, (struct sockaddr *) &remote_addr,
                                &sin_size)) == -1) {
            throw Exception("Error in accept socket");
        }

        LOG((string) "Received a connection from " +
            inet_ntoa(remote_addr.sin_addr) + ":" +
            intToString(remote_addr.sin_port));
        pthread_t thread_id;
        int *arg = new int;
        *arg = client_fd;
        pthread_create(&thread_id, NULL, connection_handler, (void *) arg);
        pthread_detach(thread_id);
    }
}

/**
 * Dispatch tasks to corresponding judgers
 * @param NULL
 * @return NULL
 */
void *dispatch(void *) {
    LOGGER->addIdentifier(pthread_self(), "Fetcher");
    while (true) {
        usleep(61743); // sleep for a random time
        pthread_mutex_lock(&runs_mutex);
        for (list<Submit *>::iterator it = runs.begin(); it != runs.end(); ++it) {
            bool found = false;
            pthread_mutex_lock(&judgers_mutex);
            for (list<JudgerThread *>::iterator ij = judgers.begin();
                 ij != judgers.end(); ++ij) {
                if ((*ij)->Getcurrent_submit() == NULL &&
                    (*ij)->Getoj() == (*it)->Getoj()) {
                    // judger available and it can judge this task
                    found = true;
                    switch ((*it)->Gettype()) {
                        case NEED_JUDGE:
                            LOG("Dispatched runid: " + intToString((*it)->Getid()) +
                                " for " + (*it)->Getoj() + " to judge");
                            break;
                        case DO_CHALLENGE:
                            LOG("Dispatched chaid: " + intToString((*it)->Getid()) +
                                " for " + (*it)->Getoj() + " to judge");
                            break;
                        case DO_PRETEST:
                            LOG("Dispatched runid: " + intToString((*it)->Getid()) +
                                " for " + (*it)->Getoj() + " to pretest");
                            break;
                        case DO_TESTALL:
                            LOG("Dispatched runid: " + intToString((*it)->Getid()) +
                                " for " + (*it)->Getoj() + " to test all");
                            break;
                    }
                    (*ij)->Setcurrent_submit(*it);
                    runs.erase(it);
                    break;
                }
            }
            pthread_mutex_unlock(&judgers_mutex);
            if (found) break;
        }
        pthread_mutex_unlock(&runs_mutex);
    }
    pthread_exit(NULL);
}

int main() {

    init_network();
    runs.clear();

    // get runs that currently needed to be judged
    DatabaseHandler *db = get_db_instance();
    vector<map<string, string> > result = db->Getall_results("\
      SELECT runid,vname \
      FROM   status,problem \
      WHERE \
             (result='Waiting' OR result='Judging' OR result='Rejudging') AND \
             status.pid=problem.pid ORDER BY runid");

    for (vector<map<string, string> >::iterator it = result.begin();
         it != result.end(); ++it) {
        insert_run(new Submit(NEED_JUDGE, stringToInt((*it)["runid"]),
                              (*it)["vname"]));
    }
    result.clear();
    delete db;

    pthread_t tid;
    // start dispatch thread
    pthread_create(&tid, NULL, dispatch, NULL);
    pthread_detach(tid);
    // start event loop
    start_listener();

    return 0;
}
