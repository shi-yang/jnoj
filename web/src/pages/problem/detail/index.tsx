import React, { useEffect, useState } from 'react';
import {
  Tabs,
  Typography,
  Grid,
  ResizeBox,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import Editor from './editor';
import Description from './description';
import Submission from './submission';
import { getProblem } from '@/api/problem';
import { useParams } from 'react-router-dom';
const TabPane = Tabs.TabPane;

function Index() {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState({
    id: 0,
    name: '',
    statements: []
  });
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
        <>
          <div className={styles.container}>
            <Grid.Row className={styles.header} justify="space-between" align="center">
              <Grid.Col span={24}>
                <Typography.Title className={styles.title} heading={5}>
                { data.id } - { data.statements[language].name }
                </Typography.Title>
              </Grid.Col>
            </Grid.Row>
            <ResizeBox.Split
              max={0.8}
              min={0.2}
              style={{ height: '100%' }}
              panes={[
                <div key='first' className={styles.left}>
                  <Tabs className={styles['tabs-container']} style={{ height: '100%' }}>
                    <TabPane key='problem' className={styles['tabs-pane']} style={{ height: '100%' }} title='题目描述'>
                      <Description problem={data} language={language} />
                    </TabPane>
                    <TabPane key='submission' className={styles['tabs-pane']} style={{ height: '100%' }} title='提交记录'>
                      <Submission problem={data} />
                    </TabPane>
                  </Tabs>
                </div>,
                <div key='second' className={styles.right}>
                  <Editor problem={data} language={language} />
                </div>,
              ]}
            />
          </div>
        </>
      )}
    </>
  );
}

export default Index;
