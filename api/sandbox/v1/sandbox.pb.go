// Code generated by protoc-gen-go. DO NOT EDIT.
// versions:
// 	protoc-gen-go v1.28.1
// 	protoc        v3.19.4
// source: v1/sandbox.proto

package v1

import (
	protoreflect "google.golang.org/protobuf/reflect/protoreflect"
	protoimpl "google.golang.org/protobuf/runtime/protoimpl"
	reflect "reflect"
	sync "sync"
)

const (
	// Verify that this generated code is sufficiently up-to-date.
	_ = protoimpl.EnforceVersion(20 - protoimpl.MinVersion)
	// Verify that runtime/protoimpl is sufficiently up-to-date.
	_ = protoimpl.EnforceVersion(protoimpl.MaxVersion - 20)
)

type RunRequest struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Source          string  `protobuf:"bytes,1,opt,name=source,proto3" json:"source,omitempty"`
	Stdin           string  `protobuf:"bytes,2,opt,name=stdin,proto3" json:"stdin,omitempty"`
	Language        int32   `protobuf:"varint,3,opt,name=language,proto3" json:"language,omitempty"`
	MemoryLimit     int64   `protobuf:"varint,4,opt,name=memory_limit,json=memoryLimit,proto3" json:"memory_limit,omitempty"`
	TimeLimit       int64   `protobuf:"varint,5,opt,name=time_limit,json=timeLimit,proto3" json:"time_limit,omitempty"`
	Answer          *string `protobuf:"bytes,6,opt,name=answer,proto3,oneof" json:"answer,omitempty"`
	CheckerSource   *string `protobuf:"bytes,7,opt,name=checker_source,json=checkerSource,proto3,oneof" json:"checker_source,omitempty"`
	CheckerLanguage *string `protobuf:"bytes,8,opt,name=checker_language,json=checkerLanguage,proto3,oneof" json:"checker_language,omitempty"`
}

func (x *RunRequest) Reset() {
	*x = RunRequest{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandbox_proto_msgTypes[0]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunRequest) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunRequest) ProtoMessage() {}

func (x *RunRequest) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandbox_proto_msgTypes[0]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use RunRequest.ProtoReflect.Descriptor instead.
func (*RunRequest) Descriptor() ([]byte, []int) {
	return file_v1_sandbox_proto_rawDescGZIP(), []int{0}
}

func (x *RunRequest) GetSource() string {
	if x != nil {
		return x.Source
	}
	return ""
}

func (x *RunRequest) GetStdin() string {
	if x != nil {
		return x.Stdin
	}
	return ""
}

func (x *RunRequest) GetLanguage() int32 {
	if x != nil {
		return x.Language
	}
	return 0
}

func (x *RunRequest) GetMemoryLimit() int64 {
	if x != nil {
		return x.MemoryLimit
	}
	return 0
}

func (x *RunRequest) GetTimeLimit() int64 {
	if x != nil {
		return x.TimeLimit
	}
	return 0
}

func (x *RunRequest) GetAnswer() string {
	if x != nil && x.Answer != nil {
		return *x.Answer
	}
	return ""
}

func (x *RunRequest) GetCheckerSource() string {
	if x != nil && x.CheckerSource != nil {
		return *x.CheckerSource
	}
	return ""
}

func (x *RunRequest) GetCheckerLanguage() string {
	if x != nil && x.CheckerLanguage != nil {
		return *x.CheckerLanguage
	}
	return ""
}

type RunResponse struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Stdout          string `protobuf:"bytes,1,opt,name=stdout,proto3" json:"stdout,omitempty"`
	Stderr          string `protobuf:"bytes,2,opt,name=stderr,proto3" json:"stderr,omitempty"`
	Time            int64  `protobuf:"varint,3,opt,name=time,proto3" json:"time,omitempty"`
	Memory          int64  `protobuf:"varint,4,opt,name=memory,proto3" json:"memory,omitempty"`
	ExitCode        int32  `protobuf:"varint,5,opt,name=exit_code,json=exitCode,proto3" json:"exit_code,omitempty"`
	CompileMsg      string `protobuf:"bytes,6,opt,name=compile_msg,json=compileMsg,proto3" json:"compile_msg,omitempty"`
	ErrMsg          string `protobuf:"bytes,7,opt,name=err_msg,json=errMsg,proto3" json:"err_msg,omitempty"`
	CheckerStdout   string `protobuf:"bytes,8,opt,name=checker_stdout,json=checkerStdout,proto3" json:"checker_stdout,omitempty"`
	CheckerExitCode int32  `protobuf:"varint,9,opt,name=checker_exit_code,json=checkerExitCode,proto3" json:"checker_exit_code,omitempty"`
}

