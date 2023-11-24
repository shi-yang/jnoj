import { getLastSubmission, getSubmission } from '@/api/submission';
import SubmissionDrawer from './SubmissionDrawer';
import SubmissionVerdict from './SubmissionVerdict';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import useLocale from '@/utils/useLocale';
import { Button, Popover, Spin } from '@arco-design/web-react';
import { IconCheckCircle, IconCloseCircle } from '@arco-design/web-react/icon';
import React, { useRef, useState, useEffect } from 'react';
import locale from './locale';
import SubmissionModalAnimation from './SubmissionModalAnimation';
import ContestAnimationModal from '../contest/ContestAnimationModal';
import getConfig from "next/config";
const { publicRuntimeConfig } = getConfig();

interface RecentlySubmittedProps {
  problemId: number,
  lastSubmissionID: number,
  animation?: boolean,
  entityId?: number,
  entityType?: number,
}

// 重连间隔时间，单位毫秒
const wsReconnectInterval = 2000; 

const RecentlySubmitted = React.memo((props: RecentlySubmittedProps) => {
  const t = useLocale(locale);
  const ws = useRef<WebSocket | null>(null);
  const [submission, setSubmission] = useState({ id: 0, verdict: 0 });
  const [visible, setVisible] = useState(false);
  const [isRunning, setIsRunning] = useState(false);
  const [btnContent, setBtnContent] = useState('');
  const user = useAppSelector(userInfo);
  const submissionModalAnimationRef = useRef(null);
  const contestAnimationModalRef = useRef(null);
  // websocket 即时向用户反馈测评进度
  function wsConnect() {
    ws.current = new WebSocket(publicRuntimeConfig.API_WS_URL + '?uid=' + user.id);
    ws.current.onmessage = (e) => {
      if (e.data === '') {
        return;
      }
      const msg = JSON.parse(e.data);
      if (msg.type === 'SUBMISSION_RESULT') {
        if (msg.message.status === 'running') {
          props.animation && progressAnimation(msg.message.message);
          setBtnContent(msg.message.message);
          setIsRunning(true);
        } else {
          getSubmission(msg.message.sid)
            .then(res => {
              setIsRunning(false);
              setSubmission(res.data);
              setBtnContent('');
              props.animation && submissionModalAnimationRef.current.done(res.data);
              // 查询是否有AK
              if (res.data.verdict === 4 && props.entityType === 1) {
                contestAnimationModalRef.current.run(props.entityId);
              }
            });
        }
      }
    };
    ws.current.onclose = () => {
      setTimeout(wsConnect, wsReconnectInterval); // 重连
    };
  }
  useEffect(() => {
    if (!user.id) {
      return;
    }
    wsConnect();
    return () => {
      ws.current?.close();
    };
  }, [ws, user.id]);
  function progressAnimation(msg: string) {
    // 处理 testing on 1/3 成进度
    const str = msg;
    const regex = /\d+/g;
    const matches = str.match(regex);
    if (matches.length == 2) {
      const current = parseInt(matches[0]);
      const total = parseInt(matches[1]);
      submissionModalAnimationRef.current.run(current, total);
    }
  }
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
          if (res.data.verdict !== 1) {
            setIsRunning(false);
          } else {
            props.animation && submissionModalAnimationRef.current.start();
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
    <>
      { props.entityType === 1 && <ContestAnimationModal ref={contestAnimationModalRef} />}
      <SubmissionModalAnimation ref={submissionModalAnimationRef} />
      {
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
      }
    </>
  );
});

RecentlySubmitted.displayName = 'RecentlySubmitted';

export default RecentlySubmitted;
