import { createContestUser } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import { Button, Typography, Modal, Divider, Input, Message } from '@arco-design/web-react';
import { useRouter } from 'next/router';
import React, { useState } from 'react';
import locale from './locale';

export default function RegisterContest({contest}) {
  const t = useLocale(locale);
  const router = useRouter();
  const [code, setCode] = useState('');
  const [visible, setVisible] = useState(false);
  function onRegister() {
    const data = {
      invitationCode: code
    };
    createContestUser(contest.id, data)
      .then(() => {
        Message.success(t['forbidden.registrationFuccessfully']);
        router.reload();
      })
      .catch((res) => {
        Message.error(t['forbidden.registrationFailed'] + ':' + res.response.data.message);
      });
  }
  return (
    <>
      {
        contest.runningStatus === 'FINISHED' ? (
          <Button type='outline' onClick={() => setVisible(true)}>
            虚拟参赛
          </Button>
        ) : (
          <Button type='primary' onClick={() => setVisible(true)}>
            参赛
          </Button>
        )
      }
      <Modal
        title='参赛须知'
        visible={visible}
        footer={null}
        onCancel={() => setVisible(false)}
      >
        <Typography.Title heading={5}>以下行为将被判定为违规参赛</Typography.Title>
        <Typography.Paragraph>1. 一人使用多个账号提交</Typography.Paragraph>
        <Typography.Paragraph>2. 多账号提交雷同代码</Typography.Paragraph>
        <Typography.Paragraph>3. 比赛未结束前与其他人分享解决方案，包括但不限于在讨论区、其它平台公开分享答案</Typography.Paragraph>
        <Typography.Paragraph>4. 以任何形式破坏和攻击测评系统</Typography.Paragraph>
        <Typography.Paragraph>当选手被评定为违规行为时将视影响程度封号3-365天不等的处罚</Typography.Paragraph>
        {
          contest.membership === 'INVITATION_CODE'
          &&
          <div>
            <Divider />
            <Typography.Paragraph>{t['join.invitationCodeMsg']}</Typography.Paragraph>
            <Input style={{ width: 350, marginBottom: '20px' }} prefix={t['join.invitationCode']} onChange={setCode}  />
          </div>
        }
        <Button type='primary' onClick={onRegister}>同意以上并参加</Button>
      </Modal>
    </>
  );
}
