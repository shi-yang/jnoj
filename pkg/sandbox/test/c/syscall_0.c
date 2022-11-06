#include <stdio.h>
#include <sys/signal.h>

int main() {
    kill(1, SIGSEGV);
    return 0;
}