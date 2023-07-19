// Code generated by protoc-gen-go. DO NOT EDIT.
// versions:
// 	protoc-gen-go v1.31.0
// 	protoc        v4.23.4
// source: v1/problem.proto

package v1

import (
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

type Problem struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields
}

func (x *Problem) Reset() {
	*x = Problem{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_problem_proto_msgTypes[0]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *Problem) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*Problem) ProtoMessage() {}

func (x *Problem) ProtoReflect() protoreflect.Message {
	mi := &file_v1_problem_proto_msgTypes[0]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use Problem.ProtoReflect.Descriptor instead.
func (*Problem) Descriptor() ([]byte, []int) {
	return file_v1_problem_proto_rawDescGZIP(), []int{0}
}

type ListProblemsRequest struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Data  []*Problem `protobuf:"bytes,1,rep,name=data,proto3" json:"data,omitempty"`
	Total int64      `protobuf:"varint,2,opt,name=total,proto3" json:"total,omitempty"`
}

func (x *ListProblemsRequest) Reset() {
	*x = ListProblemsRequest{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_problem_proto_msgTypes[1]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *ListProblemsRequest) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*ListProblemsRequest) ProtoMessage() {}

func (x *ListProblemsRequest) ProtoReflect() protoreflect.Message {
	mi := &file_v1_problem_proto_msgTypes[1]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use ListProblemsRequest.ProtoReflect.Descriptor instead.
func (*ListProblemsRequest) Descriptor() ([]byte, []int) {
	return file_v1_problem_proto_rawDescGZIP(), []int{1}
}

func (x *ListProblemsRequest) GetData() []*Problem {
	if x != nil {
		return x.Data
	}
	return nil
}

func (x *ListProblemsRequest) GetTotal() int64 {
	if x != nil {
		return x.Total
	}
	return 0
}

type ListProblemsResponse struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Name    string `protobuf:"bytes,1,opt,name=name,proto3" json:"name,omitempty"`
	Page    int32  `protobuf:"varint,2,opt,name=page,proto3" json:"page,omitempty"`
	PerPage int32  `protobuf:"varint,3,opt,name=per_page,json=perPage,proto3" json:"per_page,omitempty"`
}

func (x *ListProblemsResponse) Reset() {
	*x = ListProblemsResponse{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_problem_proto_msgTypes[2]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *ListProblemsResponse) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*ListProblemsResponse) ProtoMessage() {}

