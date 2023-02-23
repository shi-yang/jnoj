import React from 'react';
import { createContestUser } from '@/api/contest';
import { Button, Divider, Empty, Message, Statistic, Typography } from '@arco-design/web-react';
import { IconExclamation } from '@arco-design/web-react/icon';
import dayjs from 'dayjs';
import { useRouter } from 'next/router';

const now = Date.now();
export default function Forbidden({contest}: {
  contest: {id: number, runningStatus: string, status: string, startTime: string | number | Date | dayjs.Dayjs, role: string}
}) {
  const router = useRouter();
  function register() {
    createContestUser(contest.id)
      .then(() => {
        Message.success('注册成功');
        router.push(`/contests/${contest.id}`);
      });
  }
  return (
    <div>
      <Empty
        icon={
          <div
            style={{
              background: '#f2994b',
              display: 'inline-flex',
              borderRadius: '50%',
              width: 50,
              height: 50,
              fontSize: 30,
              alignItems: 'center',
              color: 'white',
              justifyContent: 'center',
            }}
          >
            <IconExclamation />
          </div>
        }
        description={
          <>
            { contest.runningStatus === 'NOT_STARTED' && (
              <Statistic.Countdown
                value={dayjs(contest.startTime)}
                format='D 天 H 时 m 分 s 秒'
                now={now}
              />
            )}
            {(contest.status === 'HIDDEN' && <>该比赛仅参赛人员可见。</>)
            || (contest.status === 'PRIVATE' && <>该比赛仅参赛人员可见。</>)
            || (contest.status === 'PUBLIC' && contest.role === 'GUEST' &&
              <div>
                您尚未报名参加该比赛，请先参赛，或比赛结束后再来访问
                <Divider>参赛协议：</Divider>
                <Typography.Paragraph>1. 不与其他人分享解决方案</Typography.Paragraph>
                <Typography.Paragraph>2. 不以任何形式破坏和攻击测评系统</Typography.Paragraph>
                <Typography.Paragraph>违反以上规则将视影响程度封号3-365天不等的处罚</Typography.Paragraph>
                <Button onClick={register}>同意以上并参加</Button>
              </div>
            )}
          </>
        }
      />
    </div>
  );
}
