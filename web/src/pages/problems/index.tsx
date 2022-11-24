import React from 'react';
import { Space } from '@arco-design/web-react';
import styles from './style/index.module.less';
import List from './list';
import Shortcuts from './shortcuts';
import Head from 'next/head';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';

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
          <List />
        </Space>
        <Space className={styles.right} size={16} direction="vertical">
          <Shortcuts />
        </Space>
      </div>
    </div>
  );
}

export default Problem;
