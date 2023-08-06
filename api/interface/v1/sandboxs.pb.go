// Code generated by protoc-gen-go. DO NOT EDIT.
// versions:
// 	protoc-gen-go v1.31.0
// 	protoc        v4.23.4
// source: v1/sandboxs.proto

package v1

import (
	_ "github.com/envoyproxy/protoc-gen-validate/validate"
	_ "google.golang.org/genproto/googleapis/api/annotations"
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

	// 源码
	Source string `protobuf:"bytes,1,opt,name=source,proto3" json:"source,omitempty"`
	// 输入
	Stdin []string `protobuf:"bytes,2,rep,name=stdin,proto3" json:"stdin,omitempty"`
	// 语言
	Language int32 `protobuf:"varint,3,opt,name=language,proto3" json:"language,omitempty"`
	// 针对 函数题，需要查询对应的输入输出主体函数
	LanguageId *int32 `protobuf:"varint,4,opt,name=language_id,json=languageId,proto3,oneof" json:"language_id,omitempty"`
	// 内存限制
	MemoryLimit int64 `protobuf:"varint,5,opt,name=memory_limit,json=memoryLimit,proto3" json:"memory_limit,omitempty"`
	// 时间限制
	TimeLimit int64 `protobuf:"varint,6,opt,name=time_limit,json=timeLimit,proto3" json:"time_limit,omitempty"`
}

func (x *RunRequest) Reset() {
	*x = RunRequest{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandboxs_proto_msgTypes[0]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunRequest) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunRequest) ProtoMessage() {}

func (x *RunRequest) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandboxs_proto_msgTypes[0]
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
	return file_v1_sandboxs_proto_rawDescGZIP(), []int{0}
}

func (x *RunRequest) GetSource() string {
	if x != nil {
		return x.Source
	}
	return ""
}

func (x *RunRequest) GetStdin() []string {
	if x != nil {
		return x.Stdin
	}
	return nil
}

func (x *RunRequest) GetLanguage() int32 {
	if x != nil {
		return x.Language
	}
	return 0
}

func (x *RunRequest) GetLanguageId() int32 {
	if x != nil && x.LanguageId != nil {
		return *x.LanguageId
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

type RunResult struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Stdout   string `protobuf:"bytes,1,opt,name=stdout,proto3" json:"stdout,omitempty"`
	Stderr   string `protobuf:"bytes,2,opt,name=stderr,proto3" json:"stderr,omitempty"`
	Time     int64  `protobuf:"varint,3,opt,name=time,proto3" json:"time,omitempty"`
	Memory   int64  `protobuf:"varint,4,opt,name=memory,proto3" json:"memory,omitempty"`
	ExitCode int32  `protobuf:"varint,5,opt,name=exit_code,json=exitCode,proto3" json:"exit_code,omitempty"`
	ErrMsg   string `protobuf:"bytes,6,opt,name=err_msg,json=errMsg,proto3" json:"err_msg,omitempty"`
}

func (x *RunResult) Reset() {
	*x = RunResult{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandboxs_proto_msgTypes[1]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunResult) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunResult) ProtoMessage() {}

func (x *RunResult) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandboxs_proto_msgTypes[1]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use RunResult.ProtoReflect.Descriptor instead.
func (*RunResult) Descriptor() ([]byte, []int) {
	return file_v1_sandboxs_proto_rawDescGZIP(), []int{1}
}

func (x *RunResult) GetStdout() string {
	if x != nil {
		return x.Stdout
	}
	return ""
}

func (x *RunResult) GetStderr() string {
	if x != nil {
		return x.Stderr
	}
	return ""
}

func (x *RunResult) GetTime() int64 {
	if x != nil {
		return x.Time
	}
	return 0
}

func (x *RunResult) GetMemory() int64 {
	if x != nil {
		return x.Memory
	}
	return 0
}

func (x *RunResult) GetExitCode() int32 {
	if x != nil {
		return x.ExitCode
	}
	return 0
}

func (x *RunResult) GetErrMsg() string {
	if x != nil {
		return x.ErrMsg
	}
	return ""
}

type RunResponse struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Results    []*RunResult `protobuf:"bytes,1,rep,name=results,proto3" json:"results,omitempty"`
	CompileMsg string       `protobuf:"bytes,2,opt,name=compile_msg,json=compileMsg,proto3" json:"compile_msg,omitempty"`
}

