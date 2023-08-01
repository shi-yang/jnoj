import React, { lazy, useEffect, useState } from 'react';
import {
  Tabs,
  Typography,
  Grid,
  Breadcrumb,
  Link,
  Message,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import Info from './info';
import Statement from './statement';
import Tests from './tests';
import Checker from './checker';
import SolutionFiles from './solution-files';
import Files from './files';
import Package from './package';
import LanguageFiles from './language-files';
import { getProblem, Problem, updateProblem } from '@/api/problem';
import { useRouter } from 'next/router';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import ObjectivePage from './objective';
import { IconRight } from '@arco-design/web-react/icon';

const TabPane = Tabs.TabPane;

function Index(props) {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState<Problem>({id: 0, statements: [], name: '', type: '', sampleTests:[]});
  const router = useRouter();
  const settings = useAppSelector<SettingState>(setting);
  function fetchData() {
    setLoading(true);
    getProblem(router.query.id)
      .then((res) => {
        setData(res.data);
      })
      .finally(() => {
        setLoading(false);
      });
  }
  function onProblemNameChange(name) {
    setData(v => ({...v, name: name}));
  }
  function onProblemNameEnd(name) {
    data.name = name;
    updateProblem(data.id, data)
      .then(() => {
        Message.success('已更新');
      });
  }

  useEffect(() => {
    fetchData();
  }, []);

  return (
    <>
      <Head>
        <title>{`${t['page.title']} - ${data.name} - ${settings.name}`}</title>
      </Head>
      { !loading && (
        <div className={styles.container}>
          <Grid.Row className={styles.header} justify="space-between" align="center">
            <Grid.Col span={24}>
            </Grid.Col>
            <Grid.Col span={24} style={{padding: '20px 15px'}}>
              <Breadcrumb separator={<IconRight />}>
                <Breadcrumb.Item>
                  <Link href='/problems'>题目准备系统</Link>
                </Breadcrumb.Item>
                <Breadcrumb.Item>
                {data.id}.&nbsp;
                <Typography.Paragraph style={{margin: 0, padding: 0}} editable={{
                  onChange: onProblemNameChange,
                  onEnd: onProblemNameEnd,
                }} className={styles.title}>
                  { data.name }
                </Typography.Paragraph>
                </Breadcrumb.Item>
              </Breadcrumb>
            </Grid.Col>
          </Grid.Row>
          {
            data.type === 'OBJECTIVE' ? (
              <ObjectivePage problem={data} />
            ) : (
              <Tabs defaultActiveTab='info' destroyOnHide>
                <TabPane key='info' title={t['tab.baseInfo']}>
                  <Info problem={data} />
                </TabPane>
                <TabPane key='statement' title={t['tab.statement']}>
                  <Statement problem={data} />
                </TabPane>
                <TabPane key='checker' title={t['tab.checker']}>
                  <Checker problem={data} />
                </TabPane>
                <TabPane key='tests' title={t['tab.tests']}>
                  <Tests problem={data} />
                </TabPane>
                <TabPane key='solutionFiles' title={t['tab.solutionFiles']}>
                  <SolutionFiles problem={data} />
                </TabPane>
                <TabPane key='files' title={t['tab.files']}>
                  <Files problem={data} />
                </TabPane>
                { data.type === 'FUNCTION' &&
                  <TabPane key='languageFiles' title={t['tab.languageFiles']}>
                    <LanguageFiles problem={data} />
                  </TabPane>
                }
                <TabPane key='package' title={t['tab.package']}>
                  <Package problemId={data.id} />
                </TabPane>
              </Tabs>
            )
          }
        </div>
      )}
    </>
  );
}

export default Index;
