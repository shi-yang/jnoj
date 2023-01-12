import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { getUsers } from '@/api/user';
import HeatMap from '@uiw/react-heat-map';
import { Typography } from '@arco-design/web-react';
import Head from 'next/head';
import { setting, SettingState } from '@/store/reducers/setting';
import { useAppSelector } from '@/hooks';

const value = [
  { date: '2016/01/11', count: 2, content: '' },
  { date: '2016/01/12', count: 20, content: '' },
  { date: '2016/01/13', count: 10, content: '' },
  ...[...Array(17)].map((_, idx) => ({ date: `2016/02/${idx + 10}`, count: idx, content: '' })),
  { date: '2016/04/11', count: 2, content: '' },
  { date: '2016/05/01', count: 5, content: '' },
  { date: '2016/05/02', count: 5, content: '' },
  { date: '2016/05/04', count: 11, content: '' },
];

export default function() {
  const router = useRouter();
  const { id } = router.query;
  const [user, setUser] = useState({username: ''});
  const settings = useAppSelector<SettingState>(setting);
  useEffect(() => {
    getUsers(id)
      .then(res => {
        setUser(res.data);
      })
  }, []);
  return (
    <>
      <Head>
        <title>{`${user.username} - 用户主页 - ${settings.name}`}</title>
      </Head>
      <div className='container'>
        <div>
          <Typography.Title>
            {user.username}
          </Typography.Title>
        </div>
        <HeatMap
          value={value}
          width={'100%'}
          startDate={new Date('2016/01/01')}
        />
      </div>
    </>
  )
}
