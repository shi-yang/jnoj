import React from 'react';
import useLocale from '@/utils/useLocale';
import { Tabs } from '@arco-design/web-react';
import Info from './info';
import locale from '../locale';
import Users from './users';
import Editorial from './editorial';

const Setting = ({contest}: any) => {
  const t = useLocale(locale);
  return (
    <Tabs defaultActiveTab='info'>
      <Tabs.TabPane key='info' title={t['setting.tab.info']} destroyOnHide>
        <Info />
      </Tabs.TabPane>
      <Tabs.TabPane key='user' title={t['setting.tab.users']} destroyOnHide>
        <Users />
      </Tabs.TabPane>
      <Tabs.TabPane key='editorial' title={t['setting.tab.editorial']} destroyOnHide>
        <Editorial />
      </Tabs.TabPane>
    </Tabs>
  );
};
export default Setting;
