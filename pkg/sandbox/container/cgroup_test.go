package container

import (
	"os"
	"strconv"
	"testing"
)

func TestCgroupInstall(t *testing.T) {
	err := Newcgroup().Install(strconv.Itoa(os.Getpid()), "test2", "64")
	if err != nil {
		t.Error(err)
	}
}
