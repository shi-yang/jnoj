package sandbox

import (
	"fmt"
	"os"
	"path/filepath"
	"strings"
	"testing"
	"unicode"

	"github.com/google/uuid"
)

func TestRunHelloWorld(t *testing.T) {
	helloworldFiles, _ := os.ReadDir("./testdata/helloworld")
	for _, f := range helloworldFiles {
		t.Run(f.Name(), func(t *testing.T) {
			t.Logf("Test Hello World: [%s]\n", f.Name())
			var l int
			for i, v := range Languages {
				if f.Name() == v.CodeFileName {
					l = i
				}
			}
			source := readSourceFile(filepath.Join("./testdata/helloworld", f.Name()))
			u, _ := uuid.NewUUID()
			workPath := filepath.Join(workDir, u.String())
			if err := Compile(workPath, source, &Languages[l]); err != nil {
				t.Error("Compiled Error\n", err)
			}
			expected := "Hello, world"
			res := Run(workPath, &Languages[l], []byte(""), 256, 1000)
			if expected != strings.TrimFunc(res.Stdout, func(r rune) bool {
				return !unicode.IsLetter(r) && !unicode.IsNumber(r)
			}) {
				t.Errorf("Wrong Answer. Expeted: [%s], got: [%s]", expected, res.Stdout)
			}
		})
	}
	os.RemoveAll(workDir)
}

// TestCAccepted
func TestCAccepted(t *testing.T) {
	u, _ := uuid.NewUUID()
	workPath := filepath.Join(workDir, u.String())
	source := readSourceFile("./testdata/accepted/main.c")
	if err := Compile(workPath, source, &Languages[LangC]); err != nil {
		t.Error("Compiled Error\n", err)
	}
	tests := []struct {
		input    []byte
		expected []byte
	}{
		{
			input:    []byte("1 2"),
			expected: []byte("3"),
		},
		{
			input:    []byte("2 3"),
			expected: []byte("5"),
		},
		{
			input:    []byte("0 3"),
			expected: []byte("3"),
		},
	}
	for _, test := range tests {
		res := Run(workPath, &Languages[LangC], test.input, 256, 1000)
		if res.Stdout != string(test.expected) {
			t.Error("Wrong Answer")
		}
	}
}

func TestChecker(t *testing.T) {
	u, _ := uuid.NewUUID()
	workPath := filepath.Join(workDir, u.String())
	source := readSourceFile("./testdata/accepted/main.c")
	if err := Compile(workPath, source, &Languages[LangC]); err != nil {
		t.Error("Compiled Error\n", err)
	}
	tests := []struct {
		input    []byte
		expected []byte
	}{
		{
			input:    []byte("1 2"),
			expected: []byte("3"),
		},
		{
			input:    []byte("2 3"),
			expected: []byte("5"),
		},
		{
			input:    []byte("0 3"),
			expected: []byte("3"),
		},
	}
	checkerSource := readSourceFile("./testdata/checker/checker.cpp")
	testlib, _ := os.ReadFile("./testdata/checker/testlib.h")
	os.WriteFile(filepath.Join(workPath, "testlib.h"), testlib, os.ModePerm)
	checkerLanguage := &Language{
		Name: "checker",
		CompileCommand: []string{"g++", "checker.cpp", "-o", "checker.exe", "-I./", "-Wall",
			"-fno-asm", "-O2", "-lm", "--static", "-std=c++11", "-DONLINE_JUDGE", "-save-temps", "-fmax-errors=10"},
		RunCommand:   []string{"./checker.exe", "data.in", "user.stdout", "data.out"},
		CodeFileName: "checker.cpp",
		IsVMRun:      false,
	}
	if err := Compile(workPath, checkerSource, checkerLanguage); err != nil {
		t.Error("Compiled Error\n", err)
	}
	for _, test := range tests {
		runRes := Run(workPath, &Languages[LangC], test.input, 256, 1000)
		t.Logf("Program output:[%+v]\n", runRes)
		if runRes.RuntimeErr == "" {
			// 准备运行 checker 所需文件
			_ = os.WriteFile(filepath.Join(workPath, "user.stdout"), []byte(runRes.Stdout), 0444)
			_ = os.WriteFile(filepath.Join(workPath, "data.in"), test.input, 0444)
			_ = os.WriteFile(filepath.Join(workPath, "data.out"), test.expected, 0444)
			// 执行 checker
			t.Log("Run checker:", workPath)
			checkerRes := Run(workPath, checkerLanguage, []byte(""), 256, 10000)
			t.Logf("Checker output:[%+v]\n", checkerRes)
		}
	}
}

