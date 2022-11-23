#include <stdio.h>

char magic[1024 * 1024 * 1024] = {'\n'};

int main() {
    magic[0] = 'a';
    printf("test");
    return 0;
}