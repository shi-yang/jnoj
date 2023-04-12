import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import { getUserProfileCalendar, getUserProfileProblemSolved, getUsers } from '@/api/user';
import HeatMap from '@uiw/react-heat-map';
import { Card, Divider, Radio, Select, Space, Statistic, Tabs, Tag, Typography } from '@arco-design/web-react';
import Head from 'next/head';
import { setting, SettingState } from '@/store/reducers/setting';
import { useAppSelector } from '@/hooks';
import Submission from '@/modules/submission/Submission';
import useLocale from '@/utils/useLocale';
import locale from './locale';

export default function UserPage() {
  const router = useRouter();
  const t = useLocale(locale);
  const { id } = router.query;
  const [user, setUser] = useState({username: '', nickname: ''});
  const settings = useAppSelector<SettingState>(setting);
  const [calendarOptions, setCalendarOptions] = useState([]);
  const [profileCalendar, setProfileCalendar] = useState({
    submissionCalendar: [],
    totalSubmission: 0,
    totalProblemSolved: 0,
    totalActiveDays: 0,
    start: '',
    end: '',
  });
  const [profileProblemSolved, setProfileProblemSolved] = useState([]);
  function onCalendarSelectChange(e) {
    getUserProfileCalendar(id, { year: e })
      .then(res => {
        const { data } = res;
        setProfileCalendar(data);
      });
  }
  function onProblemSolvedTabChange(e) {
    getUserProfileProblemSolved(id, {type: e})
      .then(res => {
        setProfileProblemSolved(res.data.problems);
      })
  }
  useEffect(() => {
    getUsers(id)
      .then(res => {
        setUser(res.data);
      });
    getUserProfileCalendar(id).
      then(res => {
        const { data } = res;
        setProfileCalendar(data);
        data.activeYears.forEach(item => {
          setCalendarOptions(current => [...current, {
            name: item,
            value: item
          }]);
        });
      });
    getUserProfileProblemSolved(id, {type: 'problemset'})
      .then(res => {
        setProfileProblemSolved(res.data.problems);
      })
  }, [id]);
  return (
    <div>
      <Head>
        <title>{`${user.username} - ${settings.name}`}</title>
      </Head>
      <div className='container'>
        <div>
          <Typography.Title>
            {user.nickname} <Divider type='vertical' /><small>{user.username}</small>
          </Typography.Title>
        </div>
        <Card 
          extra={
            <div>
              <Space>
                <Select style={{ width: 154 }} defaultValue={0} onChange={onCalendarSelectChange}>
                  <Select.Option value={0}>
                    {t['pastYear']}
                  </Select.Option>
                  {calendarOptions.map((option, index) => (
                    <Select.Option key={index} value={option.value}>
                      {option.name}
                    </Select.Option>
                  ))}
                </Select>
              </Space>
            </div>
          }
        >
          <HeatMap
            value={profileCalendar.submissionCalendar}
            width={'100%'}
            weekLabels={false}
            rectSize={20}
            startDate={new Date(profileCalendar.start)}
          />
          <Space>
            <Statistic title={t['totalSubmission']} value={profileCalendar.totalSubmission} groupSeparator style={{ marginRight: 60 }} />
            <Statistic title={t['activeDays']} value={profileCalendar.totalActiveDays} groupSeparator style={{ marginRight: 60 }} />
            <Statistic title={t['problemSolved']} value={profileCalendar.totalProblemSolved} groupSeparator style={{ marginRight: 60 }} />
          </Space>
        </Card>
        <Divider type='horizontal' />
        <Submission userId={Number(id)} />
        <Divider type='horizontal' />
        <Card
          title='做题情况'
        >
          {/* <Tabs
            type='rounded'
            style={{marginBottom: '10px'}}
            onChange={onProblemSolvedTabChange}
          >
            <Tabs.TabPane key="problemset" title='题单' />
            <Tabs.TabPane key="group" title='小组' />
          </Tabs> */}
          <Space wrap>
            {profileProblemSolved.map((item, index) => (
              <Tag key={index} color={item.status === 'CORRECT' ? 'green' : 'orange'}>{item.id}</Tag>
            ))}
          </Space>
        </Card>
      </div>
    </div>
  );
}
