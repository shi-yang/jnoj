// Code generated by protoc-gen-go. DO NOT EDIT.
// versions:
// 	protoc-gen-go v1.28.1
// 	protoc        v3.19.4
// source: websocket.proto

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

type Message_Type int32

const (
	Message_SUBMISSION_RESULT Message_Type = 0
)

// Enum value maps for Message_Type.
var (
	Message_Type_name = map[int32]string{
		0: "SUBMISSION_RESULT",
	}
	Message_Type_value = map[string]int32{
		"SUBMISSION_RESULT": 0,
	}
)

func (x Message_Type) Enum() *Message_Type {
	p := new(Message_Type)
	*p = x
	return p
}

func (x Message_Type) String() string {
	return protoimpl.X.EnumStringOf(x.Descriptor(), protoreflect.EnumNumber(x))
}

func (Message_Type) Descriptor() protoreflect.EnumDescriptor {
	return file_websocket_proto_enumTypes[0].Descriptor()
}

func (Message_Type) Type() protoreflect.EnumType {
	return &file_websocket_proto_enumTypes[0]
}

func (x Message_Type) Number() protoreflect.EnumNumber {
	return protoreflect.EnumNumber(x)
}

// Deprecated: Use Message_Type.Descriptor instead.
func (Message_Type) EnumDescriptor() ([]byte, []int) {
	return file_websocket_proto_rawDescGZIP(), []int{0, 0}
}

// 给前端的消息
type Message struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Type    Message_Type      `protobuf:"varint,1,opt,name=type,proto3,enum=jnoj.api.queue.v1.Message_Type" json:"type,omitempty"`
	UserId  int32             `protobuf:"varint,2,opt,name=user_id,json=userId,proto3" json:"user_id,omitempty"`
	Message map[string]string `protobuf:"bytes,3,rep,name=message,proto3" json:"message,omitempty" protobuf_key:"bytes,1,opt,name=key,proto3" protobuf_val:"bytes,2,opt,name=value,proto3"`
}

func (x *Message) Reset() {
	*x = Message{}
	if protoimpl.UnsafeEnabled {
		mi := &file_websocket_proto_msgTypes[0]
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		ms.StoreMessageInfo(mi)
	}
}

func (x *Message) String() string {
	return protoimpl.X.MessageStringOf(x)
}

func (*Message) ProtoMessage() {}

func (x *Message) ProtoReflect() protoreflect.Message {
	mi := &file_websocket_proto_msgTypes[0]
	if protoimpl.UnsafeEnabled && x != nil {
		ms := protoimpl.X.MessageStateOf(protoimpl.Pointer(x))
		if ms.LoadMessageInfo() == nil {
			ms.StoreMessageInfo(mi)
		}
		return ms
	}
	return mi.MessageOf(x)
}

// Deprecated: Use Message.ProtoReflect.Descriptor instead.
func (*Message) Descriptor() ([]byte, []int) {
	return file_websocket_proto_rawDescGZIP(), []int{0}
}

func (x *Message) GetType() Message_Type {
	if x != nil {
		return x.Type
	}
	return Message_SUBMISSION_RESULT
}

func (x *Message) GetUserId() int32 {
	if x != nil {
		return x.UserId
	}
	return 0
}

func (x *Message) GetMessage() map[string]string {
	if x != nil {
		return x.Message
	}
	return nil
}

var File_websocket_proto protoreflect.FileDescriptor

