import { listContestUsers } from '@/api/contest';
import { PaginationProps, Table } from '@arco-design/web-react';
import React, { useEffect, useState } from 'react';

const Users = ({contest}: {contest: {id: number}}) => {
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const columns = [
    {
      title: 'userId',
      dataIndex: 'userId',
      align: 'center' as 'center',
      width: 200,
    },
    {
      title: 'Nickname',
      dataIndex: 'nickname'
    }
  ];
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);

  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    listContestUsers(contest.id)
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
  function onChangeTable({ current, pageSize }) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  return (
    <>
      <Table
        rowKey='id'
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={data}
      />
    </>
  );
};

export default Users;
