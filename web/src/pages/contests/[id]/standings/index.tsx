import React, { useContext } from 'react';
import ContestContext from '../context';
import Standings from './standings';
import { Tabs } from '@arco-design/web-react';
import Rating from './rating';
import ContestLayout from '../Layout';

function Index() {
  const contest = useContext(ContestContext);
  if (contest.feature.includes('rated') && contest.runningStatus === 'FINISHED') {
    return (
      <Tabs key='card' destroyOnHide>
        <Tabs.TabPane key='standings' title='榜单'>
          <Standings />
        </Tabs.TabPane>
        <Tabs.TabPane key='rated' title='等级分变化'>
          <Rating />
        </Tabs.TabPane>
      </Tabs>
    );
  } else {
    return <Standings />;
  }
}

Index.getLayout = ContestLayout;

export default Index;
