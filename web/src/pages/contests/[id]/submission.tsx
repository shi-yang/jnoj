import React, { useEffect, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { LanguageMap } from '@/api/submission';
import { listContestSubmissions } from '@/api/contest';
import { FormatMemorySize, FormatTime } from '@/utils/format';
import SubmissionDrawer from '@/components/Submission/SubmissionDrawer';
import SubmissionVerdict from '@/components/Submission/SubmissionVerdict';

const Submission = ({contest}: {contest: {id: number}}) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [id, setId] = useState(0);
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
  function onDrawerCancel() {
    setVisible(false);
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
      render: (col, record) => <span> {String.fromCharCode(65 + record.problemNumber)}. {col}</span>
    },
    {
      title: t['verdict'],
      dataIndex: 'verdict',
      align: 'center',
      render: (col) => <SubmissionVerdict verdict={col} />
    },
    {
      title: t['score'],
      dataIndex: 'score',
      align: 'center',
    },
    {
      title: t['time'],
      dataIndex: 'time',
      align: 'center',
      render: (col) => `${col / 1000} ms`
    },
    {
      title: t['memory'],
      dataIndex: 'memory',
      align: 'center',
      render: (col) => FormatMemorySize(col)
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
      align: 'center',
      render: (_, record) => (
        <>
          <Button type="text" size="small" onClick={(e) => { setVisible(true); setId(record.id); }}>查看</Button>
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
      {visible && <SubmissionDrawer id={id} visible={visible} onCancel={onDrawerCancel} />}
    </Card>
  );
};

export default Submission;
