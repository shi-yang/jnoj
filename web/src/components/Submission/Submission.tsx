import React, { useEffect, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { LanguageMap, listSubmissions } from '@/api/submission';
import { FormatMemorySize, FormatTime } from '@/utils/format';
import SubmissionVerdict from './SubmissionVerdict';
import SubmissionDrawer from './SubmissionDrawer';

const Submission = ({pid=undefined, entityType=undefined, userId=undefined}) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [submissionId, setSubmissionId] = useState(0);
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
    const params = {
      page: current,
      perPage: pageSize,
      problemId: pid,
      userId: userId,
      entityType: entityType,
    };
    setLoading(true);
    listSubmissions(params)
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
  function onView(id) {
    setSubmissionId(id);
    setVisible(true);
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
      title: t['language'],
      dataIndex: 'language',
      align: 'center',
      render: (col) => LanguageMap[col]
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
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: (col) => FormatTime(col)
    },
    {
      title: t['action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Button type="text" size="small" onClick={() => { onView(record.id) }}>查看</Button>
        </>
      ),
    },
  ];
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);
  return (
    <Card>
      <SubmissionDrawer visible={visible} id={submissionId} onCancel={() => setVisible(false)} />
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
