import React from 'react';
import { Button, Link, Space, TableColumnProps, Tag } from '@arco-design/web-react';
import { IconCheckCircle, IconExclamationCircle } from '@arco-design/web-react/icon';

export const ProblemStatus = {
  'NOT_START': '',
  'ATTEMPTED': <IconExclamationCircle />,
  'SOLVED': <IconCheckCircle />,
};

import styles from './style/index.module.less';

export function getColumns(t: any, displayFields: string) {
  const displaySource = displayFields.includes('source');
  const displayTags = displayFields.includes('tag');
  const columns:TableColumnProps[] = [
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
      render: (col, record) => (
        <div className={styles['column-title']}>
          <Link href={`/problemsets/${record.problemsetId}/problems/${record.order}`}>
            <div>
              {record.order}. {record.name}
            </div>
          </Link>
          {
            displayTags && <Space>
              {record.tags.map((item, index) => <Tag key={index}>{item}</Tag>)}
            </Space>
          }
        </div>
      )
    },
  ];
  if (displaySource) {
    columns.push({
      title: t['problem.columns.source'],
      dataIndex: 'source',
      align: 'center',
      render: x => x,
      width: 200,
    })
  }
  columns.push({
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
    width: 150,
  })
  return columns;
}
