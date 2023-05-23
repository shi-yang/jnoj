// Code generated by protoc-gen-go. DO NOT EDIT.
// versions:
// 	protoc-gen-go v1.28.1
// 	protoc        v3.21.12
// source: v1/common_error.proto

package v1

import (
	_ "github.com/go-kratos/kratos/v2/errors"
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

type CommonErrorReason int32

const (
	CommonErrorReason_BAD_REQUEST  CommonErrorReason = 0
	CommonErrorReason_UNAUTHORIZED CommonErrorReason = 1
	CommonErrorReason_FORBIDDEN    CommonErrorReason = 3
	CommonErrorReason_NOT_FOUND    CommonErrorReason = 4
)

// Enum value maps for CommonErrorReason.
var (
	CommonErrorReason_name = map[int32]string{
		0: "BAD_REQUEST",
		1: "UNAUTHORIZED",
		3: "FORBIDDEN",
		4: "NOT_FOUND",
	}
	CommonErrorReason_value = map[string]int32{
		"BAD_REQUEST":  0,
		"UNAUTHORIZED": 1,
		"FORBIDDEN":    3,
		"NOT_FOUND":    4,
	}
)

func (x CommonErrorReason) Enum() *CommonErrorReason {
	p := new(CommonErrorReason)
	*p = x
	return p
}

func (x CommonErrorReason) String() string {
	return protoimpl.X.EnumStringOf(x.Descriptor(), protoreflect.EnumNumber(x))
}

func (CommonErrorReason) Descriptor() protoreflect.EnumDescriptor {
	return file_v1_common_error_proto_enumTypes[0].Descriptor()
}

func (CommonErrorReason) Type() protoreflect.EnumType {
	return &file_v1_common_error_proto_enumTypes[0]
}

func (x CommonErrorReason) Number() protoreflect.EnumNumber {
	return protoreflect.EnumNumber(x)
}

// Deprecated: Use CommonErrorReason.Descriptor instead.
func (CommonErrorReason) EnumDescriptor() ([]byte, []int) {
	return file_v1_common_error_proto_rawDescGZIP(), []int{0}
}

var File_v1_common_error_proto protoreflect.FileDescriptor

var file_v1_common_error_proto_rawDesc = []byte{
	0x0a, 0x15, 0x76, 0x31, 0x2f, 0x63, 0x6f, 0x6d, 0x6d, 0x6f, 0x6e, 0x5f, 0x65, 0x72, 0x72, 0x6f,
	0x72, 0x2e, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x12, 0x0d, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x61, 0x64,
	0x6d, 0x69, 0x6e, 0x2e, 0x76, 0x31, 0x1a, 0x13, 0x65, 0x72, 0x72, 0x6f, 0x72, 0x73, 0x2f, 0x65,
	0x72, 0x72, 0x6f, 0x72, 0x73, 0x2e, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x2a, 0x72, 0x0a, 0x11, 0x43,
	0x6f, 0x6d, 0x6d, 0x6f, 0x6e, 0x45, 0x72, 0x72, 0x6f, 0x72, 0x52, 0x65, 0x61, 0x73, 0x6f, 0x6e,
	0x12, 0x15, 0x0a, 0x0b, 0x42, 0x41, 0x44, 0x5f, 0x52, 0x45, 0x51, 0x55, 0x45, 0x53, 0x54, 0x10,
	0x00, 0x1a, 0x04, 0xa8, 0x45, 0x90, 0x03, 0x12, 0x16, 0x0a, 0x0c, 0x55, 0x4e, 0x41, 0x55, 0x54,
	0x48, 0x4f, 0x52, 0x49, 0x5a, 0x45, 0x44, 0x10, 0x01, 0x1a, 0x04, 0xa8, 0x45, 0x91, 0x03, 0x12,
	0x13, 0x0a, 0x09, 0x46, 0x4f, 0x52, 0x42, 0x49, 0x44, 0x44, 0x45, 0x4e, 0x10, 0x03, 0x1a, 0x04,
	0xa8, 0x45, 0x93, 0x03, 0x12, 0x13, 0x0a, 0x09, 0x4e, 0x4f, 0x54, 0x5f, 0x46, 0x4f, 0x55, 0x4e,
	0x44, 0x10, 0x04, 0x1a, 0x04, 0xa8, 0x45, 0x94, 0x03, 0x1a, 0x04, 0xa0, 0x45, 0xf4, 0x03, 0x42,
	0x11, 0x5a, 0x0f, 0x61, 0x70, 0x69, 0x2f, 0x61, 0x64, 0x6d, 0x69, 0x6e, 0x2f, 0x76, 0x31, 0x3b,
	0x76, 0x31, 0x62, 0x06, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x33,
}

var (
	file_v1_common_error_proto_rawDescOnce sync.Once
	file_v1_common_error_proto_rawDescData = file_v1_common_error_proto_rawDesc
)

func file_v1_common_error_proto_rawDescGZIP() []byte {
	file_v1_common_error_proto_rawDescOnce.Do(func() {
		file_v1_common_error_proto_rawDescData = protoimpl.X.CompressGZIP(file_v1_common_error_proto_rawDescData)
	})
	return file_v1_common_error_proto_rawDescData
}

var file_v1_common_error_proto_enumTypes = make([]protoimpl.EnumInfo, 1)
var file_v1_common_error_proto_goTypes = []interface{}{
	(CommonErrorReason)(0), // 0: jnoj.admin.v1.CommonErrorReason
}
var file_v1_common_error_proto_depIdxs = []int32{
	0, // [0:0] is the sub-list for method output_type
	0, // [0:0] is the sub-list for method input_type
	0, // [0:0] is the sub-list for extension type_name
	0, // [0:0] is the sub-list for extension extendee
	0, // [0:0] is the sub-list for field type_name
}

func init() { file_v1_common_error_proto_init() }
func file_v1_common_error_proto_init() {
	if File_v1_common_error_proto != nil {
		return
	}
	type x struct{}
	out := protoimpl.TypeBuilder{
		File: protoimpl.DescBuilder{
			GoPackagePath: reflect.TypeOf(x{}).PkgPath(),
			RawDescriptor: file_v1_common_error_proto_rawDesc,
			NumEnums:      1,
			NumMessages:   0,
			NumExtensions: 0,
			NumServices:   0,
		},
		GoTypes:           file_v1_common_error_proto_goTypes,
		DependencyIndexes: file_v1_common_error_proto_depIdxs,
		EnumInfos:         file_v1_common_error_proto_enumTypes,
	}.Build()
	File_v1_common_error_proto = out.File
	file_v1_common_error_proto_rawDesc = nil
	file_v1_common_error_proto_goTypes = nil
	file_v1_common_error_proto_depIdxs = nil
}
