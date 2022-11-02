package main

import "testing"

func TestLoadAPIConfigFromYAML(t *testing.T) {
	_, err := loadAPIConfigFromYAML("./apiconfig/apiconfig_test.yaml")
	if err != nil {
		t.Error(err)
	}
}