func (x *RunResponse) Reset() {
	*x = RunResponse{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandbox_proto_msgTypes[1]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunResponse) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunResponse) ProtoMessage() {}

func (x *RunResponse) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandbox_proto_msgTypes[1]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use RunResponse.ProtoReflect.Descriptor instead.
func (*RunResponse) Descriptor() ([]byte, []int) {
	return file_v1_sandbox_proto_rawDescGZIP(), []int{1}
}

func (x *RunResponse) GetStdout() string {
	if x != nil {
		return x.Stdout
	}
	return ""
}

func (x *RunResponse) GetStderr() string {
	if x != nil {
		return x.Stderr
	}
	return ""
}

func (x *RunResponse) GetTime() int64 {
	if x != nil {
		return x.Time
	}
	return 0
}

func (x *RunResponse) GetMemory() int64 {
	if x != nil {
		return x.Memory
	}
	return 0
}

func (x *RunResponse) GetExitCode() int32 {
	if x != nil {
		return x.ExitCode
	}
	return 0
}

func (x *RunResponse) GetCompileMsg() string {
	if x != nil {
		return x.CompileMsg
	}
	return ""
}

func (x *RunResponse) GetErrMsg() string {
	if x != nil {
		return x.ErrMsg
	}
	return ""
}

func (x *RunResponse) GetCheckerStdout() string {
	if x != nil {
		return x.CheckerStdout
	}
	return ""
}

func (x *RunResponse) GetCheckerExitCode() int32 {
	if x != nil {
		return x.CheckerExitCode
	}
	return 0
}

type RunSubmissionRequest struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	SubmissionId int64 `protobuf:"varint,1,opt,name=submission_id,json=submissionId,proto3" json:"submission_id,omitempty"`
}

func (x *RunSubmissionRequest) Reset() {
	*x = RunSubmissionRequest{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandbox_proto_msgTypes[2]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunSubmissionRequest) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunSubmissionRequest) ProtoMessage() {}

func (x *RunSubmissionRequest) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandbox_proto_msgTypes[2]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use RunSubmissionRequest.ProtoReflect.Descriptor instead.
func (*RunSubmissionRequest) Descriptor() ([]byte, []int) {
	return file_v1_sandbox_proto_rawDescGZIP(), []int{2}
}

func (x *RunSubmissionRequest) GetSubmissionId() int64 {
	if x != nil {
		return x.SubmissionId
	}
	return 0
}

type RunSubmissionResponse struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields
}

func (x *RunSubmissionResponse) Reset() {
	*x = RunSubmissionResponse{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandbox_proto_msgTypes[3]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunSubmissionResponse) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunSubmissionResponse) ProtoMessage() {}

func (x *RunSubmissionResponse) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandbox_proto_msgTypes[3]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use RunSubmissionResponse.ProtoReflect.Descriptor instead.
func (*RunSubmissionResponse) Descriptor() ([]byte, []int) {
	return file_v1_sandbox_proto_rawDescGZIP(), []int{3}
}

type RunProblemFileRequest struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	ProblemId     int32 `protobuf:"varint,1,opt,name=problem_id,json=problemId,proto3" json:"problem_id,omitempty"`
	ProblemFileId int32 `protobuf:"varint,2,opt,name=problem_file_id,json=problemFileId,proto3" json:"problem_file_id,omitempty"`
}

func (x *RunProblemFileRequest) Reset() {
	*x = RunProblemFileRequest{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandbox_proto_msgTypes[4]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunProblemFileRequest) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunProblemFileRequest) ProtoMessage() {}

func (x *RunProblemFileRequest) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandbox_proto_msgTypes[4]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use RunProblemFileRequest.ProtoReflect.Descriptor instead.
func (*RunProblemFileRequest) Descriptor() ([]byte, []int) {
	return file_v1_sandbox_proto_rawDescGZIP(), []int{4}
}

