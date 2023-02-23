import React, { useContext, useState } from 'react';
import { Result, Button, Input, Card, Message } from '@arco-design/web-react';
import { IconFaceSmileFill } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createGroupUser } from '@/api/group';
import { userInfo } from '@/store/reducers/user';
import { useAppSelector } from '@/hooks';
import { useRouter } from 'next/router';
import Layout from './Layout';
import context from './context';
function InvitationCode({id, membership}: {id: number, membership: number}) {
  const user = useAppSelector(userInfo);
  const t = useLocale(locale);
  const [code, setCode] = useState('');
  const router = useRouter();
  function join() {
    const data = {
      gid: id,
      uid: user.id,
      invitationCode: code
    };
    createGroupUser(id, data)
      .then(res => {
        Message.success('加入成功');
        router.push(`/groups/${id}`);
      })
      .catch(() => {
        Message.error('加入失败');
      });
  }
  return (
    <div>
      {membership === 1 && <Input style={{ width: 350 }} prefix={t['invitationCode']} onChange={setCode}  />}
      <Button type='primary' onClick={join}>加入</Button>
    </div>
  );
}

function Join() {
  const group = useContext(context);
  const t = useLocale(locale);
  return (
    <Card>
      <Result
        status='success'
        icon={<IconFaceSmileFill />}
        title={group.privacy === 0 ? t['privateGroup'] : t['publicGroup']}
        subTitle={group.membership === 1 && t['joinMsg']}
        extra={
          <InvitationCode id={group.id} membership={group.membership} />
        }
      >
      </Result>
    </Card>
  );
}

Join.getLayout = Layout;
export default Join;
