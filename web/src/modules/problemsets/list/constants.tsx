import React from 'react';
import { Button } from '@arco-design/web-react';
import IconText from './icons/text.svg';
import IconHorizontalVideo from './icons/horizontal.svg';
import IconVerticalVideo from './icons/vertical.svg';
import Link from 'next/link';

export const ContentType = ['图文', '横版短视频', '竖版短视频'];
export const FilterType = ['规则筛选', '人工'];
export const Status = ['未上线', '已上线'];

const ContentIcon = [
  <IconText key={0} />,
  <IconHorizontalVideo key={1} />,
  <IconVerticalVideo key={2} />,
];

export function getColumns(
  t: any,
  callback: (record: Record<string, any>, type: string) => Promise<void>
) {
  return [
    {
      title: t['problem.columns.id'],
      dataIndex: 'order',
      align: 'center' as 'center',
      width: 150,
    },
    {
      title: t['problem.columns.name'],
      dataIndex: 'name',
      render: (col) => {
        return col
      }
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
