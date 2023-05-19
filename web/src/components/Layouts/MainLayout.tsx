import React, { ReactNode } from 'react';
import { Divider, Layout, Link } from '@arco-design/web-react';
import LayoutHeader from './LayoutHeader';
import styles from './style/main-layouts.module.less';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';

const { Header, Footer, Content } = Layout;

const App = ({ children }: { children: ReactNode }) => {
  const settings = useAppSelector<SettingState>(setting);
  return (
    <Layout className={styles.layout}>
      <Header>
        <LayoutHeader />
      </Header>
      <Content className={styles.main}>
        {children}
      </Content>
      <Footer className={styles.footer}>
        <div className='container'>
          <div className={styles['footer-layout']}>
            <div>
              &copy; {new Date().getFullYear()} {settings.name}
              <Divider type='vertical' />
              Powered by <a href='https://github.com/shi-yang/jnoj' target="_blank" rel='noreferrer'>jnoj</a>
              {settings.beian && (
                <>
                  <Divider type='vertical' />
                  <a href="https://beian.miit.gov.cn" target="_blank" rel='noreferrer'>
                    {settings.beian}
                  </a>
                </>
              )}
            </div>
            <Link href='/submissions'>测评队列</Link>
          </div>
        </div>
      </Footer>
    </Layout>
  );
};

export default React.memo(App);
