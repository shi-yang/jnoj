// Code generated by protoc-gen-go-errors. DO NOT EDIT.

package v1

import (
	fmt "fmt"
	errors "github.com/go-kratos/kratos/v2/errors"
)

// This is a compile-time assertion to ensure that this generated file
// is compatible with the kratos package it is being compiled against.
const _ = errors.SupportPackageIsVersion1

func IsUserExist(err error) bool {
	if err == nil {
		return false
	}
	e := errors.FromError(err)
	return e.Reason == UserErrorReason_USER_EXIST.String() && e.Code == 400
}

func ErrorUserExist(format string, args ...interface{}) *errors.Error {
	return errors.New(400, UserErrorReason_USER_EXIST.String(), fmt.Sprintf(format, args...))
}