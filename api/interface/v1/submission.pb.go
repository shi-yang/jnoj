// Code generated by protoc-gen-go. DO NOT EDIT.
// versions:
// 	protoc-gen-go v1.28.1
// 	protoc        v3.19.4
// source: v1/submission.proto

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

// 题目
type Submission struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields
}

func (x *Submission) Reset() {
	*x = Submission{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_submission_proto_msgTypes[0]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *Submission) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*Submission) ProtoMessage() {}

func (x *Submission) ProtoReflect() protoreflect.Message {
	mi := &file_v1_submission_proto_msgTypes[0]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use Submission.ProtoReflect.Descriptor instead.
func (*Submission) Descriptor() ([]byte, []int) {
	return file_v1_submission_proto_rawDescGZIP(), []int{0}
}

type ListSubmissionsRequest struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields
}

func (x *ListSubmissionsRequest) Reset() {
	*x = ListSubmissionsRequest{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_submission_proto_msgTypes[1]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *ListSubmissionsRequest) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*ListSubmissionsRequest) ProtoMessage() {}

func (x *ListSubmissionsRequest) ProtoReflect() protoreflect.Message {
	mi := &file_v1_submission_proto_msgTypes[1]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use ListSubmissionsRequest.ProtoReflect.Descriptor instead.
func (*ListSubmissionsRequest) Descriptor() ([]byte, []int) {
	return file_v1_submission_proto_rawDescGZIP(), []int{1}
}

type ListSubmissionsResponse struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields
}

func (x *ListSubmissionsResponse) Reset() {
	*x = ListSubmissionsResponse{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_submission_proto_msgTypes[2]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *ListSubmissionsResponse) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*ListSubmissionsResponse) ProtoMessage() {}

func (x *ListSubmissionsResponse) ProtoReflect() protoreflect.Message {
	mi := &file_v1_submission_proto_msgTypes[2]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use ListSubmissionsResponse.ProtoReflect.Descriptor instead.
func (*ListSubmissionsResponse) Descriptor() ([]byte, []int) {
	return file_v1_submission_proto_rawDescGZIP(), []int{2}
}

type GetSubmissionRequest struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Id int32 `protobuf:"varint,1,opt,name=id,proto3" json:"id,omitempty"`
}

func (x *GetSubmissionRequest) Reset() {
	*x = GetSubmissionRequest{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_submission_proto_msgTypes[3]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *GetSubmissionRequest) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*GetSubmissionRequest) ProtoMessage() {}

func (x *GetSubmissionRequest) ProtoReflect() protoreflect.Message {
	mi := &file_v1_submission_proto_msgTypes[3]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use GetSubmissionRequest.ProtoReflect.Descriptor instead.
func (*GetSubmissionRequest) Descriptor() ([]byte, []int) {
	return file_v1_submission_proto_rawDescGZIP(), []int{3}
}

func (x *GetSubmissionRequest) GetId() int32 {
	if x != nil {
		return x.Id
	}
	return 0
}

type CreateSubmissionRequest struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields
}

func (x *CreateSubmissionRequest) Reset() {
	*x = CreateSubmissionRequest{}
	if protoimpl.UnsafeEnabled {
		mi := &file_v1_submission_proto_msgTypes[4]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *CreateSubmissionRequest) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*CreateSubmissionRequest) ProtoMessage() {}

