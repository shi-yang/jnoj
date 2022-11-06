package sandbox

import (
	"log"
	"os"
	"testing"
)

func TestCAccepted(t *testing.T) {
	source := readSourceFile("./test/c/ac.c")
	if err := Compile(workDir, source, &Languages[LANG_C]); err != nil {
		t.Error("Compiled Error\n", err)
	}
	input, _ := os.ReadFile("./test/data/1.in")
	excepted, _ := os.ReadFile("./test/data/1.out")
	res := Run(workDir, &Languages[LANG_C], input, 256, 1000)
	if res.Stdout != string(excepted) {
		t.Error("Wrong Answer")
	}
}

func TestRunJava(t *testing.T) {
	source := readSourceFile("./test/java/hello.java")
	if err := Compile(workDir, source, &Languages[LANG_JAVA]); err != nil {
		t.Error("Compiled Error\n", err)
	}
	res := Run(workDir, &Languages[LANG_JAVA], []byte(""), 256, 1000)
	log.Printf("%+v\n", res)
}

func TestRunPython(t *testing.T) {
	source := readSourceFile("./test/python/hello.py")
	if err := Compile(workDir, source, &Languages[LANG_PYTHON3]); err != nil {
		t.Error("Compiled Error\n", err)
	}
	res := Run(workDir, &Languages[LANG_PYTHON3], []byte(""), 256, 1000)
	log.Printf("%+v\n", res)
}
