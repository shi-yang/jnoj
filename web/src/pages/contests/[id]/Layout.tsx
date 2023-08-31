import React, { useContext, useEffect, useState } from 'react';
import { Layout, Typography, Grid, Slider, Statistic, Link, Popconfirm, Message, Button, Divider, Tooltip, Select, Tabs } from '@arco-design/web-react';
import { IconHome, IconOrderedList, IconFile, IconSelectAll, IconUserGroup, IconLanguage, IconMoonFill, IconSunFill, IconBook, IconSettings } from '@arco-design/web-react/icon';
import styles from './style/index.module.less';
import { exitVirtualContest, getContest, listContestProblems } from '@/api/contest';
import { getUserInfo } from '@/store/reducers/user';
import './mock';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import Logo from '@/assets/logo.png';
import { FormatTime } from '@/utils/format';
import { useRouter } from 'next/router';
import { useAppDispatch, useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import ContestContext from './context';
import dayjs from 'dayjs';
import Forbidden from './forbidden';
import { GlobalContext } from '@/context';
import IconButton from '@/components/Layouts/IconButton';
import UserAvatar from '@/components/Layouts/UserAvatar';
import { isLogged } from '@/utils/auth';
import defaultLocale from '@/locale';

const Header = Layout.Header;
const Content = Layout.Content;
const Row = Grid.Row;
const Col = Grid.Col;

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
  const dispatch = useAppDispatch();
  const [isMounted, setIsMounted] = useState(false);
  const { lang, setLang, theme, setTheme } = useContext(GlobalContext);
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
  const [problems, setProblems] = useState([]);
  const [activeTab, setActiveTab] = useState('info');
  const settings = useAppSelector<SettingState>(setting);
  const router = useRouter();
  const fetchData = () => {
    setLoading(true);
    getContest(router.query.id)
      .then((res) => {
        const { data } = res;
        setContest(data);
        if ((data.role !== 'ROLE_GUEST' && data.runningStatus !== 'NOT_STARTED') || (data.privacy === 'PUBLIC' && data.runningStatus === 'FINISHED')) {
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
    const r = router.pathname.split('/');
    if (r.length === 4) {
      setActiveTab(r[3]);
    }
    setIsMounted(true);
    dispatch(getUserInfo());
    fetchData();
  }, []);

  const handleMenuClick = (key:string) => {
    if (key === 'info') {
      key = '';
    }
    router.push(`/contests/${contest.id}/${key}`);
  };

  return (
    <Layout>
      <Head>
        <title>{`${contest.name} - ${settings.name}`}</title>
      </Head>
      <Header>
        <div className={styles.navbar}>
          <div className={styles.left}>
            <Link href='/'>
              <img style={{height: 21,  cursor: 'pointer' }} src={Logo.src} alt='logo' />
            </Link>
          </div>
          <div className={styles.title}>
            <Typography.Title heading={2}>{contest.name}</Typography.Title>
            {contest.owner.type === 'GROUP' &&
              <div className={styles['header-owner']}>
                <Link href={`/groups/${contest.owner.id}`}><IconUserGroup />{contest.owner.name}</Link>
              </div>
            }
          </div>
          <ul className={styles.right}>
            <li>
              <Select
                triggerElement={<IconButton icon={<IconLanguage />} />}
                options={[
                  { label: '中文', value: 'zh-CN' },
                  { label: 'English', value: 'en-US' },
                ]}
                value={lang}
                triggerProps={{
                  autoAlignPopupWidth: false,
                  autoAlignPopupMinWidth: true,
                  position: 'br',
                }}
                trigger="hover"
                onChange={(value) => {
                  setLang(value);
                  const nextLang = defaultLocale[value];
                  Message.info(`${nextLang['message.lang.tips']}${value}`);
                }}
              />
            </li>
            <li>
              <Tooltip
                content={
                  theme === 'light'
                    ? t['settings.navbar.theme.toDark']
                    : t['settings.navbar.theme.toLight']
                }
              >
                <IconButton
                  icon={theme !== 'dark' ? <IconMoonFill /> : <IconSunFill />}
                  onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')}
                />
              </Tooltip>
            </li>
            { isMounted && (isLogged()
                ? <li>
                  <UserAvatar />
                </li>
                : <li>
                  <Link href='/user/login'>{ t['login'] }</Link>
                </li>)
            }
          </ul>
        </div>
      </Header>
      <Content className='container'>
        {!loading &&
          <ContestContext.Provider value={{...contest, problems: problems, updateContest: setContest}}>
            <div className={styles['contest-layout-basic']}>
              <Layout style={{height: '100%'}}>
                <ContestHeader />
                <Tabs defaultActiveTab='info' activeTab={activeTab} size='large' onClickTab={handleMenuClick} onChange={setActiveTab}>
                  <Tabs.TabPane key='info' title={<span><IconHome /> {t['menu.info']}</span>}></Tabs.TabPane>
                  <Tabs.TabPane key='problem/A' title={<span><IconSelectAll /> {t['menu.problem']}</span>}></Tabs.TabPane>
                  <Tabs.TabPane key='submission' title={<span><IconFile /> {t['menu.submission']}</span>}></Tabs.TabPane>
                  <Tabs.TabPane key='standings' title={<span><IconOrderedList /> {t['menu.standings']}</span>}></Tabs.TabPane>
                  {contest.runningStatus === 'FINISHED' && <Tabs.TabPane key='editorial' title={<span><IconBook /> {t['menu.editorial']}</span>}></Tabs.TabPane>}
                  {contest.role === 'ROLE_ADMIN' && <Tabs.TabPane key='setting' title={<span><IconSettings /> {t['menu.setting']}</span>}></Tabs.TabPane>}
                </Tabs>
                {
                  ((contest.role === 'ROLE_GUEST' && (contest.privacy === 'PRIVATE' || contest.runningStatus !== 'FINISHED')) ||
                  (contest.role !== 'ROLE_ADMIN' && contest.runningStatus === 'NOT_STARTED')) ? (
                    <Forbidden />
                  ) : (
                    <Layout style={{height: '100%'}}>
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
      </Content>
    </Layout>
  );
}

export default ContestLayout;
