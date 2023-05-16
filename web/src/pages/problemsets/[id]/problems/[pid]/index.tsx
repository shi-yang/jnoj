import React, { useEffect, useReducer, useRef, useState } from 'react';
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

function timerReducer(state, action) {
  switch(action.type) {
    case 'start':
      return {...state, isRunning: true};
    case 'stop':
      return {...state, isRunning: false};
    case 'reset':
      return {isRuning: false, time: 0};
    case 'tick':
      return {...state, time: state.time + 1};
    default:
      throw new Error();
  }
}
const timerInitialState = {
  isRunning: false,
  time: 0
};

const Timer = React.memo(() => {
  const t = useLocale(locale);
  const [state, dispatch] = useReducer(timerReducer, timerInitialState);
  const [timeCountHidden, setTimeCountHidden] = useState(false);
  const idRef = useRef(null);
  useEffect(() => {
    if (!state.isRunning) {
      return;
    }
    idRef.current = setInterval(() => dispatch({type: 'tick'}), 1000);
    return () => {
      clearInterval(idRef.current);
      idRef.current = null;
    };
    }, [state.isRunning]);
  return (
    <>
    {state.isRunning 
      ? (timeCountHidden
        ? <Tooltip position='bottom' trigger='hover' content={t['header.show']}>
          <Button style={{color: 'rgb(var(--arcoblue-4))'}} onClick={(e) => setTimeCountHidden(false)}>
            <IconClockCircle fontSize={20} />
          </Button>
        </Tooltip>
        : <Space size={0}>
          <Tooltip position='bottom' trigger='hover' content={t['header.hide']}>
            <Button onClick={(e) => setTimeCountHidden(true)}>
              {dayjs.duration(state.time, 'seconds').format('HH:mm:ss')}
            </Button>
          </Tooltip>
          <Tooltip position='bottom' trigger='hover' content={t['header.reset']}>
            <Button onClick={(e) => dispatch({type: 'reset'})}>
              <IconRefresh fontSize={20} />
            </Button>
          </Tooltip>
        </Space>
      )
      : <Tooltip position='bottom' trigger='hover' content={t['header.startTheTimer']}>
        <Button onClick={(e) => dispatch({type: 'start'})}><IconClockCircle fontSize={20} /></Button>
      </Tooltip>
    }
    </>
  );
});

Timer.displayName = 'Timer';

function Index() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  const [problem, setProblem] = useState({
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
  const { id, pid } = router.query;
  function fetchData(order) {
    getProblemsetProblem(id, order)
      .then((res) => {
        const problemData = res.data;
        if (problemData.statements.length > 0) {
          problem.name = problemData.statements[0].name;
        }
        setProblem(problemData);
        const langs = problemData.statements.map((item, index) => {
          return {
            label: item.language,
            value: index,
          };
        });
        setLanguageOptions(langs);
      });
  }
  function onChangeLanguage(e) {
    setLanguage(e);
    setProblem({...problem, name: problem.statements[e].name});
  }

  useEffect(() => {
    fetchData(pid);
    getProblemset(id)
      .then(res => {
        setProblemset(res.data);
      });
  }, []);

  return (
    <ProblemContext.Provider value={{
      problem: problem,
      language: language,
      fetchProblem: fetchData,
    }}>
      <Head>
        <title>{`${problemset.name} - ${pid}. ${problem.name} - ${settings.name}`}</title>
      </Head>
      <div className={styles.container}>
        <Grid.Row className={styles.header} justify="space-between" align="center">
          <Grid.Col flex='auto'>
            <Typography.Title className={styles.title} heading={5}>
              {
                problemset.id !== 1 &&
                <>
                  <Link href={`/problemsets/${problemset.id}`} target='_blank'>{problemset.name}</Link>
                  <Divider type='vertical' />
                </>
              }
              {`${pid}. ${problem.name}`}
            </Typography.Title>
          </Grid.Col>
          <Grid.Col flex='100px'>
            <Space>
              <Timer />
              <Tooltip position='bottom' trigger='hover' content={
                <Space direction='vertical'>
                  <span>Problem ID: {problem.id}</span>
                  {problem.source !== '' && <span>{t['source']}: {problem.source}</span>}
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
                      onChange={onChangeLanguage}
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
                  <Description problemset={problemset} />
                </TabPane>
                <TabPane key='submission' className={styles['tabs-pane']} title={t['tab.submissions']}>
                  <Submission problemId={problem.id} />
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
  );
}
Index.getLayout = ProblemLayout;
export default Index;