func (x *RunResponse) Reset() {
	*x = RunResponse{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_sandboxs_proto_msgTypes[2]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *RunResponse) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*RunResponse) ProtoMessage() {}

func (x *RunResponse) ProtoReflect() protoreflect.Message {
	mi := &file_v1_sandboxs_proto_msgTypes[2]
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
	return file_v1_sandboxs_proto_rawDescGZIP(), []int{2}
}

func (x *RunResponse) GetResults() []*RunResult {
	if x != nil {
		return x.Results
	}
	return nil
}

func (x *RunResponse) GetCompileMsg() string {
	if x != nil {
		return x.CompileMsg
	}
	return ""
}

var File_v1_sandboxs_proto protoreflect.FileDescriptor

var file_v1_sandboxs_proto_rawDesc = []byte{
	0x0a, 0x11, 0x76, 0x31, 0x2f, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x73, 0x2e, 0x70, 0x72,
	0x6f, 0x74, 0x6f, 0x12, 0x1a, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65, 0x72, 0x66,
	0x61, 0x63, 0x65, 0x2e, 0x76, 0x31, 0x2e, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x73, 0x1a,
	0x1c, 0x67, 0x6f, 0x6f, 0x67, 0x6c, 0x65, 0x2f, 0x61, 0x70, 0x69, 0x2f, 0x61, 0x6e, 0x6e, 0x6f,
	0x74, 0x61, 0x74, 0x69, 0x6f, 0x6e, 0x73, 0x2e, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x1a, 0x17, 0x76,
	0x61, 0x6c, 0x69, 0x64, 0x61, 0x74, 0x65, 0x2f, 0x76, 0x61, 0x6c, 0x69, 0x64, 0x61, 0x74, 0x65,
	0x2e, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x22, 0xe7, 0x01, 0x0a, 0x0a, 0x52, 0x75, 0x6e, 0x52, 0x65,
	0x71, 0x75, 0x65, 0x73, 0x74, 0x12, 0x16, 0x0a, 0x06, 0x73, 0x6f, 0x75, 0x72, 0x63, 0x65, 0x18,
	0x01, 0x20, 0x01, 0x28, 0x09, 0x52, 0x06, 0x73, 0x6f, 0x75, 0x72, 0x63, 0x65, 0x12, 0x14, 0x0a,
	0x05, 0x73, 0x74, 0x64, 0x69, 0x6e, 0x18, 0x02, 0x20, 0x03, 0x28, 0x09, 0x52, 0x05, 0x73, 0x74,
	0x64, 0x69, 0x6e, 0x12, 0x1a, 0x0a, 0x08, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65, 0x18,
	0x03, 0x20, 0x01, 0x28, 0x05, 0x52, 0x08, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65, 0x12,
	0x24, 0x0a, 0x0b, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65, 0x5f, 0x69, 0x64, 0x18, 0x04,
	0x20, 0x01, 0x28, 0x05, 0x48, 0x00, 0x52, 0x0a, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65,
	0x49, 0x64, 0x88, 0x01, 0x01, 0x12, 0x2d, 0x0a, 0x0c, 0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x5f,
	0x6c, 0x69, 0x6d, 0x69, 0x74, 0x18, 0x05, 0x20, 0x01, 0x28, 0x03, 0x42, 0x0a, 0xfa, 0x42, 0x07,
	0x22, 0x05, 0x18, 0x80, 0x08, 0x28, 0x04, 0x52, 0x0b, 0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x4c,
	0x69, 0x6d, 0x69, 0x74, 0x12, 0x2a, 0x0a, 0x0a, 0x74, 0x69, 0x6d, 0x65, 0x5f, 0x6c, 0x69, 0x6d,
	0x69, 0x74, 0x18, 0x06, 0x20, 0x01, 0x28, 0x03, 0x42, 0x0b, 0xfa, 0x42, 0x08, 0x22, 0x06, 0x18,
	0x98, 0x75, 0x28, 0xfa, 0x01, 0x52, 0x09, 0x74, 0x69, 0x6d, 0x65, 0x4c, 0x69, 0x6d, 0x69, 0x74,
	0x42, 0x0e, 0x0a, 0x0c, 0x5f, 0x6c, 0x61, 0x6e, 0x67, 0x75, 0x61, 0x67, 0x65, 0x5f, 0x69, 0x64,
	0x22, 0x9d, 0x01, 0x0a, 0x09, 0x52, 0x75, 0x6e, 0x52, 0x65, 0x73, 0x75, 0x6c, 0x74, 0x12, 0x16,
	0x0a, 0x06, 0x73, 0x74, 0x64, 0x6f, 0x75, 0x74, 0x18, 0x01, 0x20, 0x01, 0x28, 0x09, 0x52, 0x06,
	0x73, 0x74, 0x64, 0x6f, 0x75, 0x74, 0x12, 0x16, 0x0a, 0x06, 0x73, 0x74, 0x64, 0x65, 0x72, 0x72,
	0x18, 0x02, 0x20, 0x01, 0x28, 0x09, 0x52, 0x06, 0x73, 0x74, 0x64, 0x65, 0x72, 0x72, 0x12, 0x12,
	0x0a, 0x04, 0x74, 0x69, 0x6d, 0x65, 0x18, 0x03, 0x20, 0x01, 0x28, 0x03, 0x52, 0x04, 0x74, 0x69,
	0x6d, 0x65, 0x12, 0x16, 0x0a, 0x06, 0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x18, 0x04, 0x20, 0x01,
	0x28, 0x03, 0x52, 0x06, 0x6d, 0x65, 0x6d, 0x6f, 0x72, 0x79, 0x12, 0x1b, 0x0a, 0x09, 0x65, 0x78,
	0x69, 0x74, 0x5f, 0x63, 0x6f, 0x64, 0x65, 0x18, 0x05, 0x20, 0x01, 0x28, 0x05, 0x52, 0x08, 0x65,
	0x78, 0x69, 0x74, 0x43, 0x6f, 0x64, 0x65, 0x12, 0x17, 0x0a, 0x07, 0x65, 0x72, 0x72, 0x5f, 0x6d,
	0x73, 0x67, 0x18, 0x06, 0x20, 0x01, 0x28, 0x09, 0x52, 0x06, 0x65, 0x72, 0x72, 0x4d, 0x73, 0x67,
	0x22, 0x6f, 0x0a, 0x0b, 0x52, 0x75, 0x6e, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x12,
	0x3f, 0x0a, 0x07, 0x72, 0x65, 0x73, 0x75, 0x6c, 0x74, 0x73, 0x18, 0x01, 0x20, 0x03, 0x28, 0x0b,
	0x32, 0x25, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65, 0x72, 0x66, 0x61, 0x63,
	0x65, 0x2e, 0x76, 0x31, 0x2e, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x73, 0x2e, 0x52, 0x75,
	0x6e, 0x52, 0x65, 0x73, 0x75, 0x6c, 0x74, 0x52, 0x07, 0x72, 0x65, 0x73, 0x75, 0x6c, 0x74, 0x73,
	0x12, 0x1f, 0x0a, 0x0b, 0x63, 0x6f, 0x6d, 0x70, 0x69, 0x6c, 0x65, 0x5f, 0x6d, 0x73, 0x67, 0x18,
	0x02, 0x20, 0x01, 0x28, 0x09, 0x52, 0x0a, 0x63, 0x6f, 0x6d, 0x70, 0x69, 0x6c, 0x65, 0x4d, 0x73,
	0x67, 0x32, 0x7f, 0x0a, 0x0f, 0x53, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x73, 0x53, 0x65, 0x72,
	0x76, 0x69, 0x63, 0x65, 0x12, 0x6c, 0x0a, 0x03, 0x52, 0x75, 0x6e, 0x12, 0x26, 0x2e, 0x6a, 0x6e,
	0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65, 0x72, 0x66, 0x61, 0x63, 0x65, 0x2e, 0x76, 0x31, 0x2e,
	0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x73, 0x2e, 0x52, 0x75, 0x6e, 0x52, 0x65, 0x71, 0x75,
	0x65, 0x73, 0x74, 0x1a, 0x27, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65, 0x72,
	0x66, 0x61, 0x63, 0x65, 0x2e, 0x76, 0x31, 0x2e, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f, 0x78, 0x73,
	0x2e, 0x52, 0x75, 0x6e, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x22, 0x14, 0x82, 0xd3,
	0xe4, 0x93, 0x02, 0x0e, 0x3a, 0x01, 0x2a, 0x22, 0x09, 0x2f, 0x73, 0x61, 0x6e, 0x64, 0x62, 0x6f,
	0x78, 0x73, 0x42, 0x15, 0x5a, 0x13, 0x61, 0x70, 0x69, 0x2f, 0x69, 0x6e, 0x74, 0x65, 0x72, 0x66,
	0x61, 0x63, 0x65, 0x2f, 0x76, 0x31, 0x3b, 0x76, 0x31, 0x62, 0x06, 0x70, 0x72, 0x6f, 0x74, 0x6f,
	0x33,
}