func (x *RunProblemFileRequest) GetProblemId() int32 {
	if x != nil {
		return x.ProblemId
	}
	return 0
}

func (x *RunProblemFileRequest) GetProblemFileId() int32 {
	if x != nil {
		return x.ProblemFileId
	}
	return 0
}

type RunProblemFileResponse struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields
}

func (x *RunProblemFileResponse) Reset() {
	*x = RunProblemFileResponse{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandbox_proto_msgTypes[5]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunProblemFileResponse) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunProblemFileResponse) ProtoMessage() {}

func (x *RunProblemFileResponse) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandbox_proto_msgTypes[5]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use RunProblemFileResponse.ProtoReflect.Descriptor instead.
func (*RunProblemFileResponse) Descriptor() ([]byte, []int) {
	return file_v1_sandbox_proto_rawDescGZIP(), []int{5}
}

var File_v1_sandbox_proto protoreflect.FileDescriptor

var file_v1_sandbox_proto_rawDesc = []byte{
	0x0a, 0x10, 0x76, 0x31, 0x2f, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x2e, 0x70, 0x72, 0x6f,
	0x74, 0x6f, 0x12, 0x0f, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78,
	0x2e, 0x76, 0x31, 0x22, 0xc4, 0x02, 0x0a, 0x0a, 0x52, 0x75, 0x6e, 0x52, 0x65, 0x71, 0x75, 0x65,
	0x73, 0x74, 0x12, 0x16, 0x0a, 0x06, 0x73, 0x6f, 0x75, 0x72, 0x63, 0x65, 0x18, 0x01, 0x20, 0x01,
	0x28, 0x09, 0x52, 0x06, 0x73, 0x6f, 0x75, 0x72, 0x63, 0x65, 0x12, 0x14, 0x0a, 0x05, 0x73, 0x74,
	0x64, 0x69, 0x6e, 0x18, 0x02, 0x20, 0x01, 0x28, 0x09, 0x52, 0x05, 0x73, 0x74, 0x64, 0x69, 0x6e,
	0x12, 0x1a, 0x0a, 0x08, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65, 0x18, 0x03, 0x20, 0x01,
	0x28, 0x05, 0x52, 0x08, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65, 0x12, 0x21, 0x0a, 0x0c,
	0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x5f, 0x6c, 0x69, 0x6d, 0x69, 0x74, 0x18, 0x04, 0x20, 0x01,
	0x28, 0x03, 0x52, 0x0b, 0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x4c, 0x69, 0x6d, 0x69, 0x74, 0x12,
	0x1d, 0x0a, 0x0a, 0x74, 0x69, 0x6d, 0x65, 0x5f, 0x6c, 0x69, 0x6d, 0x69, 0x74, 0x18, 0x05, 0x20,
	0x01, 0x28, 0x03, 0x52, 0x09, 0x74, 0x69, 0x6d, 0x65, 0x4c, 0x69, 0x6d, 0x69, 0x74, 0x12, 0x1b,
	0x0a, 0x06, 0x61, 0x6e, 0x73, 0x77, 0x65, 0x72, 0x18, 0x06, 0x20, 0x01, 0x28, 0x09, 0x48, 0x00,
	0x52, 0x06, 0x61, 0x6e, 0x73, 0x77, 0x65, 0x72, 0x88, 0x01, 0x01, 0x12, 0x2a, 0x0a, 0x0e, 0x63,
	0x68, 0x65, 0x63, 0x6b, 0x65, 0x72, 0x5f, 0x73, 0x6f, 0x75, 0x72, 0x63, 0x65, 0x18, 0x07, 0x20,
	0x01, 0x28, 0x09, 0x48, 0x01, 0x52, 0x0d, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x65, 0x72, 0x53, 0x6f,
	0x75, 0x72, 0x63, 0x65, 0x88, 0x01, 0x01, 0x12, 0x2e, 0x0a, 0x10, 0x63, 0x68, 0x65, 0x63, 0x6b,
	0x65, 0x72, 0x5f, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65, 0x18, 0x08, 0x20, 0x01, 0x28,
	0x09, 0x48, 0x02, 0x52, 0x0f, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x65, 0x72, 0x4c, 0x61, 0x6e, 0x67,
	0x75, 0x61, 0x67, 0x65, 0x88, 0x01, 0x01, 0x42, 0x09, 0x0a, 0x07, 0x5f, 0x61, 0x6e, 0x73, 0x77,
	0x65, 0x72, 0x42, 0x11, 0x0a, 0x0f, 0x5f, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x65, 0x72, 0x5f, 0x73,
	0x6f, 0x75, 0x72, 0x63, 0x65, 0x42, 0x13, 0x0a, 0x11, 0x5f, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x65,
	0x72, 0x5f, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65, 0x22, 0x93, 0x02, 0x0a, 0x0b, 0x52,
	0x75, 0x6e, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x12, 0x16, 0x0a, 0x06, 0x73, 0x74,
	0x64, 0x6f, 0x75, 0x74, 0x18, 0x01, 0x20, 0x01, 0x28, 0x09, 0x52, 0x06, 0x73, 0x74, 0x64, 0x6f,
	0x75, 0x74, 0x12, 0x16, 0x0a, 0x06, 0x73, 0x74, 0x64, 0x65, 0x72, 0x72, 0x18, 0x02, 0x20, 0x01,
	0x28, 0x09, 0x52, 0x06, 0x73, 0x74, 0x64, 0x65, 0x72, 0x72, 0x12, 0x12, 0x0a, 0x04, 0x74, 0x69,
	0x6d, 0x65, 0x18, 0x03, 0x20, 0x01, 0x28, 0x03, 0x52, 0x04, 0x74, 0x69, 0x6d, 0x65, 0x12, 0x16,
	0x0a, 0x06, 0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x18, 0x04, 0x20, 0x01, 0x28, 0x03, 0x52, 0x06,
	0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x12, 0x1b, 0x0a, 0x09, 0x65, 0x78, 0x69, 0x74, 0x5f, 0x63,
	0x6f, 0x64, 0x65, 0x18, 0x05, 0x20, 0x01, 0x28, 0x05, 0x52, 0x08, 0x65, 0x78, 0x69, 0x74, 0x43,
	0x6f, 0x64, 0x65, 0x12, 0x1f, 0x0a, 0x0b, 0x63, 0x6f, 0x6d, 0x70, 0x69, 0x6c, 0x65, 0x5f, 0x6d,
	0x73, 0x67, 0x18, 0x06, 0x20, 0x01, 0x28, 0x09, 0x52, 0x0a, 0x63, 0x6f, 0x6d, 0x70, 0x69, 0x6c,
	0x65, 0x4d, 0x73, 0x67, 0x12, 0x17, 0x0a, 0x07, 0x65, 0x72, 0x72, 0x5f, 0x6d, 0x73, 0x67, 0x18,
	0x07, 0x20, 0x01, 0x28, 0x09, 0x52, 0x06, 0x65, 0x72, 0x72, 0x4d, 0x73, 0x67, 0x12, 0x25, 0x0a,
	0x0e, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x65, 0x72, 0x5f, 0x73, 0x74, 0x64, 0x6f, 0x75, 0x74, 0x18,
	0x08, 0x20, 0x01, 0x28, 0x09, 0x52, 0x0d, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x65, 0x72, 0x53, 0x74,
	0x64, 0x6f, 0x75, 0x74, 0x12, 0x2a, 0x0a, 0x11, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x65, 0x72, 0x5f,
	0x65, 0x78, 0x69, 0x74, 0x5f, 0x63, 0x6f, 0x64, 0x65, 0x18, 0x09, 0x20, 0x01, 0x28, 0x05, 0x52,
	0x0f, 0x63, 0x68, 0x65, 0x63, 0x6b, 0x65, 0x72, 0x45, 0x78, 0x69, 0x74, 0x43, 0x6f, 0x64, 0x65,
	0x22, 0x3b, 0x0a, 0x14, 0x52, 0x75, 0x6e, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f,
	0x6e, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73, 0x74, 0x12, 0x23, 0x0a, 0x0d, 0x73, 0x75, 0x62, 0x6d,
	0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x5f, 0x69, 0x64, 0x18, 0x01, 0x20, 0x01, 0x28, 0x03, 0x52,
	0x0c, 0x73, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x49, 0x64, 0x22, 0x17, 0x0a,
	0x15, 0x52, 0x75, 0x6e, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x52, 0x65,
	0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x22, 0x5e, 0x0a, 0x15, 0x52, 0x75, 0x6e, 0x50, 0x72, 0x6f,
	0x62, 0x6c, 0x65, 0x6d, 0x46, 0x69, 0x6c, 0x65, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73, 0x74, 0x12,
	0x1d, 0x0a, 0x0a, 0x70, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x5f, 0x69, 0x64, 0x18, 0x01, 0x20,
	0x01, 0x28, 0x05, 0x52, 0x09, 0x70, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x49, 0x64, 0x12, 0x26,
	0x0a, 0x0f, 0x70, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x5f, 0x66, 0x69, 0x6c, 0x65, 0x5f, 0x69,
	0x64, 0x18, 0x02, 0x20, 0x01, 0x28, 0x05, 0x52, 0x0d, 0x70, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d,
	0x46, 0x69, 0x6c, 0x65, 0x49, 0x64, 0x22, 0x18, 0x0a, 0x16, 0x52, 0x75, 0x6e, 0x50, 0x72, 0x6f,
	0x62, 0x6c, 0x65, 0x6d, 0x46, 0x69, 0x6c, 0x65, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65,
	0x32, 0x9b, 0x02, 0x0a, 0x0e, 0x53, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x53, 0x65, 0x72, 0x76,
	0x69, 0x63, 0x65, 0x12, 0x42, 0x0a, 0x03, 0x52, 0x75, 0x6e, 0x12, 0x1b, 0x2e, 0x6a, 0x6e, 0x6f,
	0x6a, 0x2e, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x2e, 0x76, 0x31, 0x2e, 0x52, 0x75, 0x6e,
	0x52, 0x65, 0x71, 0x75, 0x65, 0x73, 0x74, 0x1a, 0x1c, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x73,
	0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x2e, 0x76, 0x31, 0x2e, 0x52, 0x75, 0x6e, 0x52, 0x65, 0x73,
	0x70, 0x6f, 0x6e, 0x73, 0x65, 0x22, 0x00, 0x12, 0x60, 0x0a, 0x0d, 0x52, 0x75, 0x6e, 0x53, 0x75,
	0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x12, 0x25, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e,
	0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x2e, 0x76, 0x31, 0x2e, 0x52, 0x75, 0x6e, 0x53, 0x75,
	0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73, 0x74, 0x1a,
	0x26, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x2e, 0x76,
	0x31, 0x2e, 0x52, 0x75, 0x6e, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x52,
	0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x22, 0x00, 0x12, 0x63, 0x0a, 0x0e, 0x52, 0x75, 0x6e,
	0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x46, 0x69, 0x6c, 0x65, 0x12, 0x26, 0x2e, 0x6a, 0x6e,
	0x6f, 0x6a, 0x2e, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x2e, 0x76, 0x31, 0x2e, 0x52, 0x75,
	0x6e, 0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x46, 0x69, 0x6c, 0x65, 0x52, 0x65, 0x71, 0x75,
	0x65, 0x73, 0x74, 0x1a, 0x27, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x73, 0x61, 0x6e, 0x64, 0x62,
	0x6f, 0x78, 0x2e, 0x76, 0x31, 0x2e, 0x52, 0x75, 0x6e, 0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d,
	0x46, 0x69, 0x6c, 0x65, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x22, 0x00, 0x42, 0x13,
	0x5a, 0x11, 0x61, 0x70, 0x69, 0x2f, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x2f, 0x76, 0x31,
	0x3b, 0x76, 0x31, 0x62, 0x06, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x33,
}

