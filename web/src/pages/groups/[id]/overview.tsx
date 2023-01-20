import { Card } from '@arco-design/web-react';
import ContestList from '@/modules/contest/list'
export default ({group}) => {
  return (
    <Card>
      <ContestList groupId={group.id} />
    </Card>
  );
}