var (
	file_v1_sandboxs_proto_rawDescOnce sync.Once
	file_v1_sandboxs_proto_rawDescData = file_v1_sandboxs_proto_rawDesc
)

func file_v1_sandboxs_proto_rawDescGZIP() []byte {
	file_v1_sandboxs_proto_rawDescOnce.Do(func() {
		file_v1_sandboxs_proto_rawDescData = protoimpl.X.CompressGZIP(file_v1_sandboxs_proto_rawDescData)
	})
	return file_v1_sandboxs_proto_rawDescData
}

var file_v1_sandboxs_proto_msgTypes = make([]protoimpl.MessageInfo, 3)
var file_v1_sandboxs_proto_goTypes = []interface{}{
	(*RunRequest)(nil),  // 0: jnoj.interface.v1.sandboxs.RunRequest
	(*RunResult)(nil),   // 1: jnoj.interface.v1.sandboxs.RunResult
	(*RunResponse)(nil), // 2: jnoj.interface.v1.sandboxs.RunResponse
}
var file_v1_sandboxs_proto_depIdxs = []int32{
	1, // 0: jnoj.interface.v1.sandboxs.RunResponse.results:type_name -> jnoj.interface.v1.sandboxs.RunResult
	0, // 1: jnoj.interface.v1.sandboxs.SandboxsService.Run:input_type -> jnoj.interface.v1.sandboxs.RunRequest
	2, // 2: jnoj.interface.v1.sandboxs.SandboxsService.Run:output_type -> jnoj.interface.v1.sandboxs.RunResponse
	2, // [2:3] is the sub-list for method output_type
	1, // [1:2] is the sub-list for method input_type
	1, // [1:1] is the sub-list for extension type_name
	1, // [1:1] is the sub-list for extension extendee
	0, // [0:1] is the sub-list for field type_name
}

