import React, { useContext, useEffect, useState } from 'react';
import { Layout, Menu, Typography, Grid, Slider, Statistic, Link, Popconfirm, Message, Button, Divider } from '@arco-design/web-react';
import { IconHome, IconOrderedList, IconFile, IconSelectAll, IconSettings, IconUserGroup, IconBook } from '@arco-design/web-react/icon';
import styles from './style/index.module.less';
import { exitVirtualContest, getContest, listContestProblems } from '@/api/contest';
import './mock';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { FormatTime } from '@/utils/format';
import { useRouter } from 'next/router';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import { ProblemStatus } from '@/modules/problemsets/list/constants';
import ContestContext from './context';
import dayjs from 'dayjs';
import MainLayout from '@/components/Layouts/MainLayout';
import Forbidden from './forbidden';

const MenuItem = Menu.Item;
const SubMenu = Menu.SubMenu;
const Sider = Layout.Sider;
const Header = Layout.Header;
const Content = Layout.Content;
const Row = Grid.Row;
const Col = Grid.Col;
const collapsedWidth = 60;
const normalWidth = 220;

function ContestHeader() {
  const t = useLocale(locale);
  const contest = useContext(ContestContext);
  const router = useRouter();
  const [sliderValue, setSliderValue] = useState(0);
  const [currentTime, setCurrentTime] = useState(new Date());
  let timer = null;
  let contestDuration = 0;
  const updateTime = (startTime, endTime) => {
    contestDuration = new Date(endTime).getTime() - new Date(startTime).getTime();
    timer = setInterval(() => {
      let t = new Date();
      // 虚拟竞赛
      if (!!contest.virtualStart) {
        t = new Date(t.getTime() - new Date(contest.virtualStart).getTime() + new Date(startTime).getTime());
      }
      const diff = t.getTime() - new Date(startTime).getTime();
      setSliderValue(diff / contestDuration * 100);
      setCurrentTime(t);
    }, 1000);
  };
  const onExitVirtualContest = () => {
    exitVirtualContest(contest.id).then(() => {
      Message.success({
        content: '已退出虚拟竞赛',
      });
      router.reload();
    });
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
      {contest.owner.type === 'GROUP' &&
        <div className={styles['header-owner']}>
          <Link href={`/groups/${contest.owner.id}`}><IconUserGroup />{contest.owner.name}</Link>
        </div>
      }
      <Row className={styles['contest-header-time']}>
        <Col md={8}>
          <div>
            <strong>{t['header.start']}</strong> {FormatTime(contest.startTime)}
          </div>
        </Col>
        <Col md={8}>
          <div style={{textAlign: 'center'}}>
            <strong>{t['header.now']}</strong> {FormatTime(currentTime)}
            {contest.virtualStart !== null && contest.runningStatus !== 'FINISHED' && (
              <>
                <sup>虚拟</sup>
                <Divider type='vertical' />
                <Popconfirm
                  focusLock
                  title='退出虚拟竞赛'
                  content='你确定提前退出吗？退出后将不可再次进入虚拟竞赛'
                  onOk={() => onExitVirtualContest() }
                  onCancel={() => {
                    Message.error({
                      content: 'cancel',
                    });
                  }}
                >
                  <Button size='mini'>退出虚拟</Button>
                </Popconfirm>
              </>
            )}
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

function ContestLayout(page) {
  const t = useLocale(locale);
  const [contest, setContest] = useState({
    id: 0,
    name: '',
    startTime: new Date(),
    endTime: new Date(),
    privacy: '',
    membership: '',
    role: 'GUEST',
    type: '',
    groupId: 0,
    participantCount: 0,
    runningStatus: '',
    invitationCode: '',
    description: '',
    virtualStart: null,
    feature: '',
    owner: {
      id: 0,
      type: '',
      name: '',
    },
    problems: [],
  });
  const [loading, setLoading] = useState(true);
  const [collapsed, setCollapsed] = useState(false);
  const [siderWidth, setSiderWidth] = useState(normalWidth);
  const [problems, setProblems] = useState([]);
  const [menuSelected, setMenuSelected] = useState('');
  const [problemNumber, setProblemNumber] = useState('A');
  const settings = useAppSelector<SettingState>(setting);
  const router = useRouter();
  const fetchData = () => {
    setLoading(true);
    getContest(router.query.id)
      .then((res) => {
        const { data } = res;
        setContest(data);
        if ((data.role !== 'ROLE_GUEST' && data.runningStatus !== 'NOT_STARTED') || (data.privacy === 'PUBLIC' && data.runningStatus === 'FINISHED')) {
          setMenuSelected('');
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
    if (key === 'info') {
      key = '';
    }
    router.push(`/contests/${contest.id}/${key}`);
  };

  const changeProblem = (number:string) => {
    if (number !== '') {
      setMenuSelected('problem');
      setProblemNumber(number);
    }
  };

  const updateContest = (newContestData) => {
    setContest(newContestData);
  };

  return (
    <MainLayout>
      {!loading &&
        <ContestContext.Provider value={{...contest, problems: problems, changeProblem: changeProblem, updateContest}}>
          <div className={styles['contest-layout-basic']}>
            <Head>
              <title>{`${contest.name} - ${settings.name}`}</title>
            </Head>
            <Layout style={{height: '100%'}}>
              <ContestHeader />
              {
                ((contest.role === 'ROLE_GUEST' && (contest.privacy === 'PRIVATE' || contest.runningStatus !== 'FINISHED')) ||
                (contest.role !== 'ROLE_ADMIN' && contest.runningStatus === 'NOT_STARTED')) ? (
                  <Forbidden />
                ) : (
                  <Layout style={{height: '100%'}}>
                    <Sider
                      collapsible
                      theme='light'
                      className={menuSelected === 'problem' ? styles['sider-problem'] : ''}
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
                        {contest.runningStatus === 'FINISHED' && <MenuItem key='editorial'><IconBook /> {t['menu.editorial']}</MenuItem>}
                        {contest.role === 'ROLE_ADMIN' && <MenuItem key='setting'><IconSettings /> {t['menu.setting']}</MenuItem>}
                        <SubMenu
                          key='problem'
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
                      {page}
                    </Content>
                  </Layout>
                )
              }
            </Layout>
          </div>
        </ContestContext.Provider>
      }
    </MainLayout>
  );
}

export default ContestLayout;
