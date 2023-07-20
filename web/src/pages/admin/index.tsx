import React, { useEffect, useState } from 'react';
import Layout from './Layout';
import { Avatar, Card, Divider, List, Space } from '@arco-design/web-react';
import { listServiceStatuses } from '@/api/admin/admin';
import { FormatStorageSize } from '@/utils/format';

function Index() {
  const [sanboxSystemInfo, setSanboxSystemInfo] = useState([]);
  let timer = null;
  useEffect(() => {
    timer = setInterval(() => {
      listServiceStatuses().then(res => {
        setSanboxSystemInfo(res.data.sanboxSystemInfo);
      });
    }, 1000);
    return () => {
      clearInterval(timer);
    };
  }, []);
  return (
    <Card>
      <List
        style={{ width: 622 }}
        size='small'
        header='测评进程系统信息'
        dataSource={sanboxSystemInfo}
        render={(item, index) => (
          <List.Item key={index}>
            <Space>
              <Avatar>{index+1}</Avatar>
              <Space direction='vertical'>
                <Space>
                  节点：{item.endpoint}
                </Space>
                <Space split={<Divider type='vertical' />}>
                  <span>系统</span>
                  <span>{item.host.infoStat.platform}</span>
                  <span>{item.host.infoStat.platformVersion}</span>
                  <span>{item.host.infoStat.kernelArch}</span>
                </Space>
                <Space split={<Divider type='vertical' />}>
                  <span>CPU</span>
                  <span>核心数: {item.cpu.counts}</span>
                  <span>型号：{item.cpu.infoStat.length > 0 && (item.cpu.infoStat[0].modelName)}</span>
                </Space>
                <Space split={<Divider type='vertical' />}>
                  <span>内存</span> 
                  <span>总大小: {FormatStorageSize(item.memory.virtualMemory.total)}</span> 
                  <span>已使用：{FormatStorageSize(item.memory.virtualMemory.used)}</span> 
                  <span>使用率：{Math.round(item.memory.virtualMemory.usedPercent)}%</span>
                </Space>
                <Space split={<Divider type='vertical' />}>
                  <span>硬盘</span> 
                  <span>总大小: {FormatStorageSize(item.disk.usageStat.total)}</span> 
                  <span>已使用：{FormatStorageSize(item.disk.usageStat.used)}</span> 
                  <span>剩余空间：{FormatStorageSize(item.disk.usageStat.free)}</span>
                </Space>
              </Space>
            </Space>
          </List.Item>
        )}
      />
    </Card>
  );
};

Index.getLayout = Layout;
export default Index;
