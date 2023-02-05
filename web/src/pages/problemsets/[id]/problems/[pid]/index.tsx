import React, { useEffect, useState } from 'react';
import {
  Tabs,
  Typography,
  Grid,
  ResizeBox,
  Select,
  Divider,
  Link,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import Editor from './editor';
import Description from './description';
import Submission from './submission';
import { IconLanguage } from '@arco-design/web-react/icon';
import { useRouter } from 'next/router';
import Head from 'next/head';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import { getProblemset, getProblemsetProblem } from '@/api/problemset';
const TabPane = Tabs.TabPane;
import ProblemContext from './context';

function Index() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting)
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState({
    id: 0,
    name: '',
    type: 'DEFAULT',
    statements: [],
    sampleTests: []
  });
  const [problemset, setProblemset] = useState({
    id: 0,
    name: ''
  });
  const [language, setLanguage] = useState(0);
  const [languageOptions, setLanguageOptions] = useState([]);
  const router = useRouter();
  const { id, pid } = router.query
  function fetchData(order) {
    setLoading(true);
    getProblemsetProblem(id, order)
      .then((res) => {
        setData(res.data);
        const langs = res.data.statements.map((item, index) => {
          return {
            label: item.language,
            value: index,
          }
        });
        setLanguageOptions(langs);
      })
      .finally(() => {
        setLoading(false);
      });
  }

  useEffect(() => {
    fetchData(pid);
    getProblemset(id)
      .then(res => {
        setProblemset(res.data)
      })
  }, []);

  return (
    <>
      { !loading && (
        <ProblemContext.Provider value={{
          problem: data,
          updateProblem: fetchData,
        }}>
          <Head>
            <title>{`${problemset.name} - ${pid}. ${data.statements[language].name} - ${settings.name}`}</title>
          </Head>
          <div className={styles.container}>
            <Grid.Row className={styles.header} justify="space-between" align="center">
              <Grid.Col span={24}>
                <Typography.Title className={styles.title} heading={5}>
                  <Link href={`/problemsets/${problemset.id}`} target='_blank'>{problemset.name}</Link>
                  <Divider type='vertical' />
                  {`${pid}. ${data.statements[language].name}`}
                </Typography.Title>
              </Grid.Col>
            </Grid.Row>
            <ResizeBox.Split
              max={0.8}
              min={0.2}
              style={{ height: 'calc(100% - 69px)' }}
              panes={[
                <div key='first' className={styles.left}>
                  <Tabs
                    className={styles['tabs-container']}
                    destroyOnHide
                    extra={
                      languageOptions.length > 1 &&
                      <>
                        <Select
                          bordered={false}
                          size='small'
                          defaultValue={language}
                          onChange={(value) =>
                            setLanguage(value)
                          }
                          triggerProps={{
                            autoAlignPopupWidth: false,
                            autoAlignPopupMinWidth: true,
                            position: 'bl',
                          }}
                          triggerElement={
                            <span className={styles['header-language']}>
                              <IconLanguage /> {languageOptions[language].label}
                            </span>
                          }
                        >
                          {languageOptions.map((option, index) => (
                            <Select.Option key={index} value={option.value}>
                              {option.label}
                            </Select.Option>
                          ))}
                        </Select>
                      </>
                    }
                  >
                    <TabPane key='problem' className={styles['tabs-pane']} title={t['tab.description']}>
                      <Description language={language} problemset={problemset} />
                    </TabPane>
                    <TabPane key='submission' className={styles['tabs-pane']} title={t['tab.submissions']}>
                      <Submission problem={data} />
                    </TabPane>
                  </Tabs>
                </div>,
                <div key='second' className={styles.right}>
                  <Editor />
                </div>,
              ]}
            />
          </div>
        </ProblemContext.Provider>
      )}
    </>
  );
}

export default Index;
