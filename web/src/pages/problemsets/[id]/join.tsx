import React, { useState } from 'react';
import { Result, Button, Input, Card, Message, Divider, PageHeader } from '@arco-design/web-react';
import { IconFaceSmileFill } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { userInfo } from '@/store/reducers/user';
import { useAppSelector } from '@/hooks';
import { useRouter } from 'next/router';
import { createProblemsetUser } from '@/api/problemset';
function InvitationCode({id}: {id: number}) {
  const user = useAppSelector(userInfo);
  const t = useLocale(locale);
  const [code, setCode] = useState('');
  const router = useRouter();
  function join() {
    const data = {
      userId: user.id,
      invitationCode: code
    };
    createProblemsetUser(id, data)
      .then(res => {
        Message.success('加入成功');
        router.reload();
      })
      .catch(() => {
        Message.error('加入失败');
      });
  }
  return (
    <div>
      <Input style={{ width: 350 }} prefix={t['invitationCode']} onChange={setCode}  />
      <Button type='primary' onClick={join}>加入</Button>
    </div>
  );
}

function Page({problemset}: {problemset: any}) {
  const t = useLocale(locale);
  return (
    <>
      <PageHeader title={problemset.name} style={{ background: 'var(--color-bg-2)' }}>
        {problemset.description}
      </PageHeader>
      <Divider />
      <Card>
        <Result
          status='success'
          icon={<IconFaceSmileFill />}
          title={'私有题单'}
          extra={
            <InvitationCode id={problemset.id} />
          }
        >
        </Result>
      </Card>
    </>
  );
}

export default Page;
