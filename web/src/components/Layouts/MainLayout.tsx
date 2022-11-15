import React from 'react';
import { Layout } from '@arco-design/web-react';
import { Outlet } from 'react-router-dom';
import LayoutHeader from './LayoutHeader';
import styles from './style/main-layouts.module.less'

const { Header, Footer, Content } = Layout;

const App = () => {
  return (
    <Layout className={styles.layout}>
      <Header>
        <LayoutHeader />
      </Header>
      <Content>
        <Outlet />
      </Content>
      <Footer className={styles.footer}>
        <div className='container'>
          <div style={{padding: '15px 0'}}>©2022 JNOJ</div>
        </div>
      </Footer>
    </Layout>
  );
};

export default React.memo(App);
