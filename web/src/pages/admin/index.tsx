import React, { useEffect, useState } from 'react';
import Layout from './Layout';
import { Avatar, Card, Divider, List, Space, Statistic } from '@arco-design/web-react';
import { analyticsUserActivities, listServiceStatuses } from '@/api/admin/admin';
import { FormatStorageSize } from '@/utils/format';
import { BarChart, Bar, Rectangle, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const data = [
  {
    name: 'Page A',
    uv: 4000,
    pv: 2400,
    amt: 2400,
  },
  {
    name: 'Page B',
    uv: 3000,
    pv: 1398,
    amt: 2210,
  },
  {
    name: 'Page C',
    uv: 2000,
    pv: 9800,
    amt: 2290,
  },
  {
    name: 'Page D',
    uv: 2780,
    pv: 3908,
    amt: 2000,
  },
  {
    name: 'Page E',
    uv: 1890,
    pv: 4800,
    amt: 2181,
  },
  {
    name: 'Page F',
    uv: 2390,
    pv: 3800,
    amt: 2500,
  },
  {
    name: 'Page G',
    uv: 3490,
    pv: 4300,
    amt: 2100,
  },
];

function isWithinDays(dateStr, daysRange) {
  const givenDate = new Date(dateStr);
  const currentDate = new Date();

  // 计算时间差异（毫秒）
  const diff = currentDate.getTime() - givenDate.getTime();

  // 将差异转换为天数
  const days = diff / (1000 * 60 * 60 * 24);

  // 检查是否在指定天数内
  return days <= daysRange;
}

function Index() {
  const [sanboxSystemInfo, setSanboxSystemInfo] = useState([]);
  const [isMounted, setIsMounted] = useState(false);
  const [userActivities, setUserActivities] = useState({} as any);
  const [userCount, setUserCount] = useState({} as any);
  const [submissionCount, setSubmissionCount] = useState({} as any);
  let timer = null;
  useEffect(() => {
    setIsMounted(true);
    analyticsUserActivities().then(res => {
      setUserActivities(res.data);
      const u = {
        today: 0, day7: 0, day30: 0,
      };
      for (let i = 0; i < res.data.userCount.length; i++) {
        if (isWithinDays(res.data.userCount[i].date, 0)) {
          u.today += res.data.userCount[i].count;
        }
        if (isWithinDays(res.data.userCount[i].date, 7)) {
          u.day7 += res.data.userCount[i].count;
        }
        if (isWithinDays(res.data.userCount[i].date, 30)) {
          u.day30 += res.data.userCount[i].count;
        }
      }
      setUserCount(u);
      const s = {
        today: 0, day7: 0, day30: 0,
      };
      for (let i = 0; i < res.data.submissionCount.length; i++) {
        if (isWithinDays(res.data.submissionCount[i].date, 0)) {
          s.today += res.data.submissionCount[i].count;
        }
        if (isWithinDays(res.data.submissionCount[i].date, 7)) {
          s.day7 += res.data.submissionCount[i].count;
        }
        if (isWithinDays(res.data.submissionCount[i].date, 30)) {
          s.day30 += res.data.submissionCount[i].count;
        }
      }
      setSubmissionCount(s);
    });
    timer = setInterval(() => {
      listServiceStatuses().then(res => {
        setSanboxSystemInfo(res.data.sanboxSystemInfo);
      });
    }, 1000);
    return () => {
      clearInterval(timer);
    };
  }, []);
  return isMounted && (
    <Card>
      <div>
        <div className='flex'>
          <div className='w-[40%]'>
            <div className='flex justify-around'>
              <Statistic title='今日做题用户数' value={userCount.today} groupSeparator style={{ marginRight: 20 }} />
              <Statistic title='近7天做题用户数' value={userCount.day7} groupSeparator style={{ marginRight: 20 }} />
              <Statistic title='近30天做题用户数' value={userCount.day30} groupSeparator style={{ marginRight: 20 }} />
            </div>
            <Divider />
            <div className='flex justify-around'>
              <Statistic title='今日提交数量' value={submissionCount.today} groupSeparator style={{ marginRight: 20 }} />
              <Statistic title='近7天提交数量' value={submissionCount.day7} groupSeparator style={{ marginRight: 20 }} />
              <Statistic title='近30天提交数量' value={submissionCount.day30} groupSeparator style={{ marginRight: 20 }} />
            </div>
          </div>
          <div className='flex w-[60%] h-[200px]'>
            <BarChart
              width={400}
              height={200}
              data={userActivities.userCount}
              margin={{
                top: 5,
                right: 30,
                left: 20,
                bottom: 5,
              }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Bar dataKey="count" name='做题用户数' fill="#47c04d" activeBar={<Rectangle fill="pink" stroke="blue" />} />
            </BarChart>
            <BarChart
              width={400}
              height={200}
              data={userActivities.submissionCount}
              margin={{
                top: 5,
                right: 30,
                left: 20,
                bottom: 5,
              }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Bar dataKey="count" name='提交数量' fill="#2d62f8" activeBar={<Rectangle fill="pink" stroke="blue" />} />
            </BarChart>
          </div>
        </div>
        <Divider />
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
      </div>
    </Card>
  );
};

Index.getLayout = Layout;
export default Index;
