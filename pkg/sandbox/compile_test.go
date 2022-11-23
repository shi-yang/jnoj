package sandbox

import (
	"fmt"
	"os"
	"path/filepath"
	"testing"

	"github.com/google/uuid"
)

const workDir = "/tmp/sandbox/test/"

func readSourceFile(path string) string {
	if f, err := os.ReadFile(path); err == nil {
		return string(f)
	}
	return ""
}

func TestCompile(t *testing.T) {
	helloworldFiles, _ := os.ReadDir("./testdata/helloworld")
	t.Log("Compile [hello, world]:")
	for _, f := range helloworldFiles {
		t.Logf("Compile file:%s\n", f.Name())
		var l int
		for i, v := range Languages {
			if f.Name() == v.CodeFileName {
				l = i
			}
		}
		source := readSourceFile(filepath.Join("./testdata/helloworld", f.Name()))
		u, _ := uuid.NewUUID()
		compilePath := filepath.Join(workDir, u.String())
		if err := Compile(compilePath, source, &Languages[l]); err != nil {
			t.Error("Compiled Error\n", err)
		}
	}
	t.Log("Compile [bomb]:")
	files := []struct {
		name     string
		expected func(err error) (string, bool)
	}{
		{
			"compiler_bomb_0.c",
			func(err error) (string, bool) {
				return fmt.Sprintf("expected [%s], got [%+v]", "", err), err == nil
			},
		},
		{
			"compiler_bomb_1.c",
			func(err error) (string, bool) {
				return fmt.Sprintf("expected [%s], got [%+v]", "compile timeout", err), err != nil
			},
		},
		{
			"compiler_bomb_2.c",
			func(err error) (string, bool) {
				return fmt.Sprintf("expected [%s], got [%+v]", "compile timeout", err), err != nil
			},
		},
		{
			"compiler_bomb_3.c",
			func(err error) (string, bool) {
				return fmt.Sprintf("expected [%s], got [%+v]", "", err), err == nil
			},
		},
		{
			"include_leads.c",
			func(err error) (string, bool) {
				return fmt.Sprintf("expected [%s], got [%+v]", "compile error", err), err != nil
			},
		},
		{
			"plain_text.c",
			func(err error) (string, bool) {
				return fmt.Sprintf("expected [%s], got [%+v]", "", err), err != nil
			},
		},
	}
	for _, test := range files {
		t.Run(test.name, func(t *testing.T) {
			t.Logf("Compile file:%s\n", test.name)
			source := readSourceFile(filepath.Join("./testdata/compile", test.name))
			u, _ := uuid.NewUUID()
			compilePath := filepath.Join(workDir, u.String())
			err := Compile(compilePath, source, &Languages[LANG_C])
			msg, ok := test.expected(err)
			if ok {
				t.Log(msg)
			} else {
				t.Error(msg)
			}
		})
	}
	os.RemoveAll(workDir)
}
