import React from 'react';
import { Button, Typography, Badge } from '@arco-design/web-react';
import IconText from './icons/text.svg';
import IconHorizontalVideo from './icons/horizontal.svg';
import IconVerticalVideo from './icons/vertical.svg';
import { Link } from 'react-router-dom';
import { FormatTime } from '@/utils/formatTime';

const { Text } = Typography;

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
      title: t['contest.columns.id'],
      dataIndex: 'id',
      width: 200,
      align: 'center',
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
      width: 200,
      align: 'center',
    },
    {
      title: t['contest.columns.startedAt'],
      dataIndex: 'startTime',
      width: 200,
      align: 'center',
      render: col => FormatTime(col)
    },
    {
      title: t['contest.columns.endedAt'],
      dataIndex: 'endTime',
      width: 200,
      align: 'center',
      render: col => FormatTime(col)
    },
  ];
}

export default () => ContentIcon;
