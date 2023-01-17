import Head from 'next/head';
import { useState } from 'react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { Divider, Link, Typography } from '@arco-design/web-react';
import { userInfo } from '@/store/reducers/user';
import locale from './locale';
import useLocale from '@/utils/useLocale';
import styles from './style/index.module.less';

export default () => {
  const [group, setGroup] = useState({id: 12, name: '123', description: '123', userId: 1});
  const settings = useAppSelector<SettingState>(setting);
  const t = useLocale(locale);
  const user = useAppSelector(userInfo);
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
                && <Link href={`/problemsets/${group.id}/setting`}>{t['header.edit']}</Link>
              }
            </Typography.Title>
          </div>
          <div>{group.description}</div>
        </div>
        <Divider />
      </div>
    </div>
  )
}
