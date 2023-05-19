import React, { useEffect, useState } from 'react';
import { Divider, Link, Message, Popconfirm, Typography } from '@arco-design/web-react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { useRouter } from 'next/router';
import { getProblemset, deleteProblemset } from '@/api/problemset';
import List from '@/modules/problemsets/list';
import Head from 'next/head';
import useLocale from '@/utils/useLocale';
import styles from './style/index.module.less';
import locale from './locale';
import { userInfo } from '@/store/reducers/user';

function Problem() {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [problemset, setProblemset] = useState({id: 0, name: '', description: '', userId: 0});
  const user = useAppSelector(userInfo);
  const settings = useAppSelector<SettingState>(setting);
  useEffect(() => {
    fetchData();
  }, []);
  function fetchData() {
    getProblemset(id)
      .then((res) => {
        setProblemset(res.data);
      });
  }
  function onDeleteProblemset() {
    deleteProblemset(id).then(() => {
      Message.info('已删除');
      router.push('/problemsets/all');
    });
  }
  return (
    <div className='container'>
      <Head>
        <title>{`${problemset.name} - ${t['page.title']} - ${settings.name}`}</title>
      </Head>
      <div>
        <div className={styles['header']}>
          <div>
            <Typography.Title>
              {problemset.name}
              {
                user.id === problemset.userId
                && (
                  <>
                    <Link href={`/problemsets/${problemset.id}/update`}>{t['header.edit']}</Link>
                    <Divider type='vertical' />
                    <Popconfirm
                      focusLock
                      title='Confirm'
                      content='Are you sure you want to delete?'
                      onOk={onDeleteProblemset}
                    >
                      <Link>删除</Link>
                    </Popconfirm>
                  </>
                )
              }
            </Typography.Title>
          </div>
          <div>{problemset.description}</div>
        </div>
        <Divider />
        <List problemsetID={Number(id)} />
      </div>
    </div>
  );
}

export default Problem;
