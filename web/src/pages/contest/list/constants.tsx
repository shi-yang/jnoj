import React from 'react';
import IconText from './icons/text.svg';
import IconHorizontalVideo from './icons/horizontal.svg';
import IconVerticalVideo from './icons/vertical.svg';
import { Link } from 'react-router-dom';
import { FormatTime } from '@/utils/formatTime';
import { IconUser } from '@arco-design/web-react/icon';

export const ContestStatus = ['', 'ICPC', 'IOI', 'OI'];
export const ContestType = ['', 'ICPC', 'IOI', 'OI'];

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
      title: t['contest.columns.id'],
      dataIndex: 'id',
      align: 'center',
      width: 200,
    },
    {
      title: t['contest.columns.name'],
      dataIndex: 'name',
      render: (value, record) => (
        <Link to={`/contests/${record.id}`}>
          {value}
        </Link>
      )
    },
    {
      title: t['contest.columns.status'],
      dataIndex: 'status',
      align: 'center',
      width: 180,
      render: (col, record) => (
        <>
          尚未开始 <IconUser /> x {record.participantCount}
        </>
      )
    },
    {
      title: t['contest.columns.type'],
      dataIndex: 'type',
      align: 'center',
      width: 100,
      render: (col) => ContestType[col]
    },
    {
      title: t['contest.columns.startedAt'],
      dataIndex: 'startTime',
      align: 'center',
      width: 180,
      render: col => FormatTime(col)
    },
    {
      title: t['contest.columns.endedAt'],
      dataIndex: 'endTime',
      align: 'center',
      width: 180,
      render: col => FormatTime(col)
    },
  ];
}

export default () => ContentIcon;
