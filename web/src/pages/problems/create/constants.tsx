import React from 'react';
import { Button, Typography } from '@arco-design/web-react';
import IconText from './icons/text.svg';
import IconHorizontalVideo from './icons/horizontal.svg';
import IconVerticalVideo from './icons/vertical.svg';
import styles from './style/index.module.less';
import Link from 'next/link';
import { FormatTime } from '@/utils/format';

const { Text } = Typography;

export const Status = ['', '私有', '公开'];

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
      title: t['searchTable.columns.id'],
      dataIndex: 'id',
      align: 'center' as 'center',
      render: (value) => <Text copyable>{value}</Text>,
    },
    {
      title: t['searchTable.columns.name'],
      dataIndex: 'name',
    },
    {
      title: t['searchTable.columns.createdTime'],
      align: 'center' as 'center',
      dataIndex: 'createdAt',
      sorter: (a, b) => b.createdAt - a.createdAt,
      render: (x) => FormatTime(x)
    },
    {
      title: t['searchTable.columns.status'],
      dataIndex: 'status',
      align: 'center' as 'center',
      render: (x) => Status[x],
    },
    {
      title: t['searchTable.columns.operations'],
      dataIndex: 'operations',
      align: 'center' as 'center',
      headerCellStyle: { paddingLeft: '15px' },
      render: (_, record) => (
        <Button
          type="text"
          size="small"
        >
          <Link href={`/problems/${record.id}/update`}>{t['searchTable.columns.operations.view']}</Link>
        </Button>
      ),
    },
  ];
}

export default () => ContentIcon;
