import React from 'react';
import { Divider, Layout } from '@arco-design/web-react';
import LayoutHeader from './LayoutHeader';
import styles from './style/main-layouts.module.less'
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';

const { Header, Footer, Content } = Layout;

const App = ({ children }) => {
  const settings = useAppSelector<SettingState>(setting)
  return (
    <Layout className={styles.layout}>
      <Header>
        <LayoutHeader />
      </Header>
      <Content>
        {children}
      </Content>
      <Footer className={styles.footer}>
        <div className='container'>
          <div style={{padding: '15px 0'}}>
            &copy; 2022 {settings.name} 
            {settings.beian && (
              <>
                <Divider type='vertical' />
                <a href="https://beian.miit.gov.cn" target="_blank">
                  {settings.beian}
                </a>
              </>
            )}
          </div>
        </div>
      </Footer>
    </Layout>
  );
};

export default React.memo(App);
