import { useEffect, useState } from 'react';
import { Layout, Menu, Progress, Typography, Grid, Slider } from '@arco-design/web-react';
import { IconHome, IconCalendar } from '@arco-design/web-react/icon';
import styles from './style/index.module.less';
import { getContest } from '@/api/contest';
import { Route, Routes, useNavigate, useParams } from 'react-router-dom';
import Info from './info';
import Problem from './problem';
import Standings from './standings';

import './mock';
import useLocale from '@/utils/useLocale';
import locale from './locale';
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
  const [data, setData] = useState({title: '', start_time: '', end_time: ''});
  const [loading, setLoading] = useState(true);
  const [collapsed, setCollapsed] = useState(false);
  const [siderWidth, setSiderWidth] = useState(normalWidth);
  const params = useParams();
  const navigate = useNavigate();

  function fetchData() {
    setLoading(true);
    getContest(params.id)
      .then((res) => {
        console.log(res)
        setData(res.data);
      })
      .finally(() => {
        setLoading(false);
      });
  }

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

  const handleMenuClick = (key) => {
    navigate(`/contests/${params.id}/${key}`)
  }

  return (
    <div className={styles['contest-layout-basic']}>
      <Layout style={{height: '100%'}}>
        <Header>
          <Typography.Title className={styles.title}>{data.title}</Typography.Title>
          <Row style={{padding: '20px 20px 0 20px'}}>
            <Col md={8}>
              <div>
                <strong>开始</strong> {data.start_time}
              </div>
            </Col>
            <Col md={8}>
              <div style={{textAlign: 'center'}}><strong>当前</strong> {data.end_time}</div>
            </Col>
            <Col md={8} style={{textAlign: 'right'}}>
              <div>
                <strong>结束</strong> {data.end_time}
              </div>
            </Col>
          </Row>
          <Slider defaultValue={20} />
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
              <MenuItem key='info'>
                <IconHome />
                信息
              </MenuItem>
              <MenuItem key='standings'>
                <IconCalendar />
                榜单
              </MenuItem>
              <MenuItem key='status'>
                <IconCalendar />
                状态
              </MenuItem>
              <SubMenu
                key='layout'
                title={
                  <span>
                    <IconCalendar /> 题目
                  </span>
                }
              >
                <MenuItem key='11'>栅格</MenuItem>
                <MenuItem key='12'>分隔符</MenuItem>
              </SubMenu>
            </Menu>
          </Sider>
          <Content style={{ textAlign: 'center', padding: '30px' }}>
            <Routes>
              <Route index element={ <Info /> }></Route>
              <Route path='info' element={ <Info /> }></Route>
              <Route path='problem' element={ <Problem /> }></Route>
              <Route path='standings' element={ <Standings /> }></Route>
            </Routes>
          </Content>
        </Layout>
      </Layout>
    </div>
  );
}

export default App;
