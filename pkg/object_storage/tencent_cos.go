package objectstorage

import (
	"context"
	"io"
	"net/http"
	"net/url"
	"time"

	cos2 "github.com/tencentyun/cos-go-sdk-v5"
)

const (
	STANDARD    = "STANDARD"    // 标准存储
	STANDARD_IA = "STANDARD_IA" // 低频存储
	ARCHIVE     = "ARCHIVE"     // 归档存储
)

type TencentCOS struct{}

func NewCosClient() ObjectStorager {
	return &TencentCOS{}
}

// PutObject 上传文件到腾讯云
func (o *TencentCOS) PutObject(cosReq ObjectStorageRequest, objectName string, reader io.Reader) error {
	u, _ := url.Parse(cosReq.GetBucket())
	b := &cos2.BaseURL{BucketURL: u}
	c := cos2.NewClient(b, &http.Client{
		Transport: &cos2.AuthorizationTransport{
			SecretID:  cosReq.GetSecretId(),
			SecretKey: cosReq.GetSecretKey(),
		},
	})
	opt := &cos2.ObjectPutOptions{
		ObjectPutHeaderOptions: &cos2.ObjectPutHeaderOptions{
			XCosStorageClass: STANDARD,
		},
	}
	_, err := c.Object.Put(context.Background(), objectName, reader, opt)
	if err != nil {
		return err
	}
	return nil
}

// DeleteObject 腾讯桶文件删除
func (o *TencentCOS) DeleteObject(cosReq ObjectStorageRequest, objectName string) error {
	u, _ := url.Parse(cosReq.GetBucket())
	b := &cos2.BaseURL{BucketURL: u}
	c := cos2.NewClient(b, &http.Client{
		Transport: &cos2.AuthorizationTransport{
			SecretID:  cosReq.GetSecretId(),
			SecretKey: cosReq.GetSecretKey(),
		},
	})
	_, err := c.Object.Delete(context.Background(), objectName, nil)
	return err
}

// GetObject 腾讯桶文件下载
func (o *TencentCOS) GetObject(cosReq ObjectStorageRequest, objectName string) (res []byte, err error) {
	u, _ := url.Parse(cosReq.GetBucket())
	b := &cos2.BaseURL{BucketURL: u}
	c := cos2.NewClient(b, &http.Client{
		Transport: &cos2.AuthorizationTransport{
			SecretID:  cosReq.GetSecretId(),
			SecretKey: cosReq.GetSecretKey(),
		},
	})
	resp, err := c.Object.Get(context.Background(), objectName, nil)
	if err != nil {
		return []byte(""), err
	}
	res, _ = io.ReadAll(resp.Body)
	_ = resp.Body.Close()
	return res, nil
}

// ListObjects 腾讯桶文件列表
func (o *TencentCOS) ListObjects(cosReq ObjectStorageRequest, objectName string) (res []*[]byte, err error) {
	return res, nil
}

// GetPresignedURL 请求预签名 URL 接口。用于获取预览的文件URL路径
func (o *TencentCOS) GetPresignedURL(cosReq ObjectStorageRequest, objectName string, expired time.Duration) (*url.URL, error) {
	u, _ := url.Parse(cosReq.GetBucket())
	b := &cos2.BaseURL{BucketURL: u}
	c := cos2.NewClient(b, &http.Client{
		Transport: &cos2.AuthorizationTransport{
			SecretID:  cosReq.GetSecretId(),
			SecretKey: cosReq.GetSecretKey(),
		},
	})
	return c.Object.GetPresignedURL(context.Background(), http.MethodGet, objectName, cosReq.GetSecretId(), cosReq.GetSecretKey(), expired, nil)
}
