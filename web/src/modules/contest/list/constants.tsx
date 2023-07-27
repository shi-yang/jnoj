import React from 'react';
import { FormatTime } from '@/utils/format';
import { Divider, Link, Space, Tag, Typography } from '@arco-design/web-react';
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
        <div style={{display: 'flex', justifyContent: 'space-between'}}>
          <Link href={`/contests/${record.id}`}>
            { record.privacy === 'PRIVATE' && <IconLock /> } {value}
          </Link>
          {record.feature.includes('rated') && (
            <Tag color='red'>Rated</Tag>
          )}
        </div>
      )
    },
    {
      title: t['contest.columns.status'],
      dataIndex: 'status',
      align: 'center' as 'center',
      width: 220,
      render: (col, record) => (
        <Space split={<Divider type='vertical' />}>
          {record.runningStatus === 'FINISHED' ? (
            <Typography.Text bold type='error'>{t[record.runningStatus]}</Typography.Text>
          ) : (
            <Typography.Text bold type='success'>{t[record.runningStatus]}</Typography.Text>
          )}
          <span><IconUser /> x {record.participantCount}</span>
        </Space>
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
    }
  ];
}
