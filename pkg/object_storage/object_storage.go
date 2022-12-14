package objectstorage

import "io"

type ObjectStorageRequest interface {
	GetSecretId() string
	GetSecretKey() string
	GetBucket() string
	GetEndpoint() string
}

type ObjectStorager interface {
	PutObject(cosReq ObjectStorageRequest, objectName string, reader io.Reader) error
	DeleteObject(cosReq ObjectStorageRequest, objectName string) error
	GetObject(cosReq ObjectStorageRequest, objectName string) (res []byte, err error)
	ListObjects(cosReq ObjectStorageRequest, objectPath string) (res []*[]byte, err error)
}