var (
	file_v1_sandbox_proto_rawDescOnce sync.Once
	file_v1_sandbox_proto_rawDescData = file_v1_sandbox_proto_rawDesc
)

func file_v1_sandbox_proto_rawDescGZIP() []byte {
	file_v1_sandbox_proto_rawDescOnce.Do(func() {
		file_v1_sandbox_proto_rawDescData = protoimpl.X.CompressGZIP(file_v1_sandbox_proto_rawDescData)
	})
	return file_v1_sandbox_proto_rawDescData
}

var file_v1_sandbox_proto_msgTypes = make([]protoimpl.MessageInfo, 6)
var file_v1_sandbox_proto_goTypes = []interface{}{
	(*RunRequest)(nil),             // 0: jnoj.sandbox.v1.RunRequest
	(*RunResponse)(nil),            // 1: jnoj.sandbox.v1.RunResponse
	(*RunSubmissionRequest)(nil),   // 2: jnoj.sandbox.v1.RunSubmissionRequest
	(*RunSubmissionResponse)(nil),  // 3: jnoj.sandbox.v1.RunSubmissionResponse
	(*RunProblemFileRequest)(nil),  // 4: jnoj.sandbox.v1.RunProblemFileRequest
	(*RunProblemFileResponse)(nil), // 5: jnoj.sandbox.v1.RunProblemFileResponse
}
var file_v1_sandbox_proto_depIdxs = []int32{
	0, // 0: jnoj.sandbox.v1.SandboxService.Run:input_type -> jnoj.sandbox.v1.RunRequest
	2, // 1: jnoj.sandbox.v1.SandboxService.RunSubmission:input_type -> jnoj.sandbox.v1.RunSubmissionRequest
	4, // 2: jnoj.sandbox.v1.SandboxService.RunProblemFile:input_type -> jnoj.sandbox.v1.RunProblemFileRequest
	1, // 3: jnoj.sandbox.v1.SandboxService.Run:output_type -> jnoj.sandbox.v1.RunResponse
	3, // 4: jnoj.sandbox.v1.SandboxService.RunSubmission:output_type -> jnoj.sandbox.v1.RunSubmissionResponse
	5, // 5: jnoj.sandbox.v1.SandboxService.RunProblemFile:output_type -> jnoj.sandbox.v1.RunProblemFileResponse
	3, // [3:6] is the sub-list for method output_type
	0, // [0:3] is the sub-list for method input_type
	0, // [0:0] is the sub-list for extension type_name
	0, // [0:0] is the sub-list for extension extendee
	0, // [0:0] is the sub-list for field type_name
}

