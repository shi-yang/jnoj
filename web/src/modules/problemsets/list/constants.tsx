import React from 'react';
import { Button } from '@arco-design/web-react';
import Link from 'next/link';
import { IconCheckCircle, IconExclamationCircle } from '@arco-design/web-react/icon';

export const ProblemStatus = {
  'NOT_START': '',
  'ATTEMPTED': <IconExclamationCircle />,
  'SOLVED': <IconCheckCircle />,
};

export function getColumns(
  t: any,
  callback: (record: Record<string, any>, type: string) => Promise<void>
) {
  return [
    {
      title: t['problem.columns.status'],
      dataIndex: 'status',
      align: 'center' as 'center',
      width: 80,
      render: col => ProblemStatus[col]
    },
    {
      title: t['problem.columns.name'],
      dataIndex: 'name',
      render: (col, record) => <>{record.order}. {record.name}</>
    },
    {
      title: t['problem.columns.submitAndPass'],
      dataIndex: 'count',
      align: 'center' as 'center',
      render(_, record) {
        return (
          <>
            {Number(record.acceptedCount).toLocaleString()} / {Number(record.submitCount).toLocaleString()}
          </>
        );
      },
      width: 200,
    },
    {
      title: t['problem.columns.operations'],
      dataIndex: 'operations',
      align: 'center' as 'center',
      headerCellStyle: { paddingLeft: '15px' },
      render: (_, record) => (
        <Button
          type="text"
          size="small"
          onClick={() => callback(record, 'view')}
        >
          <Link href={`/problemsets/${record.problemsetId}/problems/${record.order}`}>
            {t['problem.columns.operations.view']}
          </Link>
        </Button>
      ),
      width: 100,
    },
  ];
}

export default () => ContentIcon;
