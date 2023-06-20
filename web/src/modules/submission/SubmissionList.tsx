import React, { useEffect, useImperativeHandle, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps, Link } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { LanguageMap, getSubmission, listSubmissions } from '@/api/submission';
import { FormatMemorySize, FormatTime } from '@/utils/format';
import SubmissionVerdict from './SubmissionVerdict';
import SubmissionDrawer from './SubmissionDrawer';

interface SubmissionProps {
  pid?:number,
  entityType?:number,
  userId?:number,
  defaultPageSize?: number,
}

const Submission = ({pid=undefined, entityType=undefined, userId=undefined}: SubmissionProps, ref: any) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [submissionId, setSubmissionId] = useState(0);
  const [visible, setVisible] = useState(false);
  const [pendingId, setPendingId] = useState(null);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100]
  });
  useImperativeHandle(ref, () => {
    return {
      fetchData
    };
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
        const ids = res.data.data.filter(item => item.verdict === 1).map(item => item.id);
        setPatination(ids);
      })
      .finally(() => {
        setLoading(false);
      });
  }

  // Check if any rows are in pending state and start polling for them
  useEffect(() => {
    const pendingRows = data.filter(row => row.verdict === 1);
    if (pendingRows.length > 0) {
      const intervalId = setInterval(async () => {
        const newData = [...data];
        for (let i = 0; i < newData.length; i++) {
          if (newData[i].verdict === 1) {
            const submission = await getSubmission(newData[i].id);
            newData[i] = submission.data;
          }
        }
        setData(newData);
      }, 1000); // Poll every 1 seconds
      setPendingId(intervalId);
    }
    return () => clearInterval(pendingId);
  }, [data]);

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
      title: t['user'],
      dataIndex: 'nickname',
      align: 'center',
      render: (col, record) => <Link href={`/u/${record.userId}`}>{col}</Link>
    },
    {
      title: t['problemName'],
      dataIndex: 'problemName',
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
      render: (_, record) => <Button type="text" size="small" onClick={() => { onView(record.id); }}>查看</Button>,
    },
  ];
  useEffect(() => {
    fetchData();
  }, [pid, pagination.current, pagination.pageSize]);
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

export default React.forwardRef(Submission);
