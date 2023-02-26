import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import { getUserProfileCalendar, getUsers } from '@/api/user';
import HeatMap from '@uiw/react-heat-map';
import { Card, Divider, Select, Space, Statistic, Typography } from '@arco-design/web-react';
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
  const [user, setUser] = useState({username: ''});
  const [profileCalendar, setProfileCalendar] = useState({
    submissionCalendar: [],
    totalSubmission: 0,
    totalProblemSolved: 0,
    totalActiveDays: 0,
    start: '',
    end: '',
  });
  const settings = useAppSelector<SettingState>(setting);
  const [calendarOptions, setCalendarOptions] = useState([]);
  function onCalendarSelectChange(e) {
    getUserProfileCalendar(id, { year: e })
      .then(res => {
        const { data } = res;
        setProfileCalendar(data);
      });
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
  }, [id]);
  return (
    <div>
      <Head>
        <title>{`${user.username} - ${settings.name}`}</title>
      </Head>
      <div className='container'>
        <div>
          <Typography.Title>
            {user.username}
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
      </div>
    </div>
  );
}
