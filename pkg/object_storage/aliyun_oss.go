package objectstorage

import (
	"io"

	"github.com/aliyun/aliyun-oss-go-sdk/oss"
)

type AliyunOSS struct{}

func NewAliyunOSS() ObjectStorager {
	return &AliyunOSS{}
}

func (o *AliyunOSS) PutObject(cosReq ObjectStorageRequest, objectName string, reader io.Reader) error {
	client, err := oss.New(cosReq.GetEndpoint(), cosReq.GetSecretId(), cosReq.GetSecretKey())
	if err != nil {
		return err
	}
	// 获取存储空间。
	bucket, err := client.Bucket(cosReq.GetBucket())
	if err != nil {
		return nil
	}
	// 上传文件。
	return bucket.PutObject(objectName, reader)
}

func (o *AliyunOSS) DeleteObject(cosReq ObjectStorageRequest, objectName string) error {
	client, err := oss.New(cosReq.GetEndpoint(), cosReq.GetSecretId(), cosReq.GetSecretKey())
	if err != nil {
		return err
	}
	bucket, err := client.Bucket(cosReq.GetBucket())
	if err != nil {
		return nil
	}
	return bucket.DeleteObject(objectName)
}

func (o *AliyunOSS) GetObject(cosReq ObjectStorageRequest, objectName string) ([]byte, error) {
	client, err := oss.New(cosReq.GetEndpoint(), cosReq.GetSecretId(), cosReq.GetSecretKey())
	if err != nil {
		return nil, err
	}
	bucket, err := client.Bucket(cosReq.GetBucket())
	if err != nil {
		return nil, err
	}
	res, err := bucket.GetObject(objectName)
	if err != nil {
		return nil, err
	}
	return io.ReadAll(res)
}

func (o *AliyunOSS) ListObjects(cosReq ObjectStorageRequest, objectPath string) (res []*[]byte, err error) {
	return nil, nil
}
