import Head from 'next/head';
import { useRouter } from 'next/router';
import React, { useState, useEffect } from 'react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { Tabs, Typography } from '@arco-design/web-react';
import { userInfo } from '@/store/reducers/user';
import useLocale from '@/utils/useLocale';
import { IconHome, IconUser } from '@arco-design/web-react/icon';
import MainLayout from '@/components/Layouts/MainLayout';
import locale from './locale';
import styles from './style/index.module.less';

function Layout(page) {
  const t = useLocale(locale);
  const router = useRouter();
  const settings = useAppSelector<SettingState>(setting);
  const user = useAppSelector(userInfo);
  const [activeTab, setActiveTab] = useState('');
  function fetchData() {
  }
  function onTabChange(e) {
    setActiveTab(e);
  }
  useEffect(() => {
    fetchData();
  }, [activeTab]);
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <MainLayout>
      <Head>
        <title>{`Admin - ${settings.name}`}</title>
      </Head>
      <div>
        <div className={styles['header']}>
          <div className='container'>
            <Typography.Title>
              {t['page.title']}
            </Typography.Title>
            <Tabs
              size='large'
              activeTab={activeTab}
              onChange={onTabChange}
            >
              <Tabs.TabPane key='' title={<span><IconHome /> {t['header.tab.overview']}</span>} />
              <Tabs.TabPane key='user' title={<span><IconUser /> {t['header.tab.user']}</span>} />
            </Tabs>
          </div>
        </div>
        <div className='container'>
          {page}
        </div>
      </div>
    </MainLayout>
  );
};

export default Layout;
