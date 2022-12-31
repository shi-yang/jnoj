package objectstorage

import (
	"log"
	"os"
	"testing"
)

type testReq struct {
	SecretId  string
	SecretKey string
	Bucket    string
	Endpoint  string
}

func (a testReq) GetSecretId() string {
	return a.SecretId
}
func (a testReq) GetSecretKey() string {
	return a.SecretKey
}
func (a testReq) GetBucket() string {
	return a.Bucket
}
func (a testReq) GetEndpoint() string {
	return a.Endpoint
}

func TestSeaweed_PutObject(t *testing.T) {
	s := NewSeaweed()
	req := &testReq{
		Bucket:    "bucketTest",
		SecretKey: "some_secret_key1",
		SecretId:  "some_access_key1",
		Endpoint:  "http://localhost:8333",
	}
	f, err := os.Open("./seaweed.go")
	if err != nil {
		t.Error(err)
	}
	err = s.PutObject(req, "/key/seaweed.go", f)
	if err != nil {
		t.Error(err)
	}
	log.Println(err)
	res, err := s.GetObject(req, "/key/seaweed.go")
	if err != nil {
		t.Error(err)
	}
	log.Println(string(res))
	s.ListObjects(req, "/key")
}

func TestSeaweed_DeleteObject(t *testing.T) {
	s := NewSeaweed()
	req := &testReq{
		Bucket:    "bucketTest",
		SecretKey: "some_secret_key1",
		SecretId:  "some_access_key1",
		Endpoint:  "http://localhost:8333",
	}
	err := s.DeleteObject(req, "aaaaa")
	if err != nil {
		t.Error(err)
	}
}
