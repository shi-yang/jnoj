import SubmissionVerdict from './SubmissionVerdict';
import useLocale from '@/utils/useLocale';
import { Link, Modal, Progress } from '@arco-design/web-react';
import React, { useState, forwardRef } from 'react';
import locale from './locale';
import { useImperativeHandle } from 'react';

const SubmissionModalAnimation = function(props: any, ref: any) {
  const t = useLocale(locale);
  const [submission, setSubmission] = useState({ id: 0, verdict: 0 });
  const [visible, setVisible] = useState(false);
  const [isRunning, setIsRunning] = useState(true);
  const [status, setStatus] = useState({
    current: 0,
    total: 0,
  });
  function start() {
    setVisible(true);
    setIsRunning(true);
  }
  function run(current: number, total: number) {
    setStatus({current: current, total: total });
  }
  function done(submission: any) {
    setSubmission(submission);
    setIsRunning(false);
  }
  function onCancel() {
    setVisible(false);
    setSubmission({ id: 0, verdict: 0 });
    setStatus({current: 0, total: 0 });
  }

  useImperativeHandle(ref, () => ({
    start: start,
    run: run,
    done: done,
  }));

  return (
    <Modal
      visible={visible}
      footer={null}
      onCancel={onCancel}
    >
      <h2 style={{textAlign: 'center'}}><SubmissionVerdict icon={true} verdict={submission.verdict}/></h2>
      <Progress percent={status.total === 0 ? 0 : parseInt((status.current * 100 / status.total).toFixed(0))} width='100%' />
      { !isRunning && (
        <Link href={`/submissions/${submission.id}`} target='_blank'>查看详情</Link>
      )}
    </Modal>
  );
};

export default forwardRef(SubmissionModalAnimation);
