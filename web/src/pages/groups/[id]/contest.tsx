import React, { useContext } from 'react';
import { Card } from '@arco-design/web-react';
import ContestList from '@/modules/contest/list';
import Layout from './Layout';
import context from './context';
function Overview() {
  const group = useContext(context);
  return (
    <Card>
      <ContestList groupId={group.id} />
    </Card>
  );
}

Overview.getLayout = Layout;
export default Overview;
