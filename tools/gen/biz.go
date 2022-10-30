package main

import (
	"bytes"
	_ "embed"
	"strings"
	"text/template"
)

//go:embed biz_template.go.tpl
var bizTemplate string

type bizDesc struct {
	Name string
}

func (s *bizDesc) execute() string {
	buf := new(bytes.Buffer)
	tmpl, err := template.New("biz").Parse(bizTemplate)
	if err != nil {
		panic(err)
	}
	if err := tmpl.Execute(buf, s); err != nil {
		panic(err)
	}
	return strings.Trim(buf.String(), "\r\n")
}
