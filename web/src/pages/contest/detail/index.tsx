import { useEffect, useState } from 'react';
import { Layout, Menu, Typography, Grid, Slider } from '@arco-design/web-react';
import { IconHome, IconOrderedList, IconFile, IconSelectAll, IconSettings } from '@arco-design/web-react/icon';
import styles from './style/index.module.less';
import { getContest, listContestProblems } from '@/api/contest';
import { Route, Routes, useNavigate, useParams } from 'react-router-dom';
import Info from './info';
import Problem from './problem';
import Standings from './standings';
import Submission from './submission';
import Setting from './setting';

import './mock';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { FormatTime } from '@/utils/formatTime';
const MenuItem = Menu.Item;
const SubMenu = Menu.SubMenu;
const Sider = Layout.Sider;
const Header = Layout.Header;
const Content = Layout.Content;
const Row = Grid.Row;
const Col = Grid.Col;
const collapsedWidth = 60;
const normalWidth = 220;

function App() {
  const t = useLocale(locale);
  const [data, setData] = useState({name: '', startTime: new Date(), endTime: new Date()});
  const [loading, setLoading] = useState(true);
  const [collapsed, setCollapsed] = useState(false);
  const [siderWidth, setSiderWidth] = useState(normalWidth);
  const [problems, setProblems] = useState([]);
  const [currentTime, setCurrentTime] = useState(new Date());
  const [sliderValue, setSliderValue] = useState(0);
  const params = useParams();
  const navigate = useNavigate();

  let timer = null;
  let contestDuration = 0;
  function fetchData() {
    setLoading(true);
    getContest(params.id)
      .then((res) => {
        const { data } = res;
        setData(data);
        updateTime(data.startTime, data.endTime)
      })
      .finally(() => {
        setLoading(false);
      });
    listContestProblems(params.id).then(res => {
      setProblems(res.data.data)
    })
  }

  function updateTime(startTime, endTime) {
    contestDuration = new Date(endTime).getTime() - new Date(startTime).getTime()
    timer = setInterval(() => {
      const t = new Date();
      const diff = t.getTime() - new Date(startTime).getTime()
      setSliderValue(diff / contestDuration * 100)
      setCurrentTime(new Date())
    }, 1000)
  }

  useEffect(() => {
    fetchData();
    return () => {
      clearInterval(timer)
    }
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

  const handleMenuClick = (key) => {
    navigate(`/contests/${params.id}/${key}`)
  }

  return (
    (!loading &&
      <div className={styles['contest-layout-basic']}>
        <Layout style={{height: '100%'}}>
          <Header>
            <Typography.Title className={styles.title}>{data.name}</Typography.Title>
            <Row style={{padding: '20px 20px 0 20px'}}>
              <Col md={8}>
                <div>
                  <strong>{t['header.start']}</strong> {FormatTime(data.startTime)}
                </div>
              </Col>
              <Col md={8}>
                <div style={{textAlign: 'center'}}><strong>{t['header.now']}</strong>{FormatTime(currentTime)}</div>
              </Col>
              <Col md={8} style={{textAlign: 'right'}}>
                <div>
                  <strong>{t['header.end']}</strong> {FormatTime(data.endTime)}
                </div>
              </Col>
            </Row>
            <Slider defaultValue={sliderValue} />
          </Header>
          <Layout style={{height: '100%'}}>
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
                <MenuItem key='setting'><IconSettings /> {t['menu.setting']}</MenuItem>
                <SubMenu
                  key='layout'
                  title={<span><IconSelectAll /> {t['menu.problem']}</span>}
                >
                  {problems.map(value => {
                    return <MenuItem key={`problem/${String.fromCharCode(65 + value.number)}`}>{String.fromCharCode(65 + value.number)}. {value.name}</MenuItem>
                  })}
                </SubMenu>
              </Menu>
            </Sider>
            <Content style={{ padding: '30px' }}>
              <Routes>
                <Route index element={ <Info contest={data} /> }></Route>
                <Route path='setting' element={ <Setting contest={data} /> }></Route>
                <Route path='info' element={ <Info contest={data} /> }></Route>
                <Route path='problem/:key' element={ <Problem contest={data} /> }></Route>
                <Route path='standings' element={ <Standings /> }></Route>
                <Route path='submission' element={ <Submission /> }></Route>
              </Routes>
            </Content>
          </Layout>
        </Layout>
      </div>
    )
  );
}

export default App;
