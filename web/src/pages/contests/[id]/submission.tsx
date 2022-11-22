import React, { useEffect, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { listSubmissions } from '@/api/submission';
import { listContestSubmissions } from '@/api/contest';
import { stringify } from 'query-string';

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
    },
    {
      title: t['user'],
      dataIndex: 'user',
      render: (col, record) => (
        <span>
          {col.nickname}
        </span>
      )
    },
    {
      title: t['problem'],
      dataIndex: 'problem',
    },
    {
      title: t['verdict'],
      dataIndex: 'verdict',
    },
    {
      title: t['language'],
      dataIndex: 'language',
    },
    {
      title: t['createdAt'],
      dataIndex: 'created_at',
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
