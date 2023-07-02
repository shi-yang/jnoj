import { listContestRatingChanges } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import { Card, Link, PaginationProps, Table, Typography } from '@arco-design/web-react';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from '../context';
import locale from '../locale';

function Users() {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState([]);
  const contest = useContext(ContestContext);
  const [formParams, setFormParams] = useState({});
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100]
  });
  const columns = [
    {
      title: t['setting.users.name'],
      dataIndex: 'name',
      align: 'center' as 'center',
      render: (col, record) => <Link href={`/u/${record.userId}`} target='_blank'>{col}</Link>
    },
    {
      title: 'Δ',
      dataIndex: 'newRating',
      align: 'center' as 'center',
      render: (_, record) => {
        const changed = record.newRating - record.oldRating;
        return changed > 0 ? (
          <Typography.Text type='success' bold>+{changed}</Typography.Text>
        ) : (
          <Typography.Text type='secondary' bold>{changed}</Typography.Text>
        );
      }
    },
    {
      title: t['setting.users.rating'],
      dataIndex: 'rating',
      align: 'center' as 'center',
      render: (_, record) => (
        <div>
          <span>{record.oldRating} → {record.newRating}</span>
        </div>
      ),
    }
  ];
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);

  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      per_page: pageSize,
      ...formParams,
    };
    setLoading(true);
    listContestRatingChanges(contest.id, params)
      .then(res => {
        setData(res.data.data);
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
  function onChangeTable({ current, pageSize }, sorter, filters) {
    setFormParams({...formParams, ...filters});
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  return (
    <Card>
      <Table
        rowKey='id'
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={data}
      />
    </Card>
  );
};

export default Users;