func init() { file_v1_sandboxs_proto_init() }
func file_v1_sandboxs_proto_init() {
	if File_v1_sandboxs_proto != nil {
		return
	}
	if !protoimpl.UnsafeEnabled {
		file_v1_sandboxs_proto_msgTypes[0].Exporter = func(v interface{}, i int) interface{} {
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
		file_v1_sandboxs_proto_msgTypes[1].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*RunResult); i {
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
		file_v1_sandboxs_proto_msgTypes[2].Exporter = func(v interface{}, i int) interface{} {
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
	}
	file_v1_sandboxs_proto_msgTypes[0].OneofWrappers = []interface{}{}
	type x struct{}
	out := protoimpl.TypeBuilder{
		File: protoimpl.DescBuilder{
			GoPackagePath: reflect.TypeOf(x{}).PkgPath(),
			RawDescriptor: file_v1_sandboxs_proto_rawDesc,
			NumEnums:      0,
			NumMessages:   3,
			NumExtensions: 0,
			NumServices:   1,
		},
		GoTypes:           file_v1_sandboxs_proto_goTypes,
		DependencyIndexes: file_v1_sandboxs_proto_depIdxs,
		MessageInfos:      file_v1_sandboxs_proto_msgTypes,
	}.Build()
	File_v1_sandboxs_proto = out.File
	file_v1_sandboxs_proto_rawDesc = nil
	file_v1_sandboxs_proto_goTypes = nil
	file_v1_sandboxs_proto_depIdxs = nil
}
