package main

import (
	"flag"
	"fmt"
)

var t string
var name string

func init() {
	flag.StringVar(&t, "t", "biz", "eg: -t biz or -t data")
	flag.StringVar(&name, "name", "Test", "eg: -name test")
}

func main() {
	flag.Parse()
	var bizDesc = bizDesc{
		Name: name,
	}
	var dataDesc = dataDesc{
		Name: name,
	}
	var out string
	if t == "biz" {
		out = bizDesc.execute()
	} else if t == "data" {
		out = dataDesc.execute()
	} else {
		panic("-t biz or -t data")
	}
	fmt.Println(out)
}
