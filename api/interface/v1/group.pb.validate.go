// Code generated by protoc-gen-validate. DO NOT EDIT.
// source: v1/group.proto

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

// Validate checks the field values on Group with the rules defined in the
// proto definition for this message. If any rules are violated, the first
// error encountered is returned, or nil if there are no violations.
func (m *Group) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on Group with the rules defined in the
// proto definition for this message. If any rules are violated, the result is
// a list of violation errors wrapped in GroupMultiError, or nil if none found.
func (m *Group) ValidateAll() error {
	return m.validate(true)
}

func (m *Group) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Id

	// no validation rules for Name

	// no validation rules for Description

	// no validation rules for UserId

	// no validation rules for MemberCount

	if all {
		switch v := interface{}(m.GetCreatedAt()).(type) {
		case interface{ ValidateAll() error }:
			if err := v.ValidateAll(); err != nil {
				errors = append(errors, GroupValidationError{
					field:  "CreatedAt",
					reason: "embedded message failed validation",
					cause:  err,
				})
			}
		case interface{ Validate() error }:
			if err := v.Validate(); err != nil {
				errors = append(errors, GroupValidationError{
					field:  "CreatedAt",
					reason: "embedded message failed validation",
					cause:  err,
				})
			}
		}
	} else if v, ok := interface{}(m.GetCreatedAt()).(interface{ Validate() error }); ok {
		if err := v.Validate(); err != nil {
			return GroupValidationError{
				field:  "CreatedAt",
				reason: "embedded message failed validation",
				cause:  err,
			}
		}
	}

	if len(errors) > 0 {
		return GroupMultiError(errors)
	}

	return nil
}

// GroupMultiError is an error wrapping multiple validation errors returned by
// Group.ValidateAll() if the designated constraints aren't met.
type GroupMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m GroupMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m GroupMultiError) AllErrors() []error { return m }

// GroupValidationError is the validation error returned by Group.Validate if
// the designated constraints aren't met.
type GroupValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e GroupValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e GroupValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e GroupValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e GroupValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e GroupValidationError) ErrorName() string { return "GroupValidationError" }

// Error satisfies the builtin error interface
func (e GroupValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sGroup.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = GroupValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = GroupValidationError{}

// Validate checks the field values on ListGroupsRequest with the rules defined
// in the proto definition for this message. If any rules are violated, the
// first error encountered is returned, or nil if there are no violations.
func (m *ListGroupsRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on ListGroupsRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// ListGroupsRequestMultiError, or nil if none found.
func (m *ListGroupsRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *ListGroupsRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Name

	// no validation rules for Page

	// no validation rules for PerPage

	if m.Mygroup != nil {
		// no validation rules for Mygroup
	}

	if len(errors) > 0 {
		return ListGroupsRequestMultiError(errors)
	}

	return nil
}

// ListGroupsRequestMultiError is an error wrapping multiple validation errors
// returned by ListGroupsRequest.ValidateAll() if the designated constraints
// aren't met.
type ListGroupsRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m ListGroupsRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m ListGroupsRequestMultiError) AllErrors() []error { return m }

// ListGroupsRequestValidationError is the validation error returned by
// ListGroupsRequest.Validate if the designated constraints aren't met.
type ListGroupsRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e ListGroupsRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e ListGroupsRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e ListGroupsRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e ListGroupsRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e ListGroupsRequestValidationError) ErrorName() string {
	return "ListGroupsRequestValidationError"
}

// Error satisfies the builtin error interface
func (e ListGroupsRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sListGroupsRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = ListGroupsRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = ListGroupsRequestValidationError{}

// Validate checks the field values on ListGroupsResponse with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *ListGroupsResponse) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on ListGroupsResponse with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// ListGroupsResponseMultiError, or nil if none found.
func (m *ListGroupsResponse) ValidateAll() error {
	return m.validate(true)
}

func (m *ListGroupsResponse) validate(all bool) error {
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
					errors = append(errors, ListGroupsResponseValidationError{
						field:  fmt.Sprintf("Data[%v]", idx),
						reason: "embedded message failed validation",
						cause:  err,
					})
				}
			case interface{ Validate() error }:
				if err := v.Validate(); err != nil {
					errors = append(errors, ListGroupsResponseValidationError{
						field:  fmt.Sprintf("Data[%v]", idx),
						reason: "embedded message failed validation",
						cause:  err,
					})
				}
			}
		} else if v, ok := interface{}(item).(interface{ Validate() error }); ok {
			if err := v.Validate(); err != nil {
				return ListGroupsResponseValidationError{
					field:  fmt.Sprintf("Data[%v]", idx),
					reason: "embedded message failed validation",
					cause:  err,
				}
			}
		}

	}

	// no validation rules for Total

	if len(errors) > 0 {
		return ListGroupsResponseMultiError(errors)
	}

	return nil
}

