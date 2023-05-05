package container

import "golang.org/x/sys/unix"

const (
	// cgroupRoot is the cgroupfs root this module uses.
	cgroupRoot = "/sys/fs/cgroup"
)

// Cgroup represents a cgroup configuration.
type Cgroup interface {
	Install(pid, containerID, memory string) error
	Uninstall(containerID string)
}

func Newcgroup() Cgroup {
	if IsOnlyV2() {
		return &cgroupV2{}
	}
	return &cgroupV1{}
}

// IsOnlyV2 checks whether cgroups V2 is enabled and V1 is not.
func IsOnlyV2() bool {
	var stat unix.Statfs_t
	if err := unix.Statfs(cgroupRoot, &stat); err != nil {
		return false
	}
	return stat.Type == unix.CGROUP2_SUPER_MAGIC
}
