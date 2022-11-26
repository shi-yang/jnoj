import React, { useEffect, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps, Typography } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { LanguageMap, VerdictColorMap, VerdictMap } from '@/api/submission';
import { listContestSubmissions } from '@/api/contest';
import { FormatTime } from '@/utils/format';

const Submission = ({contest}) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 10,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  function fetchData() {
    const { current, pageSize } = pagination;
    const param = {
      page: current,
      pageSize,
      contestId: contest.id
    };
    setLoading(true);
    listContestSubmissions(contest.id, param)
      .then((res) => {
        setData(res.data.data || []);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
      })
      .finally(() => {
        setLoading(false);
      });
  }

  function onChangeTable({ current, pageSize }) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
      align: 'center',
    },
    {
      title: t['user'],
      dataIndex: 'user',
      align: 'center',
      render: (col) => col.nickname
    },
    {
      title: t['problem'],
      dataIndex: 'problemName',
      align: 'center',
    },
    {
      title: t['verdict'],
      dataIndex: 'verdict',
      align: 'center',
      render: (col) => <Typography.Text bold type={VerdictColorMap[col]}>{VerdictMap[col]}</Typography.Text>
    },
    {
      title: t['language'],
      dataIndex: 'language',
      align: 'center',
      render: col => LanguageMap[col]
    },
    {
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: col => FormatTime(col)
    },
    {
      title: t['action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Button type="text" size="small" onClick={(e) => { setVisible(true) }}>查看</Button>
        </>
      ),
    },
  ];

  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <Table
        rowKey={r => r.id}
        loading={loading}
        columns={columns}
        onChange={onChangeTable}
        pagination={pagination}
        data={data}
      />
    </Card>
  );
};

export default Submission;
