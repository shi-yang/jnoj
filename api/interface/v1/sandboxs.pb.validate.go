// Code generated by protoc-gen-validate. DO NOT EDIT.
// source: v1/sandboxs.proto

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

// Validate checks the field values on RunRequest with the rules defined in the
// proto definition for this message. If any rules are violated, the first
// error encountered is returned, or nil if there are no violations.
func (m *RunRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on RunRequest with the rules defined in
// the proto definition for this message. If any rules are violated, the
// result is a list of violation errors wrapped in RunRequestMultiError, or
// nil if none found.
func (m *RunRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *RunRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Source

	// no validation rules for Stdin

	// no validation rules for Language

	if val := m.GetMemoryLimit(); val < 4 || val > 1024 {
		err := RunRequestValidationError{
			field:  "MemoryLimit",
			reason: "value must be inside range [4, 1024]",
		}
		if !all {
			return err
		}
		errors = append(errors, err)
	}

	if val := m.GetTimeLimit(); val < 250 || val > 15000 {
		err := RunRequestValidationError{
			field:  "TimeLimit",
			reason: "value must be inside range [250, 15000]",
		}
		if !all {
			return err
		}
		errors = append(errors, err)
	}

	if m.LanguageId != nil {
		// no validation rules for LanguageId
	}

	if len(errors) > 0 {
		return RunRequestMultiError(errors)
	}

	return nil
}

// RunRequestMultiError is an error wrapping multiple validation errors
// returned by RunRequest.ValidateAll() if the designated constraints aren't met.
type RunRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m RunRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m RunRequestMultiError) AllErrors() []error { return m }

// RunRequestValidationError is the validation error returned by
// RunRequest.Validate if the designated constraints aren't met.
type RunRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e RunRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e RunRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e RunRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e RunRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e RunRequestValidationError) ErrorName() string { return "RunRequestValidationError" }

// Error satisfies the builtin error interface
func (e RunRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sRunRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = RunRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = RunRequestValidationError{}

// Validate checks the field values on RunResponse with the rules defined in
// the proto definition for this message. If any rules are violated, the first
// error encountered is returned, or nil if there are no violations.
func (m *RunResponse) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on RunResponse with the rules defined in
// the proto definition for this message. If any rules are violated, the
// result is a list of violation errors wrapped in RunResponseMultiError, or
// nil if none found.
func (m *RunResponse) ValidateAll() error {
	return m.validate(true)
}

func (m *RunResponse) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Stdout

	// no validation rules for Stderr

	// no validation rules for Time

	// no validation rules for Memory

	// no validation rules for ExitCode

	// no validation rules for CompileMsg

	// no validation rules for ErrMsg

	if len(errors) > 0 {
		return RunResponseMultiError(errors)
	}

	return nil
}

// RunResponseMultiError is an error wrapping multiple validation errors
// returned by RunResponse.ValidateAll() if the designated constraints aren't met.
type RunResponseMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m RunResponseMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m RunResponseMultiError) AllErrors() []error { return m }

// RunResponseValidationError is the validation error returned by
// RunResponse.Validate if the designated constraints aren't met.
type RunResponseValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e RunResponseValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e RunResponseValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e RunResponseValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e RunResponseValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e RunResponseValidationError) ErrorName() string { return "RunResponseValidationError" }

// Error satisfies the builtin error interface
func (e RunResponseValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sRunResponse.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = RunResponseValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = RunResponseValidationError{}
