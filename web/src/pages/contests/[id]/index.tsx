import React, { lazy, Suspense, useEffect, useState } from 'react';
import { Layout, Menu, Typography, Grid, Slider, Statistic } from '@arco-design/web-react';
import { IconHome, IconOrderedList, IconFile, IconSelectAll, IconSettings } from '@arco-design/web-react/icon';
import styles from './style/index.module.less';
import { getContest, listContestProblems } from '@/api/contest';
import './mock';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { FormatTime } from '@/utils/format';
import { useRouter } from 'next/router';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import Forbidden from './forbidden';
import { ProblemStatus } from '@/modules/problemsets/list/constants';
import ContestContext from './context';
import dayjs from 'dayjs';

const Info = lazy(() => import('./info'));
const Problem = lazy(() => import('./problem'));
const Setting = lazy(() => import('./setting'));
const Standings = lazy(() => import('./standings'));
const Submission = lazy(() => import('./submission'));

const MenuItem = Menu.Item;
const SubMenu = Menu.SubMenu;
const Sider = Layout.Sider;
const Header = Layout.Header;
const Content = Layout.Content;
const Row = Grid.Row;
const Col = Grid.Col;
const collapsedWidth = 60;
const normalWidth = 220;

function ContestHeader({contest}: any) {
  const t = useLocale(locale);
  const [sliderValue, setSliderValue] = useState(0);
  const [currentTime, setCurrentTime] = useState(new Date());
  let timer = null;
  let contestDuration = 0;
  const updateTime = (startTime, endTime) => {
    contestDuration = new Date(endTime).getTime() - new Date(startTime).getTime();
    timer = setInterval(() => {
      const t = new Date();
      const diff = t.getTime() - new Date(startTime).getTime();
      setSliderValue(diff / contestDuration * 100);
      setCurrentTime(new Date());
    }, 1000);
  };
  useEffect(() => {
    updateTime(contest.startTime, contest.endTime);
    return () => {
      clearInterval(timer);
    };
  }, []);
  return (
    <Header>
      <Typography.Title className={styles.title}>{contest.name}</Typography.Title>
      <Row className={styles['contest-header-time']}>
        <Col md={8}>
          <div>
            <strong>{t['header.start']}</strong> {FormatTime(contest.startTime)}
          </div>
        </Col>
        <Col md={8}>
          <div style={{textAlign: 'center'}}>
            <strong>{t['header.now']}</strong> {FormatTime(currentTime)}
          </div>
        </Col>
        <Col md={8} style={{textAlign: 'right'}}>
          <div>
            <strong>{t['header.end']}</strong> {FormatTime(contest.endTime)}
          </div>
        </Col>
      </Row>
      <Slider value={sliderValue} formatTooltip={(v) =>
        <Statistic.Countdown
          styleValue={{color: 'var(--color-neutral-1)', fontSize: '16px'}}
          value={dayjs(contest.endTime)}
          format='剩余 D 天 H 时 m 分 s 秒'
          now={currentTime}
        />
      } />
    </Header>
  );
}

function Index() {
  const t = useLocale(locale);
  const [contest, setContest] = useState({
    id: 0,
    name: '',
    startTime: new Date(),
    endTime: new Date(),
    privacy: '',
    membership: '',
    role: 'GUEST',
    type: 0,
    groupId: 0,
    participantCount: 0,
    runningStatus: '',
    invitationCode: '',
    description: '',
    owner: {
      id: 0,
      type: '',
      name: '',
    },
    problems: []
  });
  const [loading, setLoading] = useState(true);
  const [collapsed, setCollapsed] = useState(false);
  const [siderWidth, setSiderWidth] = useState(normalWidth);
  const [problems, setProblems] = useState([]);
  const [menuSelected, setMenuSelected] = useState('info');
  const [problemNumber, setProblemNumber] = useState('A');
  const settings = useAppSelector<SettingState>(setting);
  const router = useRouter();

  const fetchData = () => {
    setLoading(true);
    getContest(router.query.id)
      .then((res) => {
        const { data } = res;
        setContest(data);
        if (data.role !== 'GUEST' && data.runningStatus !== 'NOT_STARTED') {
          setMenuSelected('info');
          listContestProblems(router.query.id).then(res => {
            setProblems(res.data.data);
          });
        }
      })
      .finally(() => {
        setLoading(false);
      });
  };

  useEffect(() => {
    fetchData();
  }, []);

  const onCollapse = (collapsed) => {
    setCollapsed(collapsed);
    setSiderWidth(collapsed ? collapsedWidth : normalWidth);
  };

  const handleMoving = (_, { width }) => {
    if (width > collapsedWidth) {
      setSiderWidth(width);
      setCollapsed(!(width > collapsedWidth + 20));
    } else {
      setSiderWidth(collapsedWidth);
      setCollapsed(true);
    }
  };

  const handleMenuClick = (key:string) => {
    if (key.indexOf('/')) {
      const a = key.split('/');
      setMenuSelected(a[0]);
      setProblemNumber(a[1]);
    } else {
      setMenuSelected(key);
    }
  };

  return (
    (!loading &&
      <ContestContext.Provider value={{...contest, problems: problems}}>
        <div className={styles['contest-layout-basic']}>
          <Head>
            <title>{`${contest.name} - ${settings.name}`}</title>
          </Head>
          <Layout style={{height: '100%'}}>
            <ContestHeader contest={contest} />
            {(contest.role === 'GUEST' && (contest.privacy === 'PRIVATE' || contest.runningStatus !== 'FINISHED')) || (contest.role !== 'ROLE_ADMIN' && contest.runningStatus === 'NOT_STARTED')
              ? <Forbidden />
              : <Layout style={{height: '100%'}}>
                <Sider
                  collapsible
                  theme='light'
                  style={{height: '100%'}}
                  onCollapse={onCollapse}
                  collapsed={collapsed}
                  width={siderWidth}
                  resizeBoxProps={{
                    directions: ['right'],
                    onMoving: handleMoving,
                  }}
                >
                  <div className='logo' />
                  <Menu theme='light' autoOpen style={{ width: '100%' }} onClickMenuItem={handleMenuClick}>
                    <MenuItem key='info'><IconHome /> {t['menu.info']}</MenuItem>
                    <MenuItem key='standings'><IconOrderedList /> {t['menu.standings']}</MenuItem>
                    <MenuItem key='submission'><IconFile /> {t['menu.submission']}</MenuItem>
                    {contest.role === 'ROLE_ADMIN' && <MenuItem key='setting'><IconSettings /> {t['menu.setting']}</MenuItem>}
                    <SubMenu
                      key='layout'
                      title={<span><IconSelectAll /> {t['menu.problem']}</span>}
                    >
                      {problems.map(value => 
                        <MenuItem key={`problem/${String.fromCharCode(65 + value.number)}`}>
                          {String.fromCharCode(65 + value.number)}. {value.name}
                          <span className='arco-menu-icon-suffix'>
                            {ProblemStatus[value.status]}
                          </span>
                        </MenuItem>
                      )}
                    </SubMenu>
                  </Menu>
                </Sider>
                <Content>
                  <Suspense>
                    {menuSelected === 'info' && <Info />}
                    {contest.role === 'ROLE_ADMIN' && menuSelected === 'setting' && <Setting />}
                    {menuSelected === 'submission' && <Submission />}
                    {menuSelected === 'standings' && <Standings />}
                    {menuSelected === 'problem' && <Problem number={problemNumber} />}
                  </Suspense>
                </Content>
              </Layout>
            }
          </Layout>
        </div>
      </ContestContext.Provider>
    )
  );
}

export default Index;
