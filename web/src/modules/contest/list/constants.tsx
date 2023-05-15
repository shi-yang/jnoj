import React from 'react';
import { FormatTime } from '@/utils/format';
import { Link } from '@arco-design/web-react';
import { IconLock, IconUser, IconUserGroup } from '@arco-design/web-react/icon';

export function getColumns(
  t: any,
  groupId: number,
  callback: (record: Record<string, any>, type: string) => Promise<void>
) {
  return [
    {
      title: t['contest.columns.name'],
      dataIndex: 'name',
      align: 'left' as 'left',
      render: (value, record) => (
        <Link href={`/contests/${record.id}`}>
          { record.privacy === 'PRIVATE' && <IconLock /> } {value}
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
      render: col => col
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
    {
      title: t['contest.columns.owner'],
      dataIndex: 'owner',
      align: 'center' as 'center',
      render: (col, record) => {
        if (groupId) {
          return <Link href={`/u/${record.userId}`}>{col.userNickname}</Link>;
        }
        return col.type === 'GROUP'
          ? <Link href={`/groups/${col.id}`}><IconUserGroup /> {col.name}</Link>
          : <Link href={`/u/${col.id}`}>{col.name}</Link>;
      }
      // render: col => col.type === 'GROUP'
      //   ? <Link href={`/groups/${col.id}`}><IconUserGroup /> {col.name}</Link>
      //   : <Link href={`/u/${col.id}`}>{col.name}</Link>
    }
  ];
}