func (x *CreateSubmissionRequest) ProtoReflect() protoreflect.Message {
	mi := &file_v1_submission_proto_msgTypes[4]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use CreateSubmissionRequest.ProtoReflect.Descriptor instead.
func (*CreateSubmissionRequest) Descriptor() ([]byte, []int) {
	return file_v1_submission_proto_rawDescGZIP(), []int{4}
}

var File_v1_submission_proto protoreflect.FileDescriptor

var file_v1_submission_proto_rawDesc = []byte{
	0x0a, 0x13, 0x76, 0x31, 0x2f, 0x73, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x2e,
	0x70, 0x72, 0x6f, 0x74, 0x6f, 0x12, 0x11, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65,
	0x72, 0x66, 0x61, 0x63, 0x65, 0x2e, 0x76, 0x31, 0x1a, 0x1c, 0x67, 0x6f, 0x6f, 0x67, 0x6c, 0x65,
	0x2f, 0x61, 0x70, 0x69, 0x2f, 0x61, 0x6e, 0x6e, 0x6f, 0x74, 0x61, 0x74, 0x69, 0x6f, 0x6e, 0x73,
	0x2e, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x22, 0x0c, 0x0a, 0x0a, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73,
	0x73, 0x69, 0x6f, 0x6e, 0x22, 0x18, 0x0a, 0x16, 0x4c, 0x69, 0x73, 0x74, 0x53, 0x75, 0x62, 0x6d,
	0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x73, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73, 0x74, 0x22, 0x19,
	0x0a, 0x17, 0x4c, 0x69, 0x73, 0x74, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e,
	0x73, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x22, 0x26, 0x0a, 0x14, 0x47, 0x65, 0x74,
	0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73,
	0x74, 0x12, 0x0e, 0x0a, 0x02, 0x69, 0x64, 0x18, 0x01, 0x20, 0x01, 0x28, 0x05, 0x52, 0x02, 0x69,
	0x64, 0x22, 0x19, 0x0a, 0x17, 0x43, 0x72, 0x65, 0x61, 0x74, 0x65, 0x53, 0x75, 0x62, 0x6d, 0x69,
	0x73, 0x73, 0x69, 0x6f, 0x6e, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73, 0x74, 0x32, 0xff, 0x02, 0x0a,
	0x11, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x53, 0x65, 0x72, 0x76, 0x69,
	0x63, 0x65, 0x12, 0x7e, 0x0a, 0x0f, 0x4c, 0x69, 0x73, 0x74, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73,
	0x73, 0x69, 0x6f, 0x6e, 0x73, 0x12, 0x29, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74,
	0x65, 0x72, 0x66, 0x61, 0x63, 0x65, 0x2e, 0x76, 0x31, 0x2e, 0x4c, 0x69, 0x73, 0x74, 0x53, 0x75,
	0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x73, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73, 0x74,
	0x1a, 0x2a, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65, 0x72, 0x66, 0x61, 0x63,
	0x65, 0x2e, 0x76, 0x31, 0x2e, 0x4c, 0x69, 0x73, 0x74, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73,
	0x69, 0x6f, 0x6e, 0x73, 0x52, 0x65, 0x73, 0x70, 0x6f, 0x6e, 0x73, 0x65, 0x22, 0x14, 0x82, 0xd3,
	0xe4, 0x93, 0x02, 0x0e, 0x12, 0x0c, 0x2f, 0x73, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f,
	0x6e, 0x73, 0x12, 0x72, 0x0a, 0x0d, 0x47, 0x65, 0x74, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73,
	0x69, 0x6f, 0x6e, 0x12, 0x27, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65, 0x72,
	0x66, 0x61, 0x63, 0x65, 0x2e, 0x76, 0x31, 0x2e, 0x47, 0x65, 0x74, 0x53, 0x75, 0x62, 0x6d, 0x69,
	0x73, 0x73, 0x69, 0x6f, 0x6e, 0x52, 0x65, 0x71, 0x75, 0x65, 0x73, 0x74, 0x1a, 0x1d, 0x2e, 0x6a,
	0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65, 0x72, 0x66, 0x61, 0x63, 0x65, 0x2e, 0x76, 0x31,
	0x2e, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x22, 0x19, 0x82, 0xd3, 0xe4,
	0x93, 0x02, 0x13, 0x12, 0x11, 0x2f, 0x73, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e,
	0x73, 0x2f, 0x7b, 0x69, 0x64, 0x7d, 0x12, 0x76, 0x0a, 0x10, 0x43, 0x72, 0x65, 0x61, 0x74, 0x65,
	0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x12, 0x2a, 0x2e, 0x6a, 0x6e, 0x6f,
	0x6a, 0x2e, 0x69, 0x6e, 0x74, 0x65, 0x72, 0x66, 0x61, 0x63, 0x65, 0x2e, 0x76, 0x31, 0x2e, 0x43,
	0x72, 0x65, 0x61, 0x74, 0x65, 0x53, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x52,
	0x65, 0x71, 0x75, 0x65, 0x73, 0x74, 0x1a, 0x1d, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x69, 0x6e,
	0x74, 0x65, 0x72, 0x66, 0x61, 0x63, 0x65, 0x2e, 0x76, 0x31, 0x2e, 0x53, 0x75, 0x62, 0x6d, 0x69,
	0x73, 0x73, 0x69, 0x6f, 0x6e, 0x22, 0x17, 0x82, 0xd3, 0xe4, 0x93, 0x02, 0x11, 0x22, 0x0c, 0x2f,
	0x73, 0x75, 0x62, 0x6d, 0x69, 0x73, 0x73, 0x69, 0x6f, 0x6e, 0x73, 0x3a, 0x01, 0x2a, 0x42, 0x15,
	0x5a, 0x13, 0x61, 0x70, 0x69, 0x2f, 0x69, 0x6e, 0x74, 0x65, 0x72, 0x66, 0x61, 0x63, 0x65, 0x2f,
	0x76, 0x31, 0x3b, 0x76, 0x31, 0x62, 0x06, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x33,
}

var (
	file_v1_submission_proto_rawDescOnce sync.Once
	file_v1_submission_proto_rawDescData = file_v1_submission_proto_rawDesc
)

func file_v1_submission_proto_rawDescGZIP() []byte {
	file_v1_submission_proto_rawDescOnce.Do(func() {
		file_v1_submission_proto_rawDescData = protoimpl.X.CompressGZIP(file_v1_submission_proto_rawDescData)
	})
	return file_v1_submission_proto_rawDescData
}

var file_v1_submission_proto_msgTypes = make([]protoimpl.MessageInfo, 5)
var file_v1_submission_proto_goTypes = []interface{}{
	(*Submission)(nil),              // 0: jnoj.interface.v1.Submission
	(*ListSubmissionsRequest)(nil),  // 1: jnoj.interface.v1.ListSubmissionsRequest
	(*ListSubmissionsResponse)(nil), // 2: jnoj.interface.v1.ListSubmissionsResponse
	(*GetSubmissionRequest)(nil),    // 3: jnoj.interface.v1.GetSubmissionRequest
	(*CreateSubmissionRequest)(nil), // 4: jnoj.interface.v1.CreateSubmissionRequest
}
var file_v1_submission_proto_depIdxs = []int32{
	1, // 0: jnoj.interface.v1.SubmissionService.ListSubmissions:input_type -> jnoj.interface.v1.ListSubmissionsRequest
	3, // 1: jnoj.interface.v1.SubmissionService.GetSubmission:input_type -> jnoj.interface.v1.GetSubmissionRequest
	4, // 2: jnoj.interface.v1.SubmissionService.CreateSubmission:input_type -> jnoj.interface.v1.CreateSubmissionRequest
	2, // 3: jnoj.interface.v1.SubmissionService.ListSubmissions:output_type -> jnoj.interface.v1.ListSubmissionsResponse
	0, // 4: jnoj.interface.v1.SubmissionService.GetSubmission:output_type -> jnoj.interface.v1.Submission
	0, // 5: jnoj.interface.v1.SubmissionService.CreateSubmission:output_type -> jnoj.interface.v1.Submission
	3, // [3:6] is the sub-list for method output_type
	0, // [0:3] is the sub-list for method input_type
	0, // [0:0] is the sub-list for extension type_name
	0, // [0:0] is the sub-list for extension extendee
	0, // [0:0] is the sub-list for field type_name
}

func init() { file_v1_submission_proto_init() }
func file_v1_submission_proto_init() {
	if File_v1_submission_proto != nil {
		return
	}
	if !protoimpl.UnsafeEnabled {
		file_v1_submission_proto_msgTypes[0].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*Submission); i {
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
		file_v1_submission_proto_msgTypes[1].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*ListSubmissionsRequest); i {
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
		file_v1_submission_proto_msgTypes[2].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*ListSubmissionsResponse); i {
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
		file_v1_submission_proto_msgTypes[3].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*GetSubmissionRequest); i {
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
		file_v1_submission_proto_msgTypes[4].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*CreateSubmissionRequest); i {
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
			RawDescriptor: file_v1_submission_proto_rawDesc,
			NumEnums:      0,
			NumMessages:   5,
			NumExtensions: 0,
			NumServices:   1,
		},
		GoTypes:           file_v1_submission_proto_goTypes,
		DependencyIndexes: file_v1_submission_proto_depIdxs,
		MessageInfos:      file_v1_submission_proto_msgTypes,
	}.Build()
	File_v1_submission_proto = out.File
	file_v1_submission_proto_rawDesc = nil
	file_v1_submission_proto_goTypes = nil
	file_v1_submission_proto_depIdxs = nil
}
