package service

import (
	"context"
	v1 "jnoj/api/sandbox/v1"
	"jnoj/app/sandbox/internal/biz"

	"github.com/shirou/gopsutil/v3/cpu"
	"github.com/shirou/gopsutil/v3/disk"
	"github.com/shirou/gopsutil/v3/host"
	"github.com/shirou/gopsutil/v3/mem"
)

// SandboxService is a sandbox service.
type SandboxService struct {
	v1.UnimplementedSandboxServiceServer

	uc  *biz.SandboxUsecase
	suc *biz.SubmissionUsecase
}

// NewSandboxService new a sandbox service.
func NewSandboxService(uc *biz.SandboxUsecase, suc *biz.SubmissionUsecase) *SandboxService {
	return &SandboxService{uc: uc, suc: suc}
}

func (s *SandboxService) Run(ctx context.Context, req *v1.RunRequest) (*v1.RunResponse, error) {
	res := s.uc.Run(ctx, req)
	return res, nil
}

type GetSystemInfoResponse_Memory_VirtualMemoryStat struct {
	Total          uint64  `protobuf:"varint,1,opt,name=total,proto3" json:"total,omitempty"`
	Available      uint64  `protobuf:"varint,2,opt,name=available,proto3" json:"available,omitempty"`
	Used           uint64  `protobuf:"varint,3,opt,name=used,proto3" json:"used,omitempty"`
	UsedPercent    float64 `protobuf:"fixed64,4,opt,name=used_percent,json=usedPercent,proto3" json:"used_percent,omitempty"`
	Free           uint64  `protobuf:"varint,5,opt,name=free,proto3" json:"free,omitempty"`
	Active         uint64  `protobuf:"varint,6,opt,name=active,proto3" json:"active,omitempty"`
	Inactive       uint64  `protobuf:"varint,7,opt,name=inactive,proto3" json:"inactive,omitempty"`
	Wired          uint64  `protobuf:"varint,8,opt,name=wired,proto3" json:"wired,omitempty"`
	Laundry        uint64  `protobuf:"varint,9,opt,name=laundry,proto3" json:"laundry,omitempty"`
	Buffers        uint64  `protobuf:"varint,10,opt,name=buffers,proto3" json:"buffers,omitempty"`
	Cached         uint64  `protobuf:"varint,11,opt,name=cached,proto3" json:"cached,omitempty"`
	WriteBack      uint64  `protobuf:"varint,12,opt,name=write_back,json=writeBack,proto3" json:"write_back,omitempty"`
	Dirty          uint64  `protobuf:"varint,13,opt,name=dirty,proto3" json:"dirty,omitempty"`
	WriteBackTmp   uint64  `protobuf:"varint,14,opt,name=write_back_tmp,json=writeBackTmp,proto3" json:"write_back_tmp,omitempty"`
	Shared         uint64  `protobuf:"varint,15,opt,name=shared,proto3" json:"shared,omitempty"`
	Slab           uint64  `protobuf:"varint,16,opt,name=slab,proto3" json:"slab,omitempty"`
	Sreclaimable   uint64  `protobuf:"varint,17,opt,name=sreclaimable,proto3" json:"sreclaimable,omitempty"`
	Sunreclaim     uint64  `protobuf:"varint,18,opt,name=sunreclaim,proto3" json:"sunreclaim,omitempty"`
	PageTables     uint64  `protobuf:"varint,19,opt,name=page_tables,json=pageTables,proto3" json:"page_tables,omitempty"`
	SwapCached     uint64  `protobuf:"varint,20,opt,name=swap_cached,json=swapCached,proto3" json:"swap_cached,omitempty"`
	CommitLimit    uint64  `protobuf:"varint,21,opt,name=commit_limit,json=commitLimit,proto3" json:"commit_limit,omitempty"`
	CommittedAs    uint64  `protobuf:"varint,22,opt,name=committed_as,json=committedAs,proto3" json:"committed_as,omitempty"`
	HighTotal      uint64  `protobuf:"varint,23,opt,name=high_total,json=highTotal,proto3" json:"high_total,omitempty"`
	HighFree       uint64  `protobuf:"varint,24,opt,name=high_free,json=highFree,proto3" json:"high_free,omitempty"`
	LowTotal       uint64  `protobuf:"varint,25,opt,name=low_total,json=lowTotal,proto3" json:"low_total,omitempty"`
	LowFree        uint64  `protobuf:"varint,26,opt,name=low_free,json=lowFree,proto3" json:"low_free,omitempty"`
	SwapTotal      uint64  `protobuf:"varint,27,opt,name=swap_total,json=swapTotal,proto3" json:"swap_total,omitempty"`
	SwapFree       uint64  `protobuf:"varint,28,opt,name=swap_free,json=swapFree,proto3" json:"swap_free,omitempty"`
	Mapped         uint64  `protobuf:"varint,29,opt,name=mapped,proto3" json:"mapped,omitempty"`
	VmallocTotal   uint64  `protobuf:"varint,30,opt,name=vmalloc_total,json=vmallocTotal,proto3" json:"vmalloc_total,omitempty"`
	VmallocUsed    uint64  `protobuf:"varint,31,opt,name=vmalloc_used,json=vmallocUsed,proto3" json:"vmalloc_used,omitempty"`
	VmallocChunk   uint64  `protobuf:"varint,32,opt,name=vmalloc_chunk,json=vmallocChunk,proto3" json:"vmalloc_chunk,omitempty"`
	HugePagesTotal uint64  `protobuf:"varint,33,opt,name=huge_pages_total,json=hugePagesTotal,proto3" json:"huge_pages_total,omitempty"`
	HugePagesFree  uint64  `protobuf:"varint,34,opt,name=huge_pages_free,json=hugePagesFree,proto3" json:"huge_pages_free,omitempty"`
	HugePagesRsvd  uint64  `protobuf:"varint,35,opt,name=huge_pages_rsvd,json=hugePagesRsvd,proto3" json:"huge_pages_rsvd,omitempty"`
	HugePagesSurp  uint64  `protobuf:"varint,36,opt,name=huge_pages_surp,json=hugePagesSurp,proto3" json:"huge_pages_surp,omitempty"`
	HugePageSize   uint64  `protobuf:"varint,37,opt,name=huge_page_size,json=hugePageSize,proto3" json:"huge_page_size,omitempty"`
}

