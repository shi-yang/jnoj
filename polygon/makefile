all: src/polygon.c src/judge.c
	gcc -Wall -c -I/usr/local/mysql/include/mysql -I/usr/include/mysql  src/polygon.c
	gcc -Wall -o polygon polygon.o -L/usr/local/mysql/lib/mysql -L/usr/lib/mysql  -L/usr/lib64/mysql  -lmysqlclient

	gcc -Wall -c -I/usr/local/mysql/include/mysql -I/usr/include/mysql src/judge.c
	gcc -Wall -o judge judge.o -L/usr/local/mysql/lib/mysql -L/usr/lib/mysql  -L/usr/lib64/mysql -lmysqlclient
	rm judge.o polygon.o