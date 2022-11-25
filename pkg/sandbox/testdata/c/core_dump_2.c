#include <stdio.h>
int main() {
    int tmp[5] = {1, 2, 3};
    for (int i = 0; i < 10; i++) {
        tmp[i] = 0;
    }
    for (int i = 0; i < 10; i++) {
        printf("%d", tmp[i]);
    }
    return 0;
}