package testlib

import (
	"embed"
	"fmt"
	"os"
	"path/filepath"
	"sync"
)

//go:embed testlib.h
var testlibEmbed embed.FS

var (
	testlibPathOnce sync.Once
	testlibPath     string
	testlibPathErr  error
)

// GetTestlibPath 获取 testlib 路径，如果不存在则从嵌入的文件中提取到临时目录
func GetTestlibPath() (string, error) {
	testlibPathOnce.Do(func() {
		// 创建临时目录
		tmpDir := "/tmp/sandbox/testlib"
		if err := os.MkdirAll(tmpDir, 0755); err != nil {
			testlibPathErr = fmt.Errorf("failed to create testlib directory: %w", err)
			return
		}

		// 读取嵌入的 testlib.h 文件
		data, err := testlibEmbed.ReadFile("testlib.h")
		if err != nil {
			testlibPathErr = fmt.Errorf("failed to read embedded testlib.h: %w", err)
			return
		}

		// 写入到临时目录
		testlibFile := filepath.Join(tmpDir, "testlib.h")
		if err := os.WriteFile(testlibFile, data, 0644); err != nil {
			testlibPathErr = fmt.Errorf("failed to write testlib.h to %s: %w", testlibFile, err)
			return
		}

		testlibPath = tmpDir
	})
	return testlibPath, testlibPathErr
}
