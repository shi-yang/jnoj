package main

import (
	"bytes"
	_ "embed"
	"strings"
	"text/template"
)

//go:embed data_template.go.tpl
var dataTemplate string

type dataDesc struct {
	Name string
}

func (s *dataDesc) execute() string {
	buf := new(bytes.Buffer)
	funcMap := template.FuncMap{
		"tolower": func(s string) string {
			if len(s) == 0 {
				return ""
			}
			return strings.ToLower(string(s[0])) + s[1:]
		},
	}
	tmpl, err := template.New("data").
		Funcs(funcMap).
		Parse(dataTemplate)
	if err != nil {
		panic(err)
	}
	if err := tmpl.Execute(buf, s); err != nil {
		panic(err)
	}
	return strings.Trim(buf.String(), "\r\n")
}
