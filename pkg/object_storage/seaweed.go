package objectstorage

import (
	"bytes"
	"fmt"
	"io"

	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/credentials"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/s3"
)

type Seaweed struct {
}

func NewSeaweed() ObjectStorager {
	return &Seaweed{}
}

func (o *Seaweed) PutObject(cosReq ObjectStorageRequest, objectName string, reader io.Reader) error {
	cres := credentials.NewStaticCredentials(cosReq.GetSecretId(), cosReq.GetSecretKey(), "")
	cfg := aws.NewConfig().
		WithRegion("cn").
		WithEndpoint(cosReq.GetEndpoint()).
		WithCredentials(cres).
		WithS3ForcePathStyle(true)
	sess, err := session.NewSession(cfg)
	if err != nil {
		return err
	}
	svc := s3.New(sess)
	inputObject := &s3.PutObjectInput{
		Bucket: aws.String(cosReq.GetBucket()),
		Key:    aws.String(objectName),
	}
	a, _ := io.ReadAll(reader)
	inputObject.Body = bytes.NewReader(a)
	resp, err := svc.PutObject(inputObject)
	if err != nil {
		fmt.Println(err.Error())
	}
	fmt.Println(resp)
	return err
}

func (o *Seaweed) DeleteObject(cosReq ObjectStorageRequest, objectName string) error {
	cres := credentials.NewStaticCredentials(cosReq.GetSecretId(), cosReq.GetSecretKey(), "")
	cfg := aws.NewConfig().
		WithRegion("cn").
		WithEndpoint(cosReq.GetEndpoint()).
		WithCredentials(cres).
		WithS3ForcePathStyle(true)
	sess, err := session.NewSession(cfg)
	if err != nil {
		return err
	}
	svc := s3.New(sess)
	inputObject := &s3.DeleteObjectInput{
		Bucket: aws.String(cosReq.GetBucket()),
		Key:    aws.String(objectName),
	}
	_, err = svc.DeleteObject(inputObject)
	if err != nil {
		fmt.Println(err.Error())
		return err
	}
	return nil
}

func (o *Seaweed) GetObject(cosReq ObjectStorageRequest, objectName string) (res []byte, err error) {
	cres := credentials.NewStaticCredentials(cosReq.GetSecretId(), cosReq.GetSecretKey(), "")
	cfg := aws.NewConfig().
		WithRegion("cn").
		WithEndpoint(cosReq.GetEndpoint()).
		WithCredentials(cres).
		WithS3ForcePathStyle(true)
	sess, err := session.NewSession(cfg)
	if err != nil {
		return nil, err
	}
	inputObject := &s3.GetObjectInput{
		Bucket: aws.String(cosReq.GetBucket()),
		Key:    aws.String(objectName),
	}
	svc := s3.New(sess)
	out, err := svc.GetObject(inputObject)
	if err != nil {
		fmt.Println(err.Error())
		return nil, err
	}
	res, err = io.ReadAll(out.Body)
	if err != nil {
		fmt.Println(err.Error())
		return nil, err
	}
	return res, nil
}

func (o *Seaweed) ListObjects(cosReq ObjectStorageRequest, objectPath string) (res []*[]byte, err error) {
	cres := credentials.NewStaticCredentials(cosReq.GetSecretId(), cosReq.GetSecretKey(), "")
	cfg := aws.NewConfig().
		WithRegion("cn").
		WithEndpoint(cosReq.GetEndpoint()).
		WithCredentials(cres).
		WithS3ForcePathStyle(true)
	sess, err := session.NewSession(cfg)
	if err != nil {
		return nil, err
	}
	inputObject := &s3.ListObjectsV2Input{
		Bucket: aws.String(cosReq.GetBucket()),
		Prefix: aws.String(objectPath),
	}
	svc := s3.New(sess)
	out, err := svc.ListObjectsV2(inputObject)
	if err != nil {
		fmt.Println(err.Error())
		return nil, err
	}
	fmt.Println(out.String())
	return res, nil
}