// ListGroupsResponseMultiError is an error wrapping multiple validation errors
// returned by ListGroupsResponse.ValidateAll() if the designated constraints
// aren't met.
type ListGroupsResponseMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m ListGroupsResponseMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m ListGroupsResponseMultiError) AllErrors() []error { return m }

// ListGroupsResponseValidationError is the validation error returned by
// ListGroupsResponse.Validate if the designated constraints aren't met.
type ListGroupsResponseValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e ListGroupsResponseValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e ListGroupsResponseValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e ListGroupsResponseValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e ListGroupsResponseValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e ListGroupsResponseValidationError) ErrorName() string {
	return "ListGroupsResponseValidationError"
}

// Error satisfies the builtin error interface
func (e ListGroupsResponseValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sListGroupsResponse.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = ListGroupsResponseValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = ListGroupsResponseValidationError{}

// Validate checks the field values on GetGroupRequest with the rules defined
// in the proto definition for this message. If any rules are violated, the
// first error encountered is returned, or nil if there are no violations.
func (m *GetGroupRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on GetGroupRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// GetGroupRequestMultiError, or nil if none found.
func (m *GetGroupRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *GetGroupRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Id

	if len(errors) > 0 {
		return GetGroupRequestMultiError(errors)
	}

	return nil
}

// GetGroupRequestMultiError is an error wrapping multiple validation errors
// returned by GetGroupRequest.ValidateAll() if the designated constraints
// aren't met.
type GetGroupRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m GetGroupRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m GetGroupRequestMultiError) AllErrors() []error { return m }

// GetGroupRequestValidationError is the validation error returned by
// GetGroupRequest.Validate if the designated constraints aren't met.
type GetGroupRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e GetGroupRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e GetGroupRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e GetGroupRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e GetGroupRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e GetGroupRequestValidationError) ErrorName() string { return "GetGroupRequestValidationError" }

// Error satisfies the builtin error interface
func (e GetGroupRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sGetGroupRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = GetGroupRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = GetGroupRequestValidationError{}

// Validate checks the field values on CreateGroupRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *CreateGroupRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on CreateGroupRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// CreateGroupRequestMultiError, or nil if none found.
func (m *CreateGroupRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *CreateGroupRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Name

	// no validation rules for Description

	if len(errors) > 0 {
		return CreateGroupRequestMultiError(errors)
	}

	return nil
}

// CreateGroupRequestMultiError is an error wrapping multiple validation errors
// returned by CreateGroupRequest.ValidateAll() if the designated constraints
// aren't met.
type CreateGroupRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m CreateGroupRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m CreateGroupRequestMultiError) AllErrors() []error { return m }

// CreateGroupRequestValidationError is the validation error returned by
// CreateGroupRequest.Validate if the designated constraints aren't met.
type CreateGroupRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e CreateGroupRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e CreateGroupRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e CreateGroupRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e CreateGroupRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e CreateGroupRequestValidationError) ErrorName() string {
	return "CreateGroupRequestValidationError"
}

// Error satisfies the builtin error interface
func (e CreateGroupRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sCreateGroupRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = CreateGroupRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = CreateGroupRequestValidationError{}

// Validate checks the field values on UpdateGroupRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *UpdateGroupRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on UpdateGroupRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// UpdateGroupRequestMultiError, or nil if none found.
func (m *UpdateGroupRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *UpdateGroupRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Id

	// no validation rules for Name

	// no validation rules for Description

	if len(errors) > 0 {
		return UpdateGroupRequestMultiError(errors)
	}

	return nil
}

// UpdateGroupRequestMultiError is an error wrapping multiple validation errors
// returned by UpdateGroupRequest.ValidateAll() if the designated constraints
// aren't met.
type UpdateGroupRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m UpdateGroupRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m UpdateGroupRequestMultiError) AllErrors() []error { return m }

// UpdateGroupRequestValidationError is the validation error returned by
// UpdateGroupRequest.Validate if the designated constraints aren't met.
type UpdateGroupRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e UpdateGroupRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e UpdateGroupRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e UpdateGroupRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e UpdateGroupRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e UpdateGroupRequestValidationError) ErrorName() string {
	return "UpdateGroupRequestValidationError"
}

