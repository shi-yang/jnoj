import React from 'react';
import Link from 'next/link';
import { FormatTime } from '@/utils/format';
import { IconUser } from '@arco-design/web-react/icon';

export const ContestType = ['', 'ICPC', 'IOI', 'OI'];

export function getColumns(
  t: any,
  callback: (record: Record<string, any>, type: string) => Promise<void>
) {
  return [
    {
      title: t['contest.columns.id'],
      dataIndex: 'id',
      align: 'center' as 'center',
      width: 200,
    },
    {
      title: t['contest.columns.name'],
      dataIndex: 'name',
      align: 'left' as 'left',
      render: (value, record) => (
        <Link href={`/contests/${record.id}`}>
          {value}
        </Link>
      )
    },
    {
      title: t['contest.columns.status'],
      dataIndex: 'status',
      align: 'center' as 'center',
      width: 220,
      render: (col, record) => (
        <>
          {t[record.runningStatus]} <IconUser /> x {record.participantCount}
        </>
      )
    },
    {
      title: t['contest.columns.type'],
      dataIndex: 'type',
      align: 'center' as 'center',
      width: 100,
      render: (col) => ContestType[col]
    },
    {
      title: t['contest.columns.startedAt'],
      dataIndex: 'startTime',
      align: 'center' as 'center',
      width: 180,
      render: col => FormatTime(col)
    },
    {
      title: t['contest.columns.endedAt'],
      dataIndex: 'endTime',
      align: 'center' as 'center',
      width: 180,
      render: col => FormatTime(col)
    },
  ];
}