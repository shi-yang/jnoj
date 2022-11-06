import React from 'react';
import { Button, Typography, Badge } from '@arco-design/web-react';
import IconText from './icons/text.svg';
import IconHorizontalVideo from './icons/horizontal.svg';
import IconVerticalVideo from './icons/vertical.svg';
import { Link } from 'react-router-dom';

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
      title: t['problem.columns.id'],
      dataIndex: 'id',
      align: 'center',
      width: 200,
    },
    {
      title: t['problem.columns.name'],
      dataIndex: 'name',
    },
    {
      title: t['problem.columns.submitAndPass'],
      dataIndex: 'count',
      align: 'center',
      sorter: (a, b) => a.count - b.count,
      render(x) {
        return (
          <>
            {Number(x).toLocaleString()} / {Number(x).toLocaleString()}
          </>
        );
      },
      width: 200,
    },
    {
      title: t['problem.columns.operations'],
      dataIndex: 'operations',
      align: 'center',
      headerCellStyle: { paddingLeft: '15px' },
      render: (_, record) => (
        <Button
          type="text"
          size="small"
          onClick={() => callback(record, 'view')}
        >
          <Link to={`/problems/${record.id}`}>
            {t['problem.columns.operations.view']}
          </Link>
        </Button>
      ),
      width: 100,
    },
  ];
}

export default () => ContentIcon;