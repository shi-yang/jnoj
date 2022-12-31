//go:build linux && go1.19
// +build linux,go1.19

package container

import (
	"fmt"
	"io"
	"os"
	"path/filepath"
	"syscall"

	"github.com/criyle/go-sandbox/pkg/mount"
)

// InitNamespace .
func InitNamespace(workDir string) error {
	_, _ = os.Stderr.WriteString(fmt.Sprintf("InitNamespace(%s) Starting...\n", workDir))

	if err := initFileSystem(workDir); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("pivotRoot(%s) failed, err: %s\n", workDir, err.Error()))
		return err
	}

	if err := syscall.Sethostname([]byte("sandbox")); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("syscall.Sethostname failed, err: %s\n", err.Error()))
		return err
	}

	_, _ = os.Stderr.WriteString(fmt.Sprintf("InitNamespace(%s) Started\n", workDir))
	return nil
}

func initFileSystem(workDir string) error {
	// 旧文件名
	files, _ := os.ReadDir(workDir)
	containerDir := filepath.Join(workDir, "container")
	if err := os.RemoveAll(containerDir); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("os.RemoveAll(%v):  %v\n", containerDir, err))
	}
	if err := os.Mkdir(containerDir, os.ModePerm); err != nil {
		return fmt.Errorf("os.Mkdir(%v): %v", containerDir, err)
	}
	const tmpfs = "tmpfs"
	if err := syscall.Mount(tmpfs, containerDir, tmpfs, 0, ""); err != nil {
		return fmt.Errorf("syscall.Mount(tmpfs, %v, tmpfs, 0, \"\"): %v", containerDir, err)
	}
	if err := syscall.Chdir(containerDir); err != nil {
		return fmt.Errorf("syscall.Chdir(%v): %v", containerDir, err)
	}
	mb := mount.NewBuilder().
		// basic exec and lib
		WithBind("/bin", filepath.Join(containerDir, "bin"), true).
		WithBind("/lib", filepath.Join(containerDir, "lib"), true).
		WithBind("/lib64", filepath.Join(containerDir, "lib64"), true).
		WithBind("/usr", filepath.Join(containerDir, "usr"), true).
		WithProc().
		WithBind("/etc/alternatives", filepath.Join(containerDir, "etc/alternatives"), true).
		WithBind("/etc/fpc.cfg", filepath.Join(containerDir, "etc/fpc.cfg"), true).
		WithBind("/dev/null", filepath.Join(containerDir, "dev/null"), false).
		WithBind("/var/lib/ghc", filepath.Join(containerDir, "var/lib/ghc"), true).
		WithBind("/work", filepath.Join(containerDir, "work"), true).
		WithTmpfs(filepath.Join(containerDir, "work"), "size=128m,nr_inodes=4k").
		WithTmpfs(filepath.Join(containerDir, "tmp"), "size=128m,nr_inodes=4k")
	_, err := mb.FilterNotExist().Build()
	if err != nil {
		return err
	}
	for _, m := range mb.Mounts {
		if err := m.Mount(); err != nil {
			_, _ = os.Stderr.WriteString(fmt.Sprintf("init_fs: mount %v %v\n", m, err))
		}
	}
	for _, f := range files {
		if f.IsDir() {
			continue
		}
		err := copy(filepath.Join(workDir, f.Name()), filepath.Join(workDir, "container", "work", f.Name()))
		if err != nil {
			os.Stderr.WriteString(fmt.Sprintf("copy(filepath.Join(workDir, f.Name()), %+v\n", err))
		}
	}
	putOld := ".pivot_root"
	if err := os.MkdirAll(putOld, 0700); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("os.MkdirAll(%s, 0700) failed\n", putOld))
		return err
	}
	if err := syscall.PivotRoot(containerDir, putOld); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("syscall.PivotRoot(%s, %s) failed\n", containerDir, putOld))
		return err
	}
	if err := syscall.Unmount(putOld, syscall.MNT_DETACH); err != nil {
		return fmt.Errorf("syscall.Unmount(%v, syscall.MNT_DETACH) %v", putOld, err)
	}
	if err := os.Remove(putOld); err != nil {
		return fmt.Errorf("os.Remove(%v): %v", putOld, err)
	}
	if err := os.Chdir("work"); err != nil {
		return fmt.Errorf("os.Chdir(\"work\") %v", err)
	}
	return nil
}

func copy(source, dst string) error {
	sf, err := os.Open(source)
	if err != nil {
		return err
	}
	df, err := os.OpenFile(dst, os.O_RDWR|os.O_CREATE, os.ModePerm)
	if err != nil {
		return err
	}
	defer sf.Close()
	defer df.Close()
	_, err = io.Copy(df, sf)
	return err
}
