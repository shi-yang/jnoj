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
import { IconHome, IconSettings, IconUser } from '@arco-design/web-react/icon';

import Overview from './overview';
import People from './people';
import Settings from './settings';

export default () => {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [group, setGroup] = useState({id: 0, name: '', description: '', userId: 0, role: ''});
  const settings = useAppSelector<SettingState>(setting);
  const user = useAppSelector(userInfo);
  const [activeTab, setActiveTab] = useState('overview');
  const [isLoading, setIsLoading] = useState(true);
  function fetchData() {
    getGroup(id)
      .then(res => {
        setGroup(res.data);
        setIsLoading(false);
      });
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    !isLoading &&
    <>
      <Head>
        <title>{`${group.name} - ${settings.name}`}</title>
      </Head>
      <div>
        <div className={styles['header']}>
          <div className='container'>
            <Typography.Title>
              {group.name}
              {
                user.id === group.userId
                && <Link href={`/groups/${group.id}/setting`}>{t['header.edit']}</Link>
              }
            </Typography.Title>
            <div>{group.description}</div>
            <Tabs
              size='large'
              activeTab={activeTab}
              onChange={setActiveTab}
            >
              <Tabs.TabPane key='overview' title={<span><IconHome /> {t['header.tab.overview']}</span>} />
              <Tabs.TabPane key='people' title={<span><IconUser /> {t['header.tab.people']}</span>} />
              <Tabs.TabPane key='settings' title={<span><IconSettings /> {t['header.tab.settings']}</span>} />
            </Tabs>
          </div>
        </div>
        <div className='container'>
          {activeTab === 'overview' && <Overview group={group} />}
          {activeTab === 'people' && <People group={group} />}
          {(group.role === 'ADMIN' || group.role === 'MANAGER') &&  activeTab === 'settings'
            && <Settings group={group} callback={fetchData} />}
        </div>
      </div>
    </>
  )
}