func (x *ListProblemsResponse) ProtoReflect() protoreflect.Message {
	mi := &file_v1_problem_proto_msgTypes[2]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use ListProblemsResponse.ProtoReflect.Descriptor instead.
func (*ListProblemsResponse) Descriptor() ([]byte, []int) {
	return file_v1_problem_proto_rawDescGZIP(), []int{2}
}

func (x *ListProblemsResponse) GetName() string {
	if x != nil {
		return x.Name
	}
	return ""
}

func (x *ListProblemsResponse) GetPage() int32 {
	if x != nil {
		return x.Page
	}
	return 0
}

func (x *ListProblemsResponse) GetPerPage() int32 {
	if x != nil {
		return x.PerPage
	}
	return 0
}

var File_v1_problem_proto protoreflect.FileDescriptor

var file_v1_problem_proto_rawDesc = []byte{
	0x0a, 0x10, 0x76, 0x31, 0x2f, 0x70, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x2e, 0x70, 0x72, 0x6f,
	0x74, 0x6f, 0x12, 0x0d, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x61, 0x64, 0x6d, 0x69, 0x6e, 0x2e, 0x76,
	0x31, 0x1a, 0x1c, 0x67, 0x6f, 0x6f, 0x67, 0x6c, 0x65, 0x2f, 0x61, 0x70, 0x69, 0x2f, 0x61, 0x6e,
	0x6e, 0x6f, 0x74, 0x61, 0x74, 0x69, 0x6f, 0x6e, 0x73, 0x2e, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x22,
	0x09, 0x0a, 0x07, 0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x22, 0x57, 0x0a, 0x13, 0x4c, 0x69,
	0x73, 0x74, 0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x73, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73,
	0x74, 0x12, 0x2a, 0x0a, 0x04, 0x64, 0x61, 0x74, 0x61, 0x18, 0x01, 0x20, 0x03, 0x28, 0x0b, 0x32,
	0x16, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x61, 0x64, 0x6d, 0x69, 0x6e, 0x2e, 0x76, 0x31, 0x2e,
	0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x52, 0x04, 0x64, 0x61, 0x74, 0x61, 0x12, 0x14, 0x0a,
	0x05, 0x74, 0x6f, 0x74, 0x61, 0x6c, 0x18, 0x02, 0x20, 0x01, 0x28, 0x03, 0x52, 0x05, 0x74, 0x6f,
	0x74, 0x61, 0x6c, 0x22, 0x59, 0x0a, 0x14, 0x4c, 0x69, 0x73, 0x74, 0x50, 0x72, 0x6f, 0x62, 0x6c,
	0x65, 0x6d, 0x73, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x12, 0x12, 0x0a, 0x04, 0x6e,
	0x61, 0x6d, 0x65, 0x18, 0x01, 0x20, 0x01, 0x28, 0x09, 0x52, 0x04, 0x6e, 0x61, 0x6d, 0x65, 0x12,
	0x12, 0x0a, 0x04, 0x70, 0x61, 0x67, 0x65, 0x18, 0x02, 0x20, 0x01, 0x28, 0x05, 0x52, 0x04, 0x70,
	0x61, 0x67, 0x65, 0x12, 0x19, 0x0a, 0x08, 0x70, 0x65, 0x72, 0x5f, 0x70, 0x61, 0x67, 0x65, 0x18,
	0x03, 0x20, 0x01, 0x28, 0x05, 0x52, 0x07, 0x70, 0x65, 0x72, 0x50, 0x61, 0x67, 0x65, 0x32, 0x7c,
	0x0a, 0x0e, 0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x53, 0x65, 0x72, 0x76, 0x69, 0x63, 0x65,
	0x12, 0x6a, 0x0a, 0x0c, 0x4c, 0x69, 0x73, 0x74, 0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x73,
	0x12, 0x22, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x61, 0x64, 0x6d, 0x69, 0x6e, 0x2e, 0x76, 0x31,
	0x2e, 0x4c, 0x69, 0x73, 0x74, 0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x73, 0x52, 0x65, 0x71,
	0x75, 0x65, 0x73, 0x74, 0x1a, 0x23, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x61, 0x64, 0x6d, 0x69,
	0x6e, 0x2e, 0x76, 0x31, 0x2e, 0x4c, 0x69, 0x73, 0x74, 0x50, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d,
	0x73, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x22, 0x11, 0x82, 0xd3, 0xe4, 0x93, 0x02,
	0x0b, 0x12, 0x09, 0x2f, 0x70, 0x72, 0x6f, 0x62, 0x6c, 0x65, 0x6d, 0x73, 0x42, 0x11, 0x5a, 0x0f,
	0x61, 0x70, 0x69, 0x2f, 0x61, 0x64, 0x6d, 0x69, 0x6e, 0x2f, 0x76, 0x31, 0x3b, 0x76, 0x31, 0x62,
	0x06, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x33,
}

var (
	file_v1_problem_proto_rawDescOnce sync.Once
	file_v1_problem_proto_rawDescData = file_v1_problem_proto_rawDesc
)

func file_v1_problem_proto_rawDescGZIP() []byte {
	file_v1_problem_proto_rawDescOnce.Do(func() {
		file_v1_problem_proto_rawDescData = protoimpl.X.CompressGZIP(file_v1_problem_proto_rawDescData)
	})
	return file_v1_problem_proto_rawDescData
}

var file_v1_problem_proto_msgTypes = make([]protoimpl.MessageInfo, 3)
var file_v1_problem_proto_goTypes = []interface{}{
	(*Problem)(nil),              // 0: jnoj.admin.v1.Problem
	(*ListProblemsRequest)(nil),  // 1: jnoj.admin.v1.ListProblemsRequest
	(*ListProblemsResponse)(nil), // 2: jnoj.admin.v1.ListProblemsResponse
}
var file_v1_problem_proto_depIdxs = []int32{
	0, // 0: jnoj.admin.v1.ListProblemsRequest.data:type_name -> jnoj.admin.v1.Problem
	1, // 1: jnoj.admin.v1.ProblemService.ListProblems:input_type -> jnoj.admin.v1.ListProblemsRequest
	2, // 2: jnoj.admin.v1.ProblemService.ListProblems:output_type -> jnoj.admin.v1.ListProblemsResponse
	2, // [2:3] is the sub-list for method output_type
	1, // [1:2] is the sub-list for method input_type
	1, // [1:1] is the sub-list for extension type_name
	1, // [1:1] is the sub-list for extension extendee
	0, // [0:1] is the sub-list for field type_name
}

func init() { file_v1_problem_proto_init() }
func file_v1_problem_proto_init() {
	if File_v1_problem_proto != nil {
		return
	}
	if !protoimpl.UnsafeEnabled {
		file_v1_problem_proto_msgTypes[0].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*Problem); i {
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
		file_v1_problem_proto_msgTypes[1].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*ListProblemsRequest); i {
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
		file_v1_problem_proto_msgTypes[2].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*ListProblemsResponse); i {
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
	type x struct{}
	out := protoimpl.TypeBuilder{
		File: protoimpl.DescBuilder{
			GoPackagePath: reflect.TypeOf(x{}).PkgPath(),
			RawDescriptor: file_v1_problem_proto_rawDesc,
			NumEnums:      0,
			NumMessages:   3,
			NumExtensions: 0,
			NumServices:   1,
		},
		GoTypes:           file_v1_problem_proto_goTypes,
		DependencyIndexes: file_v1_problem_proto_depIdxs,
		MessageInfos:      file_v1_problem_proto_msgTypes,
	}.Build()
	File_v1_problem_proto = out.File
	file_v1_problem_proto_rawDesc = nil
	file_v1_problem_proto_goTypes = nil
	file_v1_problem_proto_depIdxs = nil
}
