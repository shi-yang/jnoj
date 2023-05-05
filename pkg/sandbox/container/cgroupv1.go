package container

import (
	"fmt"
	"os"
	"path/filepath"
)

const (
	cgv1CPUPathPrefix    = "/sys/fs/cgroup/cpu/"
	cgv1PidPathPrefix    = "/sys/fs/cgroup/pids/"
	cgv1MemoryPathPrefix = "/sys/fs/cgroup/memory/"
)

// cgroupV1 https://www.kernel.org/doc/Documentation/cgroup-v1/
type cgroupV1 struct{}

// Install creates and configures cgroups.
func (c *cgroupV1) Install(pid, containerID, memory string) error {
	_, _ = os.Stderr.WriteString(fmt.Sprintf("InitCGroup(%s, %s, %s) Starting...\n", pid, containerID, memory))

	dirs := []string{
		filepath.Join(cgv1CPUPathPrefix, containerID),
		filepath.Join(cgv1PidPathPrefix, containerID),
		filepath.Join(cgv1MemoryPathPrefix, containerID),
	}

	for _, dir := range dirs {
		if err := os.MkdirAll(dir, os.ModePerm); err != nil {
			_, _ = os.Stderr.WriteString(fmt.Sprintf("os.MkdirAll(%s, os.ModePerm) failed, err: %s\n", dir, err.Error()))
			return err
		}
	}

	if err := c.cpuCGroup(pid, containerID); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("cpuCGroup(%s, %s) failed, err: %s\n", pid, containerID, err.Error()))
		return err
	}

	if err := c.pidCGroup(pid, containerID); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("pidCGroup(%s, %s) failed, err: %s\n", pid, containerID, err.Error()))
		return err
	}

	if err := c.memoryCGroup(pid, containerID, memory); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("memoryCGroup(%s, %s) failed, err: %s\n", pid, containerID, err.Error()))
		return err
	}

	_, _ = os.Stderr.WriteString(fmt.Sprintf("InitCGroup(%s, %s, %s) Started\n", pid, containerID, memory))
	return nil
}

func (c *cgroupV1) Uninstall(containerID string) {
	dirs := []string{
		filepath.Join(cgv1CPUPathPrefix, containerID),
		filepath.Join(cgv1PidPathPrefix, containerID),
		filepath.Join(cgv1MemoryPathPrefix, containerID),
	}
	for _, dir := range dirs {
		if err := os.RemoveAll(dir); err != nil {
			_, _ = os.Stderr.WriteString(fmt.Sprintf("os.RemoveAll(%s) failed, err: %s\n", dir, err.Error()))
		}
	}
}

// https://www.kernel.org/doc/Documentation/scheduler/sched-bwc.txt
func (c *cgroupV1) cpuCGroup(pid, containerID string) error {
	cgCPUPath := filepath.Join(cgv1CPUPathPrefix, containerID)
	mapping := map[string]string{
		"tasks":            pid,
		"cpu.cfs_quota_us": "10000",
	}

	for key, value := range mapping {
		path := filepath.Join(cgCPUPath, key)
		if err := os.WriteFile(path, []byte(value), 0644); err != nil {
			_, _ = os.Stderr.WriteString(fmt.Sprintf("Writing [%s] to file: %s failed\n", value, path))
			return err
		}
	}
	return nil
}

// https://www.kernel.org/doc/Documentation/cgroup-v1/pids.txt
func (c *cgroupV1) pidCGroup(pid, containerID string) error {
	cgPidPath := filepath.Join(cgv1PidPathPrefix, containerID)
	mapping := map[string]string{
		"cgroup.procs": pid,
		"pids.max":     "64",
	}

	for key, value := range mapping {
		path := filepath.Join(cgPidPath, key)
		if err := os.WriteFile(path, []byte(value), 0644); err != nil {
			_, _ = os.Stderr.WriteString(fmt.Sprintf("Writing [%s] to file: %s failed\n", value, path))
			return err
		}
	}
	return nil
}

// https://www.kernel.org/doc/Documentation/cgroup-v1/memory.txt
func (c *cgroupV1) memoryCGroup(pid, containerID, memory string) error {
	cgMemoryPath := filepath.Join(cgv1MemoryPathPrefix, containerID)
	mapping := map[string]string{
		"memory.kmem.limit_in_bytes": "64m",
		"tasks":                      pid,
		"memory.limit_in_bytes":      fmt.Sprintf("%sm", memory),
	}

	for key, value := range mapping {
		path := filepath.Join(cgMemoryPath, key)
		if err := os.WriteFile(path, []byte(value), 0644); err != nil {
			_, _ = os.Stderr.WriteString(fmt.Sprintf("Writing [%s] to file: %s failed\n", value, path))
			return err
		}
	}
	return nil
}