// TODO 本测试样例待完善
func TestLangC(t *testing.T) {
	files := []struct {
		name     string
		expected func(res *Result) (string, bool)
	}{
		{
			"core_dump_0.c",
			func(res *Result) (string, bool) {
				expected := "signal: killed"
				return fmt.Sprintf("expected runtimeErr=[%s], got=[%s]", expected, res.RuntimeErr),
					res.RuntimeErr == expected
			},
		},
		{
			"core_dump_1.c",
			func(res *Result) (string, bool) {
				expected := "illegal instruction"
				return fmt.Sprintf("expected runtimeErr=[%s], got=[%+v]\n", expected, res.RuntimeErr), strings.Contains(res.RuntimeErr, expected)
			},
		},
		{
			"core_dump_2.c",
			func(res *Result) (string, bool) {
				expected := "aborted"
				return fmt.Sprintf("expected runtimeErr=[%s], got=[%+v]\n", expected, res.RuntimeErr), strings.Contains(res.RuntimeErr, expected)
			},
		},
		{
			"fork_bomb_0.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("expected timeout, got=[%+v]\n", res.Time), res.Time > 20000
			},
		},
		{
			"fork_bomb_1.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("expected timeout, got=[%+v]\n", res.Time), res.Time > 1000000
			},
		},
		{
			"get_host_by_name.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("expected runtimeErr not empty, got=[%+v]\n", res.RuntimeErr), res.RuntimeErr != ""
			},
		},
		{
			"infinite_loop.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("expected timeout, got=[%+v]\n", res.Time), res.Time > 1000000
			},
		},
		{
			"memory_allocation.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("expected memory limit [%d], got=[%+v]\n", 256*1024, res.Memory), res.Memory > 256*1024
			},
		},
		{
			"run_command_line_0.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("expected [cannot remove], got=[%+v]\n", res), strings.Contains(res.Stderr, "cannot remove")
			},
		},
		{
			"run_command_line_1.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("expected [shutdown: not found], got=[%+v]\n", res.Stderr), strings.Contains(res.Stderr, "shutdown: not found")
			},
		},
		{
			"syscall_0.c",
			func(res *Result) (string, bool) {
				// TODO 有问题
				return fmt.Sprintf("%+v\n", res), false
			},
		},
		{
			"tcp_client.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("%+v\n", res), res.ExitCode == 1
			},
		},
	}
	for _, test := range files {
		t.Run(test.name, func(t *testing.T) {
			t.Logf("Compile file:%s\n", test.name)
			source := readSourceFile(filepath.Join("./testdata/c", test.name))
			u, _ := uuid.NewUUID()
			workPath := filepath.Join(workDir, u.String())
			if err := Compile(workPath, source, &Languages[LangC]); err != nil {
				t.Errorf("compile error. err = [%s]", err.Error())
			}
			res := Run(workPath, &Languages[LangC], []byte(""), 256, 1000)
			msg, ok := test.expected(res)
			if ok {
				t.Log("ok", msg)
			} else {
				t.Error("fail", msg)
			}
		})
	}
	os.RemoveAll(workDir)
}

func TestLangJava(t *testing.T) {
	files := []struct {
		name     string
		expected func(res *Result) (string, bool)
	}{}
	for _, test := range files {
		t.Run(test.name, func(t *testing.T) {
			t.Logf("Compile file:%s\n", test.name)
			source := readSourceFile(filepath.Join("./testdata/java", test.name))
			u, _ := uuid.NewUUID()
			workPath := filepath.Join(workDir, u.String())
			if err := Compile(workPath, source, &Languages[LangJava]); err != nil {
				t.Errorf("compile error. err = [%s]", err.Error())
			}
			res := Run(workPath, &Languages[LangJava], []byte(""), 256, 1000)
			msg, ok := test.expected(res)
			if ok {
				t.Log("ok", msg)
			} else {
				t.Error("fail", msg)
			}
		})
	}
	os.RemoveAll(workDir)
}

func BenchmarkCompile(b *testing.B) {
	b.ResetTimer()
	for i := 0; i < b.N; i++ {
		u, _ := uuid.NewUUID()
		workPath := filepath.Join(workDir, u.String())
		source := readSourceFile("./testdata/accepted/main.c")
		if err := Compile(workPath, source, &Languages[LangC]); err != nil {
			b.Error("Compiled Error\n", err)
		}
		os.RemoveAll(workPath)
	}
}

func BenchmarkRun(b *testing.B) {
	u, _ := uuid.NewUUID()
	workPath := filepath.Join(workDir, u.String())
	source := readSourceFile("./testdata/accepted/main.c")
	if err := Compile(workPath, source, &Languages[LangC]); err != nil {
		b.Error("Compiled Error\n", err)
	}
	input := "1 2"
	excepted := "3"
	b.ResetTimer()
	for i := 0; i < b.N; i++ {
		res := Run(workPath, &Languages[LangC], []byte(input), 256, 1000)
		if res.Stdout != excepted {
			b.Errorf("excepted %s, got %s", input, excepted)
		}
	}
	os.RemoveAll(workPath)
}
