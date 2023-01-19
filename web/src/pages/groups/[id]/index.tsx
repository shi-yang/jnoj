import Head from 'next/head';
import { useRouter } from 'next/router';
import { useState, useEffect } from 'react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { Link, Tabs, Typography } from '@arco-design/web-react';
import { userInfo } from '@/store/reducers/user';
import locale from './locale';
import useLocale from '@/utils/useLocale';
import styles from './style/index.module.less';
import { getGroup } from '@/api/group';

export default () => {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [group, setGroup] = useState({id: 12, name: '123', description: '123', userId: 1});
  const settings = useAppSelector<SettingState>(setting);
  const user = useAppSelector(userInfo);
  function fetchData() {
    getGroup(id)
      .then(res => {
        setGroup(res.data);
      })
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <div className='container'>
      <Head>
        <title>{`${group.name} - ${settings.name}`}</title>
      </Head>
      <div>
        <div className={styles['header']}>
          <div>
            <Typography.Title>
              {group.name}
              {
                user.id === group.userId
                && <Link href={`/groups/${group.id}/setting`}>{t['header.edit']}</Link>
              }
            </Typography.Title>
          </div>
          <div>{group.description}</div>
        </div>
        <Tabs
          defaultActiveTab='1'
        >
          <Tabs.TabPane key='1' title='主页'>
            <Typography.Paragraph>Content of Tab Panel 1</Typography.Paragraph>
          </Tabs.TabPane>
          <Tabs.TabPane key='2' title='比赛'>
            <Typography.Paragraph>Content of Tab Panel 2</Typography.Paragraph>
          </Tabs.TabPane>
          <Tabs.TabPane key='3' title='成员'>
            <Typography.Paragraph>Content of Tab Panel 3</Typography.Paragraph>
          </Tabs.TabPane>
        </Tabs>
      </div>
    </div>
  )
}
