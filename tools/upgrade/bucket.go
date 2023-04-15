package main

type Bucket struct {
	Bucket    string
	SecretId  string
	SecretKey string
	Endpoint  string
}

func (b Bucket) GetBucket() string {
	return b.Bucket
}

func (b Bucket) GetSecretId() string {
	return b.SecretId
}

func (b Bucket) GetSecretKey() string {
	return b.SecretKey
}

func (b Bucket) GetEndpoint() string {
	return b.Endpoint
}