func (s *SandboxService) GetSystemInfo(ctx context.Context, req *v1.GetSystemInfoRequest) (*v1.GetSystemInfoResponse, error) {
	resp := &v1.GetSystemInfoResponse{
		Host:   &v1.GetSystemInfoResponse_Host{},
		Cpu:    &v1.GetSystemInfoResponse_Cpu{},
		Memory: &v1.GetSystemInfoResponse_Memory{},
		Disk:   &v1.GetSystemInfoResponse_Disk{},
	}
	if h, err := host.InfoWithContext(ctx); err == nil {
		resp.Host.InfoStat = &v1.GetSystemInfoResponse_Host_InfoStat{
			Hostname:             h.Hostname,
			Uptime:               h.Uptime,
			BootTime:             h.BootTime,
			Procs:                h.Procs,
			Os:                   h.OS,
			Platform:             h.Platform,
			PlatformFamily:       h.PlatformFamily,
			PlatformVersion:      h.PlatformVersion,
			KernelVersion:        h.KernelVersion,
			KernelArch:           h.KernelArch,
			VirtualizationSystem: h.VirtualizationSystem,
			VirtualizationRole:   h.VirtualizationRole,
		}
	}
	if memory, err := mem.SwapDevicesWithContext(ctx); err == nil {
		for _, v := range memory {
			resp.Memory.SwapDevice = append(resp.Memory.SwapDevice, &v1.GetSystemInfoResponse_Memory_SwapDevice{
				Name:      v.Name,
				UsedBytes: v.UsedBytes,
				FreeBytes: v.FreeBytes,
			})
		}
	}
	if memory, err := mem.VirtualMemoryWithContext(ctx); err == nil {
		resp.Memory.VirtualMemory = &v1.GetSystemInfoResponse_Memory_VirtualMemoryStat{
			Total:       memory.Total,
			Available:   memory.Available,
			Used:        memory.Used,
			UsedPercent: memory.UsedPercent,
			Free:        memory.Free,
			Active:      memory.Active,
			Inactive:    memory.Inactive,
			SwapTotal:   memory.SwapTotal,
			SwapFree:    memory.SwapFree,
		}
	}
	if c, err := cpu.CountsWithContext(ctx, true); err == nil {
		resp.Cpu.Counts = int32(c)
	}
	if cpuInfo, err := cpu.InfoWithContext(ctx); err == nil {
		for _, v := range cpuInfo {
			resp.Cpu.InfoStat = append(resp.Cpu.InfoStat, &v1.GetSystemInfoResponse_Cpu_InfoStat{
				Cpu:        v.CPU,
				VendorId:   v.VendorID,
				Family:     v.Family,
				Model:      v.Model,
				Stepping:   v.Stepping,
				PhysicalId: v.PhysicalID,
				CoreId:     v.CoreID,
				Cores:      v.Cores,
				ModelName:  v.ModelName,
				Mhz:        v.Mhz,
				CacheSize:  v.CacheSize,
				Microcode:  v.Microcode,
			})
		}
	}
	if usage, err := disk.UsageWithContext(ctx, "/"); err == nil {
		resp.Disk.UsageStat = &v1.GetSystemInfoResponse_Disk_UsageStat{
			Path:              usage.Path,
			Fst:               usage.Fstype,
			Total:             usage.Total,
			Free:              usage.Free,
			Used:              usage.Used,
			UsedPercent:       usage.UsedPercent,
			InodesTotal:       usage.InodesTotal,
			InodesUsed:        usage.InodesUsed,
			InodesFree:        usage.InodesFree,
			InodesUsedPercent: usage.InodesUsedPercent,
		}
	}
	return resp, nil
}
