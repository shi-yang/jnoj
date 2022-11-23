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

func TestCAccepted(t *testing.T) {
	source := readSourceFile("./testdata/c/ac.c")
	if err := Compile(workDir, source, &Languages[LANG_C]); err != nil {
		t.Error("Compiled Error\n", err)
	}
	input, _ := os.ReadFile("./testdata/data/1.in")
	excepted, _ := os.ReadFile("./testdata/data/1.out")
	res := Run(workDir, &Languages[LANG_C], input, 256, 1000)
	if res.Stdout != string(excepted) {
		t.Error("Wrong Answer")
	}
}

func TestLangC(t *testing.T) {
	// TODO 本测试样例待完善
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
				expected := "Floating point exception"
				return fmt.Sprintf("expected runtimeErr=[%s], got=[%+v]\n", expected, res), strings.Contains(res.RuntimeErr, expected)
			},
		},
		{
			"core_dump_2.c",
			func(res *Result) (string, bool) {
				expected := "*** stack smashing detected ***: terminated"
				return fmt.Sprintf("expected runtimeErr=[%s], got=[%+v]\n", expected, res), strings.Contains(res.RuntimeErr, expected)
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
				return fmt.Sprintf("%+v\n", res), res.ExitCode == 1
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
				return fmt.Sprintf("expected memory out, got=[%+v]\n", res.Memory), res.Memory > 256*1024
			},
		},
		{
			"run_command_line_0.c",
			func(res *Result) (string, bool) {
				return fmt.Sprintf("expected [cannot remove], got=[%+v]\n", res.Stderr), strings.Contains(res.Stderr, "cannot remove")
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
				// TODO 偶尔会异常
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
			if err := Compile(workPath, source, &Languages[LANG_C]); err != nil {
				t.Errorf("compile error. err = [%s]", err.Error())
			}
			res := Run(workPath, &Languages[LANG_C], []byte(""), 256, 1000)
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
