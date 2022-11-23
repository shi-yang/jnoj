#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

int main() {
    int chunk_size = 1024 * 1024;
    void *p = NULL;

    while (1) {
        if ((p = (int *) malloc((size_t) chunk_size)) == NULL) {
            break;
        }
        memset(p, 1, (size_t) chunk_size);
    }
    return 0;
}