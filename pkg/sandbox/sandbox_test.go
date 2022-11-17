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

func TestLangC(t *testing.T) {
	sourceFiles, _ := os.ReadDir("./test/c")
	for _, f := range sourceFiles {
		source := readSourceFile("./test/c/" + f.Name())
		log.Printf("filename start:[%s]\n", f.Name())
		if err := Compile(workDir, source, &Languages[LANG_C]); err != nil {
			t.Error("Compiled Error:", err)
		} else {
			input, _ := os.ReadFile("./test/data/1.in")
			res := Run(workDir, &Languages[LANG_C], input, 256, 1000)
			log.Printf("filename result:[%s] result=[%+v]\n", f.Name(), res)
		}
		log.Printf("filename done:[%s]\n", f.Name())
		log.Println("==============================")
	}
}
