import React, { lazy, useEffect, useState } from 'react';
import {
  Tabs,
  Typography,
  Grid,
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
import { getProblem, Problem } from '@/api/problem';
import { useRouter } from 'next/router';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import ObjectivePage from './objective';

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
              <Typography.Title className={styles.title} heading={5}>
              { data.id } - { data.name }
              </Typography.Title>
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
                {/* <TabPane key='files' title={t['tab.files']}>
                  <Files problem={data} />
                </TabPane> */}
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
