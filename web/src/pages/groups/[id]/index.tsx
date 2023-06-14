import React, { useContext } from 'react';
import { Card } from '@arco-design/web-react';
import ContestList from '@/modules/contest/list';
import Layout from './Layout';
import Groups from './groups';
import context from './context';
function Overview() {
  const group = useContext(context);
  return (
    <Card>
      {group.type === 'GROUP' ?
        <ContestList groupId={group.id} />
        :
        <Groups />
      }
    </Card>
  );
}

Overview.getLayout = Layout;
export default Overview;
