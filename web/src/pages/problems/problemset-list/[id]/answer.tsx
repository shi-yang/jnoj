import React, { useEffect, useState } from 'react';
import {
  Avatar,
  Card,
  Link,
  PaginationProps,
  Table, TableColumnProps
} from '@arco-design/web-react';
import {
  listProblemsetAnswers,
} from '@/api/problemset';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { FormatTime, FormatDuration } from '@/utils/format';

function Page({problemset}: {problemset:any}) {
  const problemsetId = problemset.id;
  const t = useLocale(locale);
  const [users, setUsers] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 50,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);

  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    const params = {
      page: current,
      perPage: pageSize,
    };
    listProblemsetAnswers(problemsetId, params)
      .then((res) => {
        setUsers(res.data.data);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
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
      key: 'username',
      title: t['username'],
      dataIndex: 'username',
      align: 'center',
      render: (_, record) => record.user.username
    },
    {
      key: 'user',
      title: t['user'],
      dataIndex: 'user',
      align: 'center',
      render: (_, record) => (
        <>
          <Link href={`/u/${record.user.id}`} target='_blank'>
            {
              record.user.avatar && (
                <Avatar size={18}>
                  <img src={record.user.avatar} alt='user avatar' />
                </Avatar>
              )
            } {record.user.username}
          </Link>
        </>
      )
    },
    {
      key: 'score',
      title: t['score'],
      dataIndex: 'score',
      align: 'center',
    },
    {
      key: 'createdAt',
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: col => FormatTime(col)
    },
    {
      key: 'duration',
      title: t['duration'],
      dataIndex: 'duration',
      align: 'center',
      render: (_, item) => FormatDuration(item.submittedAt, item.createdAt)
    },
    {
      key: 'action',
      title: t['update.table.column.action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Link href={`/problemsets/${problemsetId}/answer/${record.id}`}>查看</Link>
        </>
      ),
    },
  ];

  return (
    <Card>
      <Table
        rowKey={r => r.id}
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={users}
      />
    </Card>
  );
}

export default Page;
