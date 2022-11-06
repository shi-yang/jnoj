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
	files, _ := os.ReadDir("./test/compile")
	for _, f := range files {
		fmt.Println(f.Name())
		var l int
		for i, v := range Languages {
			if f.Name() == v.CodeFileName {
				l = i
			}
		}
		source := readSourceFile(filepath.Join("./test/compile", f.Name()))
		u, _ := uuid.NewUUID()
		fmt.Println(source)
		compilePath := filepath.Join(workDir, u.String())
		if err := Compile(compilePath, source, &Languages[l]); err != nil {
			t.Error("Compiled Error\n", err)
		}
	}
	os.RemoveAll(workDir)
}
