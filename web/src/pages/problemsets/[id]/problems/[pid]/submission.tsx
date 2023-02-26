import React, { useEffect, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { LanguageMap, listSubmissions } from '@/api/submission';
import { FormatMemorySize, FormatTime } from '@/utils/format';
import SubmissionDrawer from '@/modules/submission/SubmissionDrawer';
import SubmissionVerdict from '@/modules/submission/SubmissionVerdict';
import { userInfo } from '@/store/reducers/user';
import { useAppSelector } from '@/hooks';

const Submission = ({problemId}: {problemId: number}) => {
  const t = useLocale(locale);
  const user = useAppSelector(userInfo);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [id, setId] = useState(0);
  const [pagination, setPagination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 10,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const [formParams, setFormParams] = useState({});
  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    listSubmissions({
      problemId: problemId,
      userId: user.id,
      page: current,
      perPage: pageSize,
      ...formParams,
    })
      .then((res) => {
        setData(res.data.data || []);
        setPagination({
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
    setPagination({
      ...pagination,
      current,
      pageSize,
    });
  }
  function onDrawerCancel() {
    setVisible(false);
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
          <Button type="text" size="small" onClick={(e) => { setVisible(true); setId(record.id); }}>查看</Button>
        </>
      ),
    },
  ];
  useEffect(() => {
    fetchData();
  }, [problemId, pagination.current, pagination.pageSize, JSON.stringify(formParams)]);
  return (
    <Card style={{height: '100%', overflow: 'auto'}}>
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
