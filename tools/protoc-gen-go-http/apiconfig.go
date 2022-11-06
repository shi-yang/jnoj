package main

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strings"

	"github.com/go-kratos/kratos/cmd/protoc-gen-go-http/v2/apiconfig"
	"google.golang.org/protobuf/compiler/protogen"
	"google.golang.org/protobuf/encoding/protojson"
	"gopkg.in/yaml.v3"
)

func loadAPIConfigFromYAML(yamlFile string) (*apiconfig.Config, error) {
	var yamlContents interface{}
	yamlFileContents, err := os.ReadFile(yamlFile)
	if err != nil {
		return nil, fmt.Errorf("failed to read Configuration description from '%v': %v", yamlFile, err)
	}
	err = yaml.Unmarshal(yamlFileContents, &yamlContents)
	if err != nil {
		return nil, fmt.Errorf("failed to parse API Configuration from YAML in '%v': %v", yamlFile, err)
	}

	jsonContents, err := json.Marshal(yamlContents)
	if err != nil {
		return nil, err
	}

	// Reject unknown fields because APIConfig is only used here
	unmarshaler := protojson.UnmarshalOptions{
		DiscardUnknown: false,
	}

	apiConfiguration := apiconfig.Config{}
	if err := unmarshaler.Unmarshal(jsonContents, &apiConfiguration); err != nil {
		return nil, fmt.Errorf("failed to parse API Configuration from YAML in '%v': %v", yamlFile, err)
	}

	return &apiConfiguration, nil
}

func importAPIConfigPackage(g *protogen.GeneratedFile, config *apiconfig.Config) {
	mp := make(map[string]bool)
	for _, rule := range config.Rules {
		mp[rule.Middleware] = true
	}
	for k := range mp {
		p := strings.Split(k, ".")
		g.P("//", protogen.GoImportPath(p[0]).Ident(""))
	}
}

func buildMiddleware(rule *apiconfig.Rules) *middlewareDesc {
	middleware := strings.Split(rule.Middleware, "/")
	return &middlewareDesc{
		PackageName: middleware[len(middleware)-1],
		Selector:    rule.Selector,
	}
}

// TODO can not get the relative path
func getAPIConfigFilePath(path string, file *protogen.File) string {
	generatedFilenamePath := strings.Split(file.GeneratedFilenamePrefix, "/")
	yamlFilename := generatedFilenamePath[len(generatedFilenamePath)-1] + ".yaml"
	return filepath.Join(path, yamlFilename)
}
