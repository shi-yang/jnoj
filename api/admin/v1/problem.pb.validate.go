// Code generated by protoc-gen-validate. DO NOT EDIT.
// source: v1/problem.proto

package v1

import (
	"bytes"
	"errors"
	"fmt"
	"net"
	"net/mail"
	"net/url"
	"regexp"
	"sort"
	"strings"
	"time"
	"unicode/utf8"

	"google.golang.org/protobuf/types/known/anypb"
)

// ensure the imports are used
var (
	_ = bytes.MinRead
	_ = errors.New("")
	_ = fmt.Print
	_ = utf8.UTFMax
	_ = (*regexp.Regexp)(nil)
	_ = (*strings.Reader)(nil)
	_ = net.IPv4len
	_ = time.Duration(0)
	_ = (*url.URL)(nil)
	_ = (*mail.Address)(nil)
	_ = anypb.Any{}
	_ = sort.Sort
)

// Validate checks the field values on Problem with the rules defined in the
// proto definition for this message. If any rules are violated, the first
// error encountered is returned, or nil if there are no violations.
func (m *Problem) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on Problem with the rules defined in the
// proto definition for this message. If any rules are violated, the result is
// a list of violation errors wrapped in ProblemMultiError, or nil if none found.
func (m *Problem) ValidateAll() error {
	return m.validate(true)
}

func (m *Problem) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	if len(errors) > 0 {
		return ProblemMultiError(errors)
	}

	return nil
}

// ProblemMultiError is an error wrapping multiple validation errors returned
// by Problem.ValidateAll() if the designated constraints aren't met.
type ProblemMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m ProblemMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m ProblemMultiError) AllErrors() []error { return m }

// ProblemValidationError is the validation error returned by Problem.Validate
// if the designated constraints aren't met.
type ProblemValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e ProblemValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e ProblemValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e ProblemValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e ProblemValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e ProblemValidationError) ErrorName() string { return "ProblemValidationError" }

// Error satisfies the builtin error interface
func (e ProblemValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sProblem.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = ProblemValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = ProblemValidationError{}

// Validate checks the field values on ListProblemsRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *ListProblemsRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on ListProblemsRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// ListProblemsRequestMultiError, or nil if none found.
func (m *ListProblemsRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *ListProblemsRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	for idx, item := range m.GetData() {
		_, _ = idx, item

		if all {
			switch v := interface{}(item).(type) {
			case interface{ ValidateAll() error }:
				if err := v.ValidateAll(); err != nil {
					errors = append(errors, ListProblemsRequestValidationError{
						field:  fmt.Sprintf("Data[%v]", idx),
						reason: "embedded message failed validation",
						cause:  err,
					})
				}
			case interface{ Validate() error }:
				if err := v.Validate(); err != nil {
					errors = append(errors, ListProblemsRequestValidationError{
						field:  fmt.Sprintf("Data[%v]", idx),
						reason: "embedded message failed validation",
						cause:  err,
					})
				}
			}
		} else if v, ok := interface{}(item).(interface{ Validate() error }); ok {
			if err := v.Validate(); err != nil {
				return ListProblemsRequestValidationError{
					field:  fmt.Sprintf("Data[%v]", idx),
					reason: "embedded message failed validation",
					cause:  err,
				}
			}
		}

	}

	// no validation rules for Total

	if len(errors) > 0 {
		return ListProblemsRequestMultiError(errors)
	}

	return nil
}

// ListProblemsRequestMultiError is an error wrapping multiple validation
// errors returned by ListProblemsRequest.ValidateAll() if the designated
// constraints aren't met.
type ListProblemsRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m ListProblemsRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m ListProblemsRequestMultiError) AllErrors() []error { return m }

// ListProblemsRequestValidationError is the validation error returned by
// ListProblemsRequest.Validate if the designated constraints aren't met.
type ListProblemsRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e ListProblemsRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e ListProblemsRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e ListProblemsRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e ListProblemsRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e ListProblemsRequestValidationError) ErrorName() string {
	return "ListProblemsRequestValidationError"
}

// Error satisfies the builtin error interface
func (e ListProblemsRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sListProblemsRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = ListProblemsRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = ListProblemsRequestValidationError{}

// Validate checks the field values on ListProblemsResponse with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *ListProblemsResponse) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on ListProblemsResponse with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// ListProblemsResponseMultiError, or nil if none found.
func (m *ListProblemsResponse) ValidateAll() error {
	return m.validate(true)
}

func (m *ListProblemsResponse) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Name

	// no validation rules for Page

	// no validation rules for PerPage

	if len(errors) > 0 {
		return ListProblemsResponseMultiError(errors)
	}

	return nil
}

// ListProblemsResponseMultiError is an error wrapping multiple validation
// errors returned by ListProblemsResponse.ValidateAll() if the designated
// constraints aren't met.
type ListProblemsResponseMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m ListProblemsResponseMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m ListProblemsResponseMultiError) AllErrors() []error { return m }

// ListProblemsResponseValidationError is the validation error returned by
// ListProblemsResponse.Validate if the designated constraints aren't met.
type ListProblemsResponseValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e ListProblemsResponseValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e ListProblemsResponseValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e ListProblemsResponseValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e ListProblemsResponseValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e ListProblemsResponseValidationError) ErrorName() string {
	return "ListProblemsResponseValidationError"
}

// Error satisfies the builtin error interface
func (e ListProblemsResponseValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sListProblemsResponse.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = ListProblemsResponseValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = ListProblemsResponseValidationError{}