// Error satisfies the builtin error interface
func (e UpdateGroupRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sUpdateGroupRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = UpdateGroupRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = UpdateGroupRequestValidationError{}

// Validate checks the field values on GroupUser with the rules defined in the
// proto definition for this message. If any rules are violated, the first
// error encountered is returned, or nil if there are no violations.
func (m *GroupUser) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on GroupUser with the rules defined in
// the proto definition for this message. If any rules are violated, the
// result is a list of violation errors wrapped in GroupUserMultiError, or nil
// if none found.
func (m *GroupUser) ValidateAll() error {
	return m.validate(true)
}

func (m *GroupUser) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Id

	// no validation rules for Nickname

	// no validation rules for UserId

	// no validation rules for GroupId

	if all {
		switch v := interface{}(m.GetCreatedAt()).(type) {
		case interface{ ValidateAll() error }:
			if err := v.ValidateAll(); err != nil {
				errors = append(errors, GroupUserValidationError{
					field:  "CreatedAt",
					reason: "embedded message failed validation",
					cause:  err,
				})
			}
		case interface{ Validate() error }:
			if err := v.Validate(); err != nil {
				errors = append(errors, GroupUserValidationError{
					field:  "CreatedAt",
					reason: "embedded message failed validation",
					cause:  err,
				})
			}
		}
	} else if v, ok := interface{}(m.GetCreatedAt()).(interface{ Validate() error }); ok {
		if err := v.Validate(); err != nil {
			return GroupUserValidationError{
				field:  "CreatedAt",
				reason: "embedded message failed validation",
				cause:  err,
			}
		}
	}

	if len(errors) > 0 {
		return GroupUserMultiError(errors)
	}

	return nil
}

// GroupUserMultiError is an error wrapping multiple validation errors returned
// by GroupUser.ValidateAll() if the designated constraints aren't met.
type GroupUserMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m GroupUserMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m GroupUserMultiError) AllErrors() []error { return m }

// GroupUserValidationError is the validation error returned by
// GroupUser.Validate if the designated constraints aren't met.
type GroupUserValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e GroupUserValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e GroupUserValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e GroupUserValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e GroupUserValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e GroupUserValidationError) ErrorName() string { return "GroupUserValidationError" }

// Error satisfies the builtin error interface
func (e GroupUserValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sGroupUser.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = GroupUserValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = GroupUserValidationError{}

// Validate checks the field values on ListGroupUsersRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *ListGroupUsersRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on ListGroupUsersRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// ListGroupUsersRequestMultiError, or nil if none found.
func (m *ListGroupUsersRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *ListGroupUsersRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Id

	if len(errors) > 0 {
		return ListGroupUsersRequestMultiError(errors)
	}

	return nil
}

// ListGroupUsersRequestMultiError is an error wrapping multiple validation
// errors returned by ListGroupUsersRequest.ValidateAll() if the designated
// constraints aren't met.
type ListGroupUsersRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m ListGroupUsersRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m ListGroupUsersRequestMultiError) AllErrors() []error { return m }

// ListGroupUsersRequestValidationError is the validation error returned by
// ListGroupUsersRequest.Validate if the designated constraints aren't met.
type ListGroupUsersRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e ListGroupUsersRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e ListGroupUsersRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e ListGroupUsersRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e ListGroupUsersRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e ListGroupUsersRequestValidationError) ErrorName() string {
	return "ListGroupUsersRequestValidationError"
}

// Error satisfies the builtin error interface
func (e ListGroupUsersRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sListGroupUsersRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = ListGroupUsersRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = ListGroupUsersRequestValidationError{}

// Validate checks the field values on ListGroupUsersResponse with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *ListGroupUsersResponse) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on ListGroupUsersResponse with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// ListGroupUsersResponseMultiError, or nil if none found.
func (m *ListGroupUsersResponse) ValidateAll() error {
	return m.validate(true)
}

func (m *ListGroupUsersResponse) validate(all bool) error {
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
					errors = append(errors, ListGroupUsersResponseValidationError{
						field:  fmt.Sprintf("Data[%v]", idx),
						reason: "embedded message failed validation",
						cause:  err,
					})
				}
			case interface{ Validate() error }:
				if err := v.Validate(); err != nil {
					errors = append(errors, ListGroupUsersResponseValidationError{
						field:  fmt.Sprintf("Data[%v]", idx),
						reason: "embedded message failed validation",
						cause:  err,
					})
				}
			}
		} else if v, ok := interface{}(item).(interface{ Validate() error }); ok {
			if err := v.Validate(); err != nil {
				return ListGroupUsersResponseValidationError{
					field:  fmt.Sprintf("Data[%v]", idx),
					reason: "embedded message failed validation",
					cause:  err,
				}
			}
		}

	}

	// no validation rules for Total

	if len(errors) > 0 {
		return ListGroupUsersResponseMultiError(errors)
	}

	return nil
}

// ListGroupUsersResponseMultiError is an error wrapping multiple validation
// errors returned by ListGroupUsersResponse.ValidateAll() if the designated
// constraints aren't met.
type ListGroupUsersResponseMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m ListGroupUsersResponseMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m ListGroupUsersResponseMultiError) AllErrors() []error { return m }

