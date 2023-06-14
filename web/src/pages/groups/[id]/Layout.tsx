import Head from 'next/head';
import { useRouter } from 'next/router';
import React, { useState, useEffect } from 'react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { Link, Tabs, Typography } from '@arco-design/web-react';
import { userInfo } from '@/store/reducers/user';
import locale from './locale';
import useLocale from '@/utils/useLocale';
import styles from './style/index.module.less';
import { getGroup } from '@/api/group';
import { IconHome, IconList, IconSettings, IconUser, IconUserGroup } from '@arco-design/web-react/icon';
import MainLayout from '@/components/Layouts/MainLayout';
import GroupContext from './context';

function Layout(page) {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [group, setGroup] = useState({
    id: 0,
    name: '',
    description: '',
    userId: 0,
    role: '',
    membership: 0,
    privacy: 0,
    invitationCode: '',
    type: '',
    team: null,
  });
  const settings = useAppSelector<SettingState>(setting);
  const user = useAppSelector(userInfo);
  const [activeTab, setActiveTab] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  function fetchData() {
    getGroup(id)
      .then(res => {
        const g = res.data;
        setGroup(g);
        // 私有小组需要加入才能查看
        if (g.role === 'GUEST' && g.privacy === 0) {
          router.push(`/groups/${g.id}/join`);
        }
        setIsLoading(false);
      });
  }
  function onTabChange(e) {
    setActiveTab(e);
    router.push(`/groups/${group.id}/${e}`);
  }
  useEffect(() => {
    fetchData();
  }, [activeTab]);
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <MainLayout>
      {!isLoading &&
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
                  group.team
                  && <Link href={`/groups/${group.team.id}`}><IconUserGroup />{group.team.name}</Link>
                }
              </Typography.Title>
              <div>{group.description}</div>
              <Tabs
                size='large'
                activeTab={activeTab}
                onChange={onTabChange}
              >
                {group.type === 'TEAM' &&
                  <Tabs.TabPane key='' title={<span><IconUserGroup /> {t['header.tab.group']}</span>} />
                }
                {group.type === 'TEAM' &&
                  <Tabs.TabPane key='contest' title={<span><IconList /> {t['header.tab.contest']}</span>} />
                }
                {group.type === 'GROUP' &&
                  <Tabs.TabPane key='' title={<span><IconHome /> {t['header.tab.overview']}</span>} />
                }
                <Tabs.TabPane key='people' title={<span><IconUser /> {t['header.tab.people']}</span>} />
                {(group.role === 'ADMIN' || group.role === 'MANAGER') && 
                  <Tabs.TabPane key='settings' title={<span><IconSettings /> {t['header.tab.settings']}</span>} />
                }
              </Tabs>
            </div>
          </div>
          <div className='container'>
            <GroupContext.Provider
              value={group}
            >
              {page}
            </GroupContext.Provider>
          </div>
        </div>
      </>
      }
    </MainLayout>
  );
};

export default Layout;
