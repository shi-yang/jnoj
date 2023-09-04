import React, { useContext, useEffect, useState } from 'react';
import { AutoComplete, Card } from '@arco-design/web-react';
import ContestList from '@/modules/contest/list';
import Layout from './Layout';
import Groups from './groups';
import context from './context';
import ReactECharts from 'echarts-for-react';
import { useRouter } from 'next/router';
import { listContestStandingStats } from '@/api/contest';
import { listGroupUsers } from '@/api/group';

// 排名统计图
function StandingStats() {
  const router = useRouter();
  const group = useContext(context);
  const [lineChartOption, setLineChartOption] = useState({
    xAxis: {
      data: []
    },
    yAxis: {},
    series: [{
      type: 'line', label: {show:true},
      data: []
    }]
  });
  const [users, setUsers] = useState([]);
  const [userId, setUserId] = useState('0');
  const { id } = router.query;
  const handleSearch = (inputValue) => {
    if (!inputValue) {
      setUsers([]);
      return;
    }
    listGroupUsers(id, {username: inputValue}).
      then(res => {
        setUsers(res.data.data);
      });
  };
  useEffect(() => {
    listContestStandingStats({groupId: id, userId: [userId]}).then(res => {
      const {data} = res.data;
      const xAxisData = [];
      const seriesData = [];
      data.forEach(item => {
        xAxisData.push(item.contestName);
        seriesData.push(item.rank);
      });
      setLineChartOption({
        ...lineChartOption,
        xAxis: {data: xAxisData},
        series: [
          {type: 'line', data: seriesData, label: {show:true}}
        ]
      });
    });
  }, [userId]);
  return (
    <Card
      title='历次比赛排名'
      extra={
        (group.role === 'ADMIN' || group.role === 'MANAGER') && (
          <AutoComplete
            placeholder='输入用户名搜索'
            onSearch={handleSearch}
            onSelect={(v) => setUserId(users.find(item => item.username === v).userId)}
          >
            {users.map((item, index) => {
              return (
                <AutoComplete.Option key={index} value={`${item.username}`}>
                  {item.username}
                </AutoComplete.Option>
              );
            })}
          </AutoComplete>
        )
      }
    >
      <ReactECharts
        option={lineChartOption}
        style={{ height: 400 }}
        opts={{ locale: 'FR' }}
      />
    </Card>
  );
}

function Overview() {
  const group = useContext(context);
  return (
    <Card>
      {group.type === 'GROUP' ? (
        <div>
          <StandingStats />
          <ContestList groupId={group.id} />
        </div>
      ) : (
        <Groups />
      )}
    </Card>
  );
}

Overview.getLayout = Layout;
export default Overview;
