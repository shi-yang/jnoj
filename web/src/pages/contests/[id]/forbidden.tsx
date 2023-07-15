import React, { useContext } from 'react';
import { Divider, Result, Statistic, Typography } from '@arco-design/web-react';
import { IconFaceSmileFill } from '@arco-design/web-react/icon';
import dayjs from 'dayjs';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import ContestContext from './context';
import ReactMarkdown from 'react-markdown';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import rehypeHighlight from 'rehype-highlight';
import RegisterContest from '@/modules/contest/RegisterContest';

const now = Date.now();

function Forbidden() {
  const contest = useContext(ContestContext);
  const t = useLocale(locale);
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
              contest.role === 'ROLE_GUEST' && (
                <>
                  <p>需要参赛才能查看比赛信息。您尚未报名参加该比赛，请点击 <RegisterContest contest={contest} />，开始竞赛</p>
                  {contest.membership === 'GROUP_USER' && (
                    <p><Typography.Text type='error' bold>当前比赛仅小组成员才能参加，如果你不是本小组成员，将参加失败</Typography.Text></p>
                  )}
                  {contest.runningStatus === 'FINISHED' && (
                    <Typography.Text type='error' bold>请注意：由于该比赛当前已经结束，点击参赛您将<strong>立即</strong>开始虚拟竞赛，在虚拟竞赛中，将重现本次比赛的竞赛过程</Typography.Text>
                  )}
                </>
              )
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

export default Forbidden;
