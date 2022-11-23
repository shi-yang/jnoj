#include <stdio.h>
#include <string.h>
#include <netdb.h>
#include <arpa/inet.h>

int main(int argc, char *argv[]) {
    char *hostname = "www.amazon.com";
    char ip[100];
    struct hostent *he;
    struct in_addr **addr_list;

    if ((he = gethostbyname(hostname)) == NULL) {
        printf("gethostbyname error");
        return 1;
    }

    addr_list = (struct in_addr **) he->h_addr_list;

    for (int i = 0; addr_list[i] != NULL; i++) {
        strcpy(ip, inet_ntoa(*addr_list[i]));
    }

    printf("ip is: %s", ip);
    return 0;
}