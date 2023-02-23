import React from 'react';
import { Card } from '@arco-design/web-react';
import ContestList from '@/modules/contest/list';
export default function Overview({group}: {group: {id: number}}) {
  return (
    <Card>
      <ContestList groupId={group.id} />
    </Card>
  );
}
