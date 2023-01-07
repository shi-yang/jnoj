import React from 'react';
import { Space } from '@arco-design/web-react';
import styles from './style/index.module.less';
import List from '@/modules/problemsets/list';
import Head from 'next/head';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import Sidebar from './sidebar';

function Problem() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  return (
    <div className='container'>
      <Head>
        <title>{`${t['page.title']} - ${settings.name}`}</title>
      </Head>
      <div className={styles.wrapper}>
        <Space size={16} direction="vertical" className={styles.left}>
          <List problemsetID={1} />
        </Space>
        <Space className={styles.right} size={16} direction="vertical">
          <Sidebar />
        </Space>
      </div>
    </div>
  );
}

export default Problem;
