import React, { useContext, useEffect, useState } from 'react';
import { AutoComplete, Card } from '@arco-design/web-react';
import ContestList from '@/modules/contest/list';
import Layout from './Layout';
import Groups from './groups';
import context from './context';
import { useRouter } from 'next/router';
import { listContestStandingStats } from '@/api/contest';
import { listGroupUsers } from '@/api/group';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Legend, ResponsiveContainer, LabelList } from 'recharts';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';

// 排名统计图
function StandingStats() {
  const router = useRouter();
  const group = useContext(context);
  const [chartData, setChartData] = useState([]);
  const [users, setUsers] = useState([]);
  const [userId, setUserId] = useState('0');
  const user = useAppSelector(userInfo);
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
    if (user.id) {
      setUserId(user.id);
    }
  }, [user.id]);
  useEffect(() => {
    listContestStandingStats({groupId: id, userId: [userId]}).then(res => {
      const data = [];
      res.data.data.forEach(item => {
        data.push({
          name: item.contestName,
          rank: item.rank,
        });
      });
      setChartData(data);
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
      {chartData.length > 0 && (
        <ResponsiveContainer width="100%" height={300}>
          <BarChart
            data={chartData}
            margin={{
              top: 5,
              right: 30,
              left: 20,
              bottom: 5
            }}
          >
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="name" />
            <YAxis />
            <Legend />
            <Bar dataKey="rank" barSize={20} fill="#2d62f8">
              <LabelList position='top' />
            </Bar>
          </BarChart>
        </ResponsiveContainer>
      )}
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
