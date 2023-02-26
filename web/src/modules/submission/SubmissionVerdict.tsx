import useLocale from '@/utils/useLocale';
import { Typography } from '@arco-design/web-react';
import locale from './locale';
import React from 'react';

enum VerdictStatus {
  Pending = 1,
  CompileError = 2,
  WrongAnswer = 3,
  Accepted = 4,
  PresentationError = 5,
  TimeLimitExceeded = 6,
  MemoryLimitExceeded = 7,
  RuntimeError = 8,
  SystemError = 9,
}

const VerdictMap = {
  1: 'verdict.pending',
  2: 'verdict.compileError',
  3: 'verdict.wrongAnswer',
  4: 'verdict.accepted',
  5: 'verdict.presentationError',
  6: 'verdict.timeLimitExceeded',
  7: 'verdict.memoryLimitExceeded',
  8: 'verdict.runtimeError',
  9: 'verdict.systemError',
};

const VerdictColorMap = {
  1: 'secondary',
  2: 'warning',
  3: 'error',
  4: 'success',
  5: 'warning',
  6: 'warning',
  7: 'warning',
  8: 'error',
  9: 'error',
};

export default function SubmissionVerdict({ verdict }: { verdict: number }) {
  const t = useLocale(locale);
  return (
    <Typography.Text bold type={VerdictColorMap[verdict]}>
      {t[VerdictMap[verdict]]}
    </Typography.Text>
  );
}
