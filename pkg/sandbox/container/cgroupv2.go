package container

import (
	"errors"
	"fmt"
	"os"
	"path"
	"path/filepath"
	"strings"
)

const (
	rootPathPrefix  = "/sys/fs/cgroup/"
	subtreeControl  = "cgroup.subtree_control"
	controllersFile = "cgroup.controllers"
	cgroupProcs     = "cgroup.procs"
	initPath        = "init"
)

func init() {
	Initcgroupv2()
}

func Initcgroupv2() error {
	if !IsOnlyV2() {
		return nil
	}
	p, err := os.ReadFile(path.Join(rootPathPrefix, cgroupProcs))
	if err != nil {
		return err
	}
	procs := strings.Split(string(p), "\n")
	if len(procs) == 0 {
		return nil
	}
	// mkdir init
	if err := os.Mkdir(path.Join(rootPathPrefix, initPath), 0755); err != nil && !errors.Is(err, os.ErrExist) {
		fmt.Printf("mkdir init(%s) failed, err: %s\n", rootPathPrefix, err.Error())
		return err
	}
	// move all process into init cgroup
	procFile, err := os.OpenFile(path.Join(rootPathPrefix, initPath, cgroupProcs), os.O_RDWR, 0644)
	if err != nil {
		fmt.Printf("move all process into init cgroup(%s) failed, err: %s\n", rootPathPrefix, err.Error())
		return err
	}
	for _, v := range procs {
		procFile.WriteString(v)
	}
	procFile.Close()

	controllers, err := getcgroupV2AvailableController()
	if err != nil {
		fmt.Printf("getcgroupV2AvailableController(%s) failed, err: %s\n", rootPathPrefix, err.Error())
		return err
	}
	controlMsg := []byte("+" + strings.Join(controllers, " +"))
	if err := os.WriteFile(path.Join(rootPathPrefix, subtreeControl), controlMsg, os.ModePerm); err != nil {
		fmt.Printf("os.WriteFile(%s, %s, os.ModePerm) failed, err: %s\n", rootPathPrefix, subtreeControl, err.Error())
		return err
	}
	return nil
}

// cgroupV2
// https://www.kernel.org/doc/Documentation/cgroup-v2.txt
// https://docs.kernel.org/admin-guide/cgroup-v2.html
type cgroupV2 struct{}

// Install creates and configures cgroups.
func (c *cgroupV2) Install(pid, containerID, memory string) error {
	_, _ = os.Stderr.WriteString(fmt.Sprintf("InitCGroup(%s, %s, %s) Starting...\n", pid, containerID, memory))

	if err := os.MkdirAll(filepath.Join(rootPathPrefix, containerID), os.ModePerm); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("os.MkdirAll(%s, os.ModePerm) failed, err: %s\n", filepath.Join(rootPathPrefix, containerID), err.Error()))
		return err
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

func (c *cgroupV2) Uninstall(containerID string) {
	if err := os.RemoveAll(filepath.Join(rootPathPrefix, containerID)); err != nil {
		_, _ = os.Stderr.WriteString(fmt.Sprintf("os.RemoveAll(%s) failed, err: %s\n", containerID, err.Error()))
	}
}

func (c *cgroupV2) cpuCGroup(pid, containerID string) error {
	cgCPUPath := filepath.Join(rootPathPrefix, containerID)
	mapping := map[string]string{}

	for key, value := range mapping {
		path := filepath.Join(cgCPUPath, key)
		if err := os.WriteFile(path, []byte(value), 0644); err != nil {
			_, _ = os.Stderr.WriteString(fmt.Sprintf("Writing [%s] to file: %s failed\n", value, path))
			return err
		}
	}
	return nil
}

func (c *cgroupV2) pidCGroup(pid, containerID string) error {
	cgPidPath := filepath.Join(rootPathPrefix, containerID)
	mapping := map[string]string{
		"cgroup.procs": pid,
		//"pids.max":     "64",
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

func (c *cgroupV2) memoryCGroup(pid, containerID, memory string) error {
	cgMemoryPath := filepath.Join(rootPathPrefix, containerID)
	mapping := map[string]string{
		"memory.max": fmt.Sprintf("%sm", memory),
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

// getcgroupV2AvailableController /sys/fs/cgroup/cgroup.controllers to get all controller
func getcgroupV2AvailableController() ([]string, error) {
	c, err := os.ReadFile(path.Join(rootPathPrefix, controllersFile))
	if err != nil {
		return nil, err
	}
	return strings.Fields(string(c)), nil
}
