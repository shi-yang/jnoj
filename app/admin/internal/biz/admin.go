package biz

import (
	"context"
	v1 "jnoj/api/admin/v1"
	sandboxV1 "jnoj/api/sandbox/v1"
	"jnoj/app/admin/internal/conf"
	"strings"
	"time"

	"github.com/go-kratos/kratos/v2/log"
	"github.com/go-kratos/kratos/v2/middleware/recovery"
	"github.com/go-kratos/kratos/v2/registry"
	"github.com/go-kratos/kratos/v2/transport/grpc"
)

// AdminRepo is a Admin repo.
type AdminRepo interface {
}

// AdminUsecase is a Admin usecase.
type AdminUsecase struct {
	log       *log.Helper
	discovery registry.Discovery
	conf      *conf.Registry
}

// NewAdminUsecase new a Admin usecase.
func NewAdminUsecase(
	logger log.Logger,
	discovery registry.Discovery,
	conf *conf.Registry,
) *AdminUsecase {
	return &AdminUsecase{
		discovery: discovery,
		log:       log.NewHelper(logger),
	}
}

func (uc *AdminUsecase) ListServiceStatuses(ctx context.Context) *v1.ListServiceStatusesResponse {
	var res = new(v1.ListServiceStatusesResponse)
	services, _ := uc.discovery.GetService(ctx, "jnoj.sandbox.service")
	for _, service := range services {
		if len(service.Endpoints) == 0 {
			continue
		}
		conn, err := grpc.DialInsecure(
			context.Background(),
			grpc.WithEndpoint(strings.Split(service.Endpoints[0], "//")[1]),
			grpc.WithMiddleware(
				recovery.Recovery(),
			),
			grpc.WithTimeout(time.Second*60),
		)
		if err != nil {
			panic(err)
		}
		c := sandboxV1.NewSandboxServiceClient(conn)
		resp, err := c.GetSystemInfo(ctx, &sandboxV1.GetSystemInfoRequest{})
		if err != nil {
			continue
		}
		info := &v1.ListServiceStatusesResponse_SanboxSystemInfo{
			Endpoint: service.Endpoints[0],
		}
		if resp.Host != nil {
			info.Host = &v1.ListServiceStatusesResponse_SanboxSystemInfo_Host{}
			info.Host.InfoStat = &v1.ListServiceStatusesResponse_SanboxSystemInfo_Host_InfoStat{
				Hostname:             resp.Host.InfoStat.Hostname,
				Uptime:               resp.Host.InfoStat.Uptime,
				BootTime:             resp.Host.InfoStat.BootTime,
				Procs:                resp.Host.InfoStat.Procs,
				Os:                   resp.Host.InfoStat.Os,
				Platform:             resp.Host.InfoStat.Platform,
				PlatformFamily:       resp.Host.InfoStat.PlatformFamily,
				PlatformVersion:      resp.Host.InfoStat.PlatformVersion,
				KernelVersion:        resp.Host.InfoStat.KernelVersion,
				KernelArch:           resp.Host.InfoStat.KernelArch,
				VirtualizationSystem: resp.Host.InfoStat.VirtualizationSystem,
				VirtualizationRole:   resp.Host.InfoStat.VirtualizationRole,
			}
		}
		if resp.Cpu != nil {
			info.Cpu = &v1.ListServiceStatusesResponse_SanboxSystemInfo_Cpu{}
			info.Cpu.Counts = resp.Cpu.Counts
			info.Cpu.Percent = resp.Cpu.Percent
			for _, v := range resp.Cpu.InfoStat {
				info.Cpu.InfoStat = append(info.Cpu.InfoStat, &v1.ListServiceStatusesResponse_SanboxSystemInfo_Cpu_InfoStat{
					Cpu:        v.Cpu,
					VendorId:   v.VendorId,
					Family:     v.Family,
					Model:      v.Model,
					Stepping:   v.Stepping,
					PhysicalId: v.PhysicalId,
					CoreId:     v.CoreId,
					Cores:      v.Cores,
					ModelName:  v.ModelName,
					Mhz:        v.Mhz,
					CacheSize:  v.CacheSize,
					Microcode:  v.Microcode,
				})
			}
		}
		if resp.Memory != nil {
			info.Memory = &v1.ListServiceStatusesResponse_SanboxSystemInfo_Memory{}
			for _, v := range resp.Memory.SwapDevice {
				info.Memory.SwapDevice = append(info.Memory.SwapDevice, &v1.ListServiceStatusesResponse_SanboxSystemInfo_Memory_SwapDevice{
					Name:      v.Name,
					UsedBytes: v.UsedBytes,
					FreeBytes: v.FreeBytes,
				})
			}
			if resp.Memory.VirtualMemory != nil {
				info.Memory.VirtualMemory = &v1.ListServiceStatusesResponse_SanboxSystemInfo_Memory_VirtualMemoryStat{
					Total:       resp.Memory.VirtualMemory.Total,
					Available:   resp.Memory.VirtualMemory.Available,
					Used:        resp.Memory.VirtualMemory.Used,
					UsedPercent: resp.Memory.VirtualMemory.UsedPercent,
					Free:        resp.Memory.VirtualMemory.Free,
					Active:      resp.Memory.VirtualMemory.Active,
					Inactive:    resp.Memory.VirtualMemory.Inactive,
					SwapTotal:   resp.Memory.VirtualMemory.SwapTotal,
					SwapFree:    resp.Memory.VirtualMemory.SwapFree,
				}
			}
		}
		if resp.Disk != nil {
			info.Disk = &v1.ListServiceStatusesResponse_SanboxSystemInfo_Disk{}
			info.Disk.UsageStat = &v1.ListServiceStatusesResponse_SanboxSystemInfo_Disk_UsageStat{
				Path:              resp.Disk.UsageStat.Path,
				Fst:               resp.Disk.UsageStat.Fst,
				Total:             resp.Disk.UsageStat.Total,
				Free:              resp.Disk.UsageStat.Free,
				Used:              resp.Disk.UsageStat.Used,
				UsedPercent:       resp.Disk.UsageStat.UsedPercent,
				InodesTotal:       resp.Disk.UsageStat.InodesTotal,
				InodesUsed:        resp.Disk.UsageStat.InodesUsed,
				InodesFree:        resp.Disk.UsageStat.InodesFree,
				InodesUsedPercent: resp.Disk.UsageStat.InodesUsedPercent,
			}
		}
		res.SanboxSystemInfo = append(res.SanboxSystemInfo, info)
	}
	return res
}
