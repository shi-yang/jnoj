import { getLastSubmission, getSubmission } from '@/api/submission';
import SubmissionDrawer from '@/components/Submission/SubmissionDrawer';
import SubmissionVerdict from '@/components/Submission/SubmissionVerdict';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import useLocale from '@/utils/useLocale';
import { Button, Popover, Spin } from '@arco-design/web-react';
import { IconCheckCircle, IconCloseCircle } from '@arco-design/web-react/icon';
import React, { useRef, useState, useEffect } from 'react';
import locale from './locale';

interface RecentlySubmittedProps {
  problemId: number,
  lastSubmissionID: number,
  entityId?: number,
  entityType?: number,
}

const RecentlySubmitted = React.memo((props: RecentlySubmittedProps) => {
  const t = useLocale(locale);
  const ws = useRef<WebSocket | null>(null);
  const [submission, setSubmission] = useState({ id: 0, verdict: 0 });
  const [visible, setVisible] = useState(false);
  const [isRunning, setIsRunning] = useState(false);
  const [btnContent, setBtnContent] = useState('');
  const user = useAppSelector(userInfo);
  // websocket 即时向用户反馈测评进度
  useEffect(() => {
    ws.current = new WebSocket(process.env.NEXT_PUBLIC_API_WS_URL + '?uid=' + user.id);
    ws.current.onmessage = (e) => {
      if (e.data === '') {
        return;
      }
      const msg = JSON.parse(e.data);
      if (msg.type === 'SUBMISSION_RESULT') {
        if (msg.message.status === 'running') {
          setBtnContent(msg.message.message);
          setIsRunning(true);
        } else {
          getSubmission(msg.message.sid)
            .then(res => {
              setIsRunning(false);
              setSubmission(res.data);
              setBtnContent('');
            });
        }
      }
    };
    return () => {
      ws.current?.close();
    };
  }, [ws]);
  function icon() {
    if (isRunning) {
      return <Spin />;
    } else if (submission.verdict === 4) {
      return <IconCheckCircle />;
    }
    return <IconCloseCircle />;
  }
  useEffect(() => {
    if (props.lastSubmissionID && props.lastSubmissionID !== 0) {
      setIsRunning(true);
      getSubmission(props.lastSubmissionID)
        .then(res => {
          if (res.data.verdict !== 0) {
            setIsRunning(false);
          }
          setSubmission(res.data);
        });
    } else {
      getLastSubmission({
        entityId: props.entityId,
        entityType: props.entityType,
        problemId: props.problemId,
      }).then(res => {
        setSubmission(res.data);
      }).catch(err => {
        setSubmission({id: 0, verdict: 0});
      });
    }
  }, [props.entityId, props.entityType, props.problemId, props.lastSubmissionID]);

  function onCancel() {
    setVisible(false);
  }
  return (
    submission.id !== 0 &&
    <Popover
      trigger='hover'
      title={t['recentlySubmitted']}
      content={
        <span>
          <p>{t['submissionID']}: {submission.id}</p>
          <p>{t['verdict']}: <SubmissionVerdict verdict={submission.verdict} /></p>
        </span>
      }
    >
      <Button type='dashed' icon={icon()} onClick={() => { setVisible(true); }}>
        {btnContent === '' && <SubmissionVerdict verdict={submission.verdict} />}
        {btnContent !== '' && <span>{btnContent}</span>}
      </Button>
      {visible && <SubmissionDrawer id={submission.id} visible={visible} onCancel={onCancel} />}
    </Popover>
  );
});

RecentlySubmitted.displayName = 'RecentlySubmitted';

export default RecentlySubmitted;