func init() { file_v1_sandbox_proto_init() }
func file_v1_sandbox_proto_init() {
	if File_v1_sandbox_proto != nil {
		return
	}
	if !protoimpl.UnsafeEnabled {
		file_v1_sandbox_proto_msgTypes[0].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*RunRequest); i {
			case 0:
				return &v.state
			case 1:
				return &v.sizeCache
			case 2:
				return &v.unknownFields
			default:
				return nil
			}
		}
		file_v1_sandbox_proto_msgTypes[1].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*RunResponse); i {
			case 0:
				return &v.state
			case 1:
				return &v.sizeCache
			case 2:
				return &v.unknownFields
			default:
				return nil
			}
		}
		file_v1_sandbox_proto_msgTypes[2].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*RunSubmissionRequest); i {
			case 0:
				return &v.state
			case 1:
				return &v.sizeCache
			case 2:
				return &v.unknownFields
			default:
				return nil
			}
		}
		file_v1_sandbox_proto_msgTypes[3].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*RunSubmissionResponse); i {
			case 0:
				return &v.state
			case 1:
				return &v.sizeCache
			case 2:
				return &v.unknownFields
			default:
				return nil
			}
		}
		file_v1_sandbox_proto_msgTypes[4].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*RunProblemFileRequest); i {
			case 0:
				return &v.state
			case 1:
				return &v.sizeCache
			case 2:
				return &v.unknownFields
			default:
				return nil
			}
		}
		file_v1_sandbox_proto_msgTypes[5].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*RunProblemFileResponse); i {
			case 0:
				return &v.state
			case 1:
				return &v.sizeCache
			case 2:
				return &v.unknownFields
			default:
				return nil
			}
		}
	}
	file_v1_sandbox_proto_msgTypes[0].OneofWrappers = []interface{}{}
	type x struct{}
	out := protoimpl.TypeBuilder{
		File: protoimpl.DescBuilder{
			GoPackagePath: reflect.TypeOf(x{}).PkgPath(),
			RawDescriptor: file_v1_sandbox_proto_rawDesc,
			NumEnums:      0,
			NumMessages:   6,
			NumExtensions: 0,
			NumServices:   1,
		},
		GoTypes:           file_v1_sandbox_proto_goTypes,
		DependencyIndexes: file_v1_sandbox_proto_depIdxs,
		MessageInfos:      file_v1_sandbox_proto_msgTypes,
	}.Build()
	File_v1_sandbox_proto = out.File
	file_v1_sandbox_proto_rawDesc = nil
	file_v1_sandbox_proto_goTypes = nil
	file_v1_sandbox_proto_depIdxs = nil
}
