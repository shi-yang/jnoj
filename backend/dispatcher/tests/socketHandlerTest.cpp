/* 
 * File:   socketHandlerTest.cpp
 * Author: 51isoft
 *
 * Created on 2014-1-30, 0:53:59
 */

#include <stdlib.h>
#include <iostream>

#include "SocketHandler.h"

/*
 * Simple C++ Test Suite
 */

void server(int &sockfd) {
    struct sockaddr_in remote_addr;
    socklen_t sin_size = sizeof(struct sockaddr_in);

    int client_fd = accept(sockfd, (struct sockaddr *) &remote_addr, &sin_size);

    cout << "Received a connection from " << inet_ntoa(remote_addr.sin_addr) << ":" << intToString(remote_addr.sin_port)
         << endl;
    SocketHandler *socket = new SocketHandler(client_fd);
    socket->receiveFile("tests/test-recv.bott");
    delete socket;
}

void client() {
    int sockfd = socket(AF_INET, SOCK_STREAM, 0);
    struct sockaddr_in server;
    bzero(&server, sizeof(server));
    server.sin_family = AF_INET;
    server.sin_port = htons(6636);
    server.sin_addr.s_addr = inet_addr("127.0.0.1");
    connect(sockfd, (struct sockaddr *) &server, sizeof(server));

    SocketHandler *socket = new SocketHandler(sockfd);
    socket->sendFile("tests/test.bott");
    delete socket;
}

void init_network(int &sockfd) {
    sockfd = socket(AF_INET, SOCK_STREAM, 0);

    struct sockaddr_in my_addr;
    my_addr.sin_family = AF_INET;
    my_addr.sin_port = htons(6636);
    my_addr.sin_addr.s_addr = INADDR_ANY;
    bzero(&(my_addr.sin_zero), 8);

    bind(sockfd, (struct sockaddr *) &my_addr, sizeof(struct sockaddr));
    listen(sockfd, 5);
}

void test1() {
    std::cout << "socketHandlerTest test 1" << std::endl;
    int sockfd;

    init_network(sockfd);
    if (fork() == 0) {
        // child process
        sleep(1);
        client();
    } else {
        server(sockfd);
    }
}

//void test2() {
//    std::cout << "socketHandlerTest test 2" << std::endl;
//    std::cout << "%TEST_FAILED% time=0 testname=test2 (socketHandlerTest) message=error message sample" << std::endl;
//}

int main(int argc, char **argv) {
    std::cout << "%SUITE_STARTING% socketHandlerTest" << std::endl;
    std::cout << "%SUITE_STARTED%" << std::endl;

    std::cout << "%TEST_STARTED% test1 (socketHandlerTest)" << std::endl;
    test1();
    std::cout << "%TEST_FINISHED% time=0 test1 (socketHandlerTest)" << std::endl;

    //    std::cout << "%TEST_STARTED% test2 (socketHandlerTest)\n" << std::endl;
    //    test2();
    //    std::cout << "%TEST_FINISHED% time=0 test2 (socketHandlerTest)" << std::endl;

    std::cout << "%SUITE_FINISHED% time=0" << std::endl;

    return (EXIT_SUCCESS);
}