var file_websocket_proto_rawDesc = []byte{
	0x0a, 0x0f, 0x77, 0x65, 0x62, 0x73, 0x6f, 0x63, 0x6b, 0x65, 0x74, 0x2e, 0x70, 0x72, 0x6f, 0x74,
	0x6f, 0x12, 0x11, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x61, 0x70, 0x69, 0x2e, 0x71, 0x75, 0x65, 0x75,
	0x65, 0x2e, 0x76, 0x31, 0x22, 0xf5, 0x01, 0x0a, 0x07, 0x4d, 0x65, 0x73, 0x73, 0x61, 0x67, 0x65,
	0x12, 0x33, 0x0a, 0x04, 0x74, 0x79, 0x70, 0x65, 0x18, 0x01, 0x20, 0x01, 0x28, 0x0e, 0x32, 0x1f,
	0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x61, 0x70, 0x69, 0x2e, 0x71, 0x75, 0x65, 0x75, 0x65, 0x2e,
	0x76, 0x31, 0x2e, 0x4d, 0x65, 0x73, 0x73, 0x61, 0x67, 0x65, 0x2e, 0x54, 0x79, 0x70, 0x65, 0x52,
	0x04, 0x74, 0x79, 0x70, 0x65, 0x12, 0x17, 0x0a, 0x07, 0x75, 0x73, 0x65, 0x72, 0x5f, 0x69, 0x64,
	0x18, 0x02, 0x20, 0x01, 0x28, 0x05, 0x52, 0x06, 0x75, 0x73, 0x65, 0x72, 0x49, 0x64, 0x12, 0x41,
	0x0a, 0x07, 0x6d, 0x65, 0x73, 0x73, 0x61, 0x67, 0x65, 0x18, 0x03, 0x20, 0x03, 0x28, 0x0b, 0x32,
	0x27, 0x2e, 0x6a, 0x6e, 0x6f, 0x6a, 0x2e, 0x61, 0x70, 0x69, 0x2e, 0x71, 0x75, 0x65, 0x75, 0x65,
	0x2e, 0x76, 0x31, 0x2e, 0x4d, 0x65, 0x73, 0x73, 0x61, 0x67, 0x65, 0x2e, 0x4d, 0x65, 0x73, 0x73,
	0x61, 0x67, 0x65, 0x45, 0x6e, 0x74, 0x72, 0x79, 0x52, 0x07, 0x6d, 0x65, 0x73, 0x73, 0x61, 0x67,
	0x65, 0x1a, 0x3a, 0x0a, 0x0c, 0x4d, 0x65, 0x73, 0x73, 0x61, 0x67, 0x65, 0x45, 0x6e, 0x74, 0x72,
	0x79, 0x12, 0x10, 0x0a, 0x03, 0x6b, 0x65, 0x79, 0x18, 0x01, 0x20, 0x01, 0x28, 0x09, 0x52, 0x03,
	0x6b, 0x65, 0x79, 0x12, 0x14, 0x0a, 0x05, 0x76, 0x61, 0x6c, 0x75, 0x65, 0x18, 0x02, 0x20, 0x01,
	0x28, 0x09, 0x52, 0x05, 0x76, 0x61, 0x6c, 0x75, 0x65, 0x3a, 0x02, 0x38, 0x01, 0x22, 0x1d, 0x0a,
	0x04, 0x54, 0x79, 0x70, 0x65, 0x12, 0x15, 0x0a, 0x11, 0x53, 0x55, 0x42, 0x4d, 0x49, 0x53, 0x53,
	0x49, 0x4f, 0x4e, 0x5f, 0x52, 0x45, 0x53, 0x55, 0x4c, 0x54, 0x10, 0x00, 0x42, 0x16, 0x5a, 0x14,
	0x6a, 0x6e, 0x6f, 0x6a, 0x2f, 0x61, 0x70, 0x69, 0x2f, 0x71, 0x75, 0x65, 0x75, 0x65, 0x2f, 0x76,
	0x31, 0x3b, 0x76, 0x31, 0x62, 0x06, 0x70, 0x72, 0x6f, 0x74, 0x6f, 0x33,
}

var (
	file_websocket_proto_rawDescOnce sync.Once
	file_websocket_proto_rawDescData = file_websocket_proto_rawDesc
)

func file_websocket_proto_rawDescGZIP() []byte {
	file_websocket_proto_rawDescOnce.Do(func() {
		file_websocket_proto_rawDescData = protoimpl.X.CompressGZIP(file_websocket_proto_rawDescData)
	})
	return file_websocket_proto_rawDescData
}

var file_websocket_proto_enumTypes = make([]protoimpl.EnumInfo, 1)
var file_websocket_proto_msgTypes = make([]protoimpl.MessageInfo, 2)
var file_websocket_proto_goTypes = []interface{}{
	(Message_Type)(0), // 0: jnoj.api.queue.v1.Message.Type
	(*Message)(nil),   // 1: jnoj.api.queue.v1.Message
	nil,               // 2: jnoj.api.queue.v1.Message.MessageEntry
}
var file_websocket_proto_depIdxs = []int32{
	0, // 0: jnoj.api.queue.v1.Message.type:type_name -> jnoj.api.queue.v1.Message.Type
	2, // 1: jnoj.api.queue.v1.Message.message:type_name -> jnoj.api.queue.v1.Message.MessageEntry
	2, // [2:2] is the sub-list for method output_type
	2, // [2:2] is the sub-list for method input_type
	2, // [2:2] is the sub-list for extension type_name
	2, // [2:2] is the sub-list for extension extendee
	0, // [0:2] is the sub-list for field type_name
}

func init() { file_websocket_proto_init() }
func file_websocket_proto_init() {
	if File_websocket_proto != nil {
		return
	}
	if !protoimpl.UnsafeEnabled {
		file_websocket_proto_msgTypes[0].Exporter = func(v interface{}, i int) interface{} {
			switch v := v.(*Message); i {
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
			RawDescriptor: file_websocket_proto_rawDesc,
			NumEnums:      1,
			NumMessages:   2,
			NumExtensions: 0,
			NumServices:   0,
		},
		GoTypes:           file_websocket_proto_goTypes,
		DependencyIndexes: file_websocket_proto_depIdxs,
		EnumInfos:         file_websocket_proto_enumTypes,
		MessageInfos:      file_websocket_proto_msgTypes,
	}.Build()
	File_websocket_proto = out.File
	file_websocket_proto_rawDesc = nil
	file_websocket_proto_goTypes = nil
	file_websocket_proto_depIdxs = nil
}