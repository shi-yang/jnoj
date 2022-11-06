#include <stdio.h>
#include <sys/socket.h>
#include <arpa/inet.h>

int main(int argc, char *argv[]) {
    int sock;
    struct sockaddr_in server;
    char message[1000], server_reply[2000];

    sock = socket(AF_INET, SOCK_STREAM, 0);
    if (sock == -1) {
        printf("create socket failed");
        return 1;
    }

    server.sin_addr.s_addr = inet_addr("54.182.3.77");
    server.sin_family = AF_INET;
    server.sin_port = htons(80);

    if (connect(sock, (struct sockaddr *) &server, sizeof(server)) < 0) {
        printf("connect failed");
        return 1;
    }

    printf("connected");
    close(sock);
    return 0;
}
