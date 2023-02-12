import React, { useEffect, useRef, useState } from 'react';
import {
  Tabs,
  Typography,
  Grid,
  ResizeBox,
  Select,
  Divider,
  Link,
  Tooltip,
  Button,
  Space,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import Editor from './editor';
import Description from './description';
import Submission from './submission';
import { IconClockCircle, IconInfoCircle, IconLanguage, IconRefresh } from '@arco-design/web-react/icon';
import { useRouter } from 'next/router';
import Head from 'next/head';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import { getProblemset, getProblemsetProblem } from '@/api/problemset';
const TabPane = Tabs.TabPane;
import ProblemContext from './context';
import ProblemLayout from '../ProblemLayout';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';
dayjs.extend(duration);

function Index() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  const [loading, setLoading] = useState(true);
  const timer = useRef(null);
  const [timeCount, setTimeCount] = useState(0);
  const [timeCountHidden, setTimeCountHidden] = useState(false);
  const [data, setData] = useState({
    id: 0,
    name: '',
    type: 'DEFAULT',
    source: '',
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
  function startTimer() {
    setTimeCount(0);
    timer.current = setInterval(() => {
      setTimeCount(v => v + 1);
    }, 1000)
  }
  function endTimer() {
    if (timer.current !== null) {
      clearTimeout(timer.current);
    }
    setTimeCount(0);
  }

  useEffect(() => {
    return () => {
      if (timer.current !== null) {
        clearTimeout(timer.current);
      }
    }
  }, [timer])

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
              <Grid.Col flex='auto'>
                <Typography.Title className={styles.title} heading={5}>
                  <Link href={`/problemsets/${problemset.id}`} target='_blank'>{problemset.name}</Link>
                  <Divider type='vertical' />
                  {`${pid}. ${data.statements[language].name}`}
                </Typography.Title>
              </Grid.Col>
              <Grid.Col flex='100px'>
                <Space>
                  { timeCountHidden ? 
                    <Tooltip position='bottom' trigger='hover' content={t['header.show']}>
                      <Button style={{color: 'rgb(var(--arcoblue-4))'}} onClick={(e) => setTimeCountHidden(false)}><IconClockCircle fontSize={20} /></Button>
                    </Tooltip>
                  :
                   (timeCount === 0 
                    ? <Tooltip position='bottom' trigger='hover' content={t['header.startTheTimer']}>
                      <Button onClick={(e) => startTimer()}><IconClockCircle fontSize={20} /></Button>
                    </Tooltip>
                    :
                    <Space size={0}>
                      <Tooltip position='bottom' trigger='hover' content={t['header.hide']}>
                        <Button onClick={(e) => setTimeCountHidden(true)}>
                          {dayjs.duration(timeCount, "seconds").format('HH:mm:ss')}
                        </Button>
                      </Tooltip>
                      <Tooltip position='bottom' trigger='hover' content={t['header.reset']}>
                        <Button onClick={(e) => endTimer()}>
                          <IconRefresh fontSize={20} />
                        </Button>
                      </Tooltip>
                    </Space>
                  )}
                  <Tooltip position='bottom' trigger='hover' content={
                    <Space direction='vertical'>
                      <span>Problem ID: {data.id}</span>
                      {data.source !== '' && <span>{t['source']}: {data.source}</span>}
                    </Space>
                  }>
                    <Button><IconInfoCircle fontSize={20} /></Button>
                  </Tooltip>
                </Space>
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
Index.getLayout = ProblemLayout;
export default Index;
