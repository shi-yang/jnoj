import React, { useEffect, useState } from 'react';
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
import Validator from './validator';
import SolutionFiles from './solution-files';
import { getProblem, Problem } from '@/api/problem';
import { useParams } from 'react-router-dom';

const TabPane = Tabs.TabPane;

function Index(props) {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState<Problem>({id:'', statements: [], name: ''});
  const [language, setLanguage] = useState(0);
  const params = useParams();
  function fetchData() {
    setLoading(true);
    getProblem(params.id)
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
      { !loading && (
        <div className={styles.container}>
          <Grid.Row className={styles.header} justify="space-between" align="center">
            <Grid.Col span={24}>
              <Typography.Title className={styles.title} heading={5}>
              { data.id } - { data.name }
              </Typography.Title>
            </Grid.Col>
          </Grid.Row>
          <Tabs defaultActiveTab='preview'>
            <TabPane key='preview' title={t['tab.baseInfo']}>
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
          </Tabs>
        </div>
      )}
    </>
  );
}

export default Index;