// ListGroupUsersResponseValidationError is the validation error returned by
// ListGroupUsersResponse.Validate if the designated constraints aren't met.
type ListGroupUsersResponseValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e ListGroupUsersResponseValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e ListGroupUsersResponseValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e ListGroupUsersResponseValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e ListGroupUsersResponseValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e ListGroupUsersResponseValidationError) ErrorName() string {
	return "ListGroupUsersResponseValidationError"
}

// Error satisfies the builtin error interface
func (e ListGroupUsersResponseValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sListGroupUsersResponse.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = ListGroupUsersResponseValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = ListGroupUsersResponseValidationError{}

// Validate checks the field values on DeleteGroupUserRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *DeleteGroupUserRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on DeleteGroupUserRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// DeleteGroupUserRequestMultiError, or nil if none found.
func (m *DeleteGroupUserRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *DeleteGroupUserRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Gid

	// no validation rules for Uid

	if len(errors) > 0 {
		return DeleteGroupUserRequestMultiError(errors)
	}

	return nil
}

// DeleteGroupUserRequestMultiError is an error wrapping multiple validation
// errors returned by DeleteGroupUserRequest.ValidateAll() if the designated
// constraints aren't met.
type DeleteGroupUserRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m DeleteGroupUserRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m DeleteGroupUserRequestMultiError) AllErrors() []error { return m }

// DeleteGroupUserRequestValidationError is the validation error returned by
// DeleteGroupUserRequest.Validate if the designated constraints aren't met.
type DeleteGroupUserRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e DeleteGroupUserRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e DeleteGroupUserRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e DeleteGroupUserRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e DeleteGroupUserRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e DeleteGroupUserRequestValidationError) ErrorName() string {
	return "DeleteGroupUserRequestValidationError"
}

// Error satisfies the builtin error interface
func (e DeleteGroupUserRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sDeleteGroupUserRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = DeleteGroupUserRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = DeleteGroupUserRequestValidationError{}

// Validate checks the field values on CreateGroupUserRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the first error encountered is returned, or nil if there are no violations.
func (m *CreateGroupUserRequest) Validate() error {
	return m.validate(false)
}

// ValidateAll checks the field values on CreateGroupUserRequest with the rules
// defined in the proto definition for this message. If any rules are
// violated, the result is a list of violation errors wrapped in
// CreateGroupUserRequestMultiError, or nil if none found.
func (m *CreateGroupUserRequest) ValidateAll() error {
	return m.validate(true)
}

func (m *CreateGroupUserRequest) validate(all bool) error {
	if m == nil {
		return nil
	}

	var errors []error

	// no validation rules for Gid

	// no validation rules for Uid

	if len(errors) > 0 {
		return CreateGroupUserRequestMultiError(errors)
	}

	return nil
}

// CreateGroupUserRequestMultiError is an error wrapping multiple validation
// errors returned by CreateGroupUserRequest.ValidateAll() if the designated
// constraints aren't met.
type CreateGroupUserRequestMultiError []error

// Error returns a concatenation of all the error messages it wraps.
func (m CreateGroupUserRequestMultiError) Error() string {
	var msgs []string
	for _, err := range m {
		msgs = append(msgs, err.Error())
	}
	return strings.Join(msgs, "; ")
}

// AllErrors returns a list of validation violation errors.
func (m CreateGroupUserRequestMultiError) AllErrors() []error { return m }

// CreateGroupUserRequestValidationError is the validation error returned by
// CreateGroupUserRequest.Validate if the designated constraints aren't met.
type CreateGroupUserRequestValidationError struct {
	field  string
	reason string
	cause  error
	key    bool
}

// Field function returns field value.
func (e CreateGroupUserRequestValidationError) Field() string { return e.field }

// Reason function returns reason value.
func (e CreateGroupUserRequestValidationError) Reason() string { return e.reason }

// Cause function returns cause value.
func (e CreateGroupUserRequestValidationError) Cause() error { return e.cause }

// Key function returns key value.
func (e CreateGroupUserRequestValidationError) Key() bool { return e.key }

// ErrorName returns error name.
func (e CreateGroupUserRequestValidationError) ErrorName() string {
	return "CreateGroupUserRequestValidationError"
}

// Error satisfies the builtin error interface
func (e CreateGroupUserRequestValidationError) Error() string {
	cause := ""
	if e.cause != nil {
		cause = fmt.Sprintf(" | caused by: %v", e.cause)
	}

	key := ""
	if e.key {
		key = "key for "
	}

	return fmt.Sprintf(
		"invalid %sCreateGroupUserRequest.%s: %s%s",
		key,
		e.field,
		e.reason,
		cause)
}

var _ error = CreateGroupUserRequestValidationError{}

var _ interface {
	Field() string
	Reason() string
	Key() bool
	Cause() error
	ErrorName() string
} = CreateGroupUserRequestValidationError{}
