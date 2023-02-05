import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { getUserProfileCalendar, getUsers } from '@/api/user';
import HeatMap from '@uiw/react-heat-map';
import { Card, Divider, Select, Space, Typography } from '@arco-design/web-react';
import Head from 'next/head';
import { setting, SettingState } from '@/store/reducers/setting';
import { useAppSelector } from '@/hooks';
import Submission from '@/components/Submission/Submission';

export default function() {
  const router = useRouter();
  const { id } = router.query;
  const [user, setUser] = useState({username: ''});
  const [profileCalendar, setProfileCalendar] = useState({
    submissionCalendar: [],
    total: 0,
    totalActiveDays: 0,
    start: '',
    end: '',
  });
  const settings = useAppSelector<SettingState>(setting);
  const [calendarOptions, setCalendarOptions] = useState([{name: '过去一年', value: 0}]);
  function onCalendarSelectChange(e) {
    getUserProfileCalendar(id, { year: e })
      .then(res => {
        const { data } = res;
        setProfileCalendar(data);
      })
  }
  useEffect(() => {
    getUsers(id)
      .then(res => {
        setUser(res.data);
      })
    getUserProfileCalendar(id).
      then(res => {
        const { data } = res;
        setProfileCalendar(data);
        data.activeYears.forEach(item => {
          setCalendarOptions(current => [...current, {
            name: item,
            value: item
          }])
        })
      })
  }, []);
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
          title={`总提交：${profileCalendar.total}`}
          extra={
            <div>
              <Space>
                <span>累计提交天数：{profileCalendar.totalActiveDays}</span>
                <Select style={{ width: 154 }} defaultValue={0} onChange={onCalendarSelectChange}>
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
        </Card>
        <Divider type='horizontal' />
        <Submission userId={id} />
      </div>
    </div>
  )
}
