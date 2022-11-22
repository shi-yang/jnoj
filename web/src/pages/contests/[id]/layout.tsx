import { useEffect, useState } from 'react';
import { Layout, Menu, Typography, Grid, Slider } from '@arco-design/web-react';
import { IconHome, IconOrderedList, IconFile, IconSelectAll, IconSettings } from '@arco-design/web-react/icon';
import styles from './style/index.module.less';

import './mock';
import { useRouter } from 'next/router';
const MenuItem = Menu.Item;
const SubMenu = Menu.SubMenu;
const Sider = Layout.Sider;
const Header = Layout.Header;
const Content = Layout.Content;
const Row = Grid.Row;
const Col = Grid.Col;
const collapsedWidth = 60;
const normalWidth = 220;

function ContestLayout({children}) {
  return (
      <div className={styles['contest-layout-basic']}>
        <Layout style={{height: '100%'}}>
          <Layout style={{height: '100%'}}>
            <Content style={{ padding: '30px' }}>
              {children}
            </Content>
          </Layout>
        </Layout>
      </div>
  );
}

export default ContestLayout;
