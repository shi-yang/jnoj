import React, { useContext, useState } from 'react';
import { createContestUser } from '@/api/contest';
import { Button, Divider, Input, Message, Modal, Result, Statistic, Typography } from '@arco-design/web-react';
import { IconFaceSmileFill } from '@arco-design/web-react/icon';
import dayjs from 'dayjs';
import { useRouter } from 'next/router';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import ContestContext from './context';
import ReactMarkdown from 'react-markdown';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import rehypeHighlight from 'rehype-highlight';

const now = Date.now();

export default function Forbidden() {
  const contest = useContext(ContestContext);
  const router = useRouter();
  const t = useLocale(locale);
  const [code, setCode] = useState('');
  const [visible, setVisible] = useState(false);
  function register() {
    const data = {
      invitationCode: code
    };
    createContestUser(contest.id, data)
      .then(() => {
        Message.success(t['forbidden.registrationFuccessfully']);
        router.reload();
      })
      .catch(() => {
        Message.error(t['forbidden.registrationFailed']);
      });
  }
  return (
    <div>
      <Result
        icon={<IconFaceSmileFill />}
        title={contest.privacy === 'PRIVATE' ? t['forbidden.title.private'] : t['forbidden.title.public']}
        extra={
          <div>
            { contest.runningStatus === 'NOT_STARTED' && (
              <Statistic.Countdown
                value={dayjs(contest.startTime)}
                format='D 天 H 时 m 分 s 秒'
                now={now}
              />
            )}
            {
              contest.role === 'ROLE_GUEST'
              && <>
                {
                  <div>
                    <p>私有比赛需要参赛才能查看比赛信息。您尚未报名参加该比赛，请点击 <Button type='primary' onClick={() => setVisible(true)}>参赛</Button>，开始竞赛</p>
                    {contest.runningStatus === 'FINISHED' && (
                      <Typography.Text type='error' bold>请注意：由于该比赛当前已经结束，点击参赛您将<strong>立即</strong>开始虚拟竞赛，在虚拟竞赛中，将重现本次比赛的竞赛过程</Typography.Text>
                    )}
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
                      <Button type='primary' onClick={register}>同意以上并参加</Button>
                    </Modal>
                  </div>
                }
              </>
            }
            <Divider />
            <div className='container' style={{maxWidth: '1200px'}}>
              <Typography.Text style={{textAlign: 'left'}}>
                <ReactMarkdown
                  remarkPlugins={[remarkMath]}
                  rehypePlugins={[rehypeKatex, rehypeHighlight]}
                >
                  {contest.description}
                </ReactMarkdown>
              </Typography.Text>
            </div>
          </div>
        }
      />
    </div>
  );
}
