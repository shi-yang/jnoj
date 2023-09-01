import React, { useEffect, useState } from 'react';
import {
  Typography,
  Grid,
  Breadcrumb,
  Link,
  Tabs,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import { useRouter } from 'next/router';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import { IconRight } from '@arco-design/web-react/icon';
import { getProblemset } from '@/api/problemset';
import ExamProblems from './exam-problems';
import SimpleProblems from './simple-problems';
import Info from './info';
import User from './user';
import Answer from './answer';

const TabPane = Tabs.TabPane;

function Index(props) {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(true);
  const [problemset, setProblemset] = useState({
    id: 0,
    name: '',
    description: '',
    type: '',
  });
  const router = useRouter();
  const settings = useAppSelector<SettingState>(setting);
  function fetchData() {
    setLoading(true);
    getProblemset(router.query.id)
      .then((res) => {
        setProblemset(res.data);
      })
      .finally(() => {
        setLoading(false);
      });
  }

  useEffect(() => {
    fetchData();
  }, []);

  return (
    <>
      <Head>
        <title>{`${t['page.title']} - ${problemset.name} - ${settings.name}`}</title>
      </Head>
      { !loading && (
        <div className={styles.container}>
          <Grid.Row className={styles.header} justify="space-between" align="center">
            <Grid.Col span={24} style={{padding: '20px 15px'}}>
              <Breadcrumb separator={<IconRight />}>
                <Breadcrumb.Item>
                  <Link href='/problems'>题目准备系统</Link>
                </Breadcrumb.Item>
                <Breadcrumb.Item>
                {problemset.id}.&nbsp;
                <Typography.Paragraph className={styles.title}>
                  { problemset.name }
                </Typography.Paragraph>
                </Breadcrumb.Item>
              </Breadcrumb>
            </Grid.Col>
          </Grid.Row>
          <Tabs defaultActiveTab='info' destroyOnHide>
            <TabPane key='info' title={t['tab.baseInfo']}>
              <Info problemset={problemset} />
            </TabPane>
            <TabPane key='problem' title={t['tab.problem']}>
              {problemset.type === 'SIMPLE' ? (
                <SimpleProblems problemset={problemset} />
              ) : (
                <ExamProblems problemset={problemset} />
              )}
            </TabPane>
            <TabPane key='user' title={t['tab.user']}>
              <User problemset={problemset} />
            </TabPane>
            {problemset.type === 'EXAM' && (
              <TabPane key='answer' title={t['tab.answer']}>
                <Answer problemset={problemset} />
              </TabPane>
            )}
          </Tabs>
        </div>
      )}
    </>
  );
}

export default Index;
