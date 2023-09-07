import { listProblemsetUsers } from '@/api/problemset';
import { PaginationProps, TableColumnProps, Button, Drawer, Table, Input } from '@arco-design/web-react';
import { IconSearch, IconUser } from '@arco-design/web-react/icon';
import Link from 'next/link';
import React, { useState, useEffect, useRef } from 'react';

export function UserMember({problemset}: {problemset:any}) {
  const [visible, setVisible] = useState(false);
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [formParams, setFormParams] = useState({});
  const inputRef = useRef(null);
  const [pagination, setPatination] = useState<PaginationProps>({
    hideOnSinglePage: true,
    sizeCanChange: true,
    showTotal: true,
    pageSize: 50,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const simpleColumns: TableColumnProps[] = [
    {
      title: '用户昵称',
      dataIndex: 'user.nickname',
      align: 'center',
      render: (col, item) => <Link href={`/u/${item.user.id}`} target='_blank'>{col}</Link>,
      filterMultiple: false,
      filterIcon: <IconSearch />,
      filterDropdown: ({ filterKeys, setFilterKeys, confirm }) => {
        return (
          <div className='arco-table-custom-filter'>
            <Input.Search
              ref={inputRef}
              searchButton
              placeholder='输入用户名进行搜索'
              value={filterKeys[0] || ''}
              onChange={(value) => {
                setFilterKeys(value ? [value] : []);
              }}
              onSearch={() => {
                confirm();
              }}
            />
          </div>
        );
      },
      onFilterDropdownVisibleChange: (visible) => {
        if (visible) {
          setTimeout(() => inputRef.current.focus(), 150);
        }
      },
    },
    {
      title: '解答数量',
      dataIndex: 'acceptedCount',
      align: 'center',
    },
  ];
  const examColumns: TableColumnProps[] = [
    {
      title: '用户昵称',
      dataIndex: 'user.nickname',
      align: 'center',
      render: (col, item) => <Link href={`/u/${item.user.id}`} target='_blank'>{col}</Link>,
      filterMultiple: false,
      filterIcon: <IconSearch />,
      filterDropdown: ({ filterKeys, setFilterKeys, confirm }) => {
        return (
          <div className='arco-table-custom-filter'>
            <Input.Search
              ref={inputRef}
              searchButton
              placeholder='输入用户名进行搜索'
              value={filterKeys[0] || ''}
              onChange={(value) => {
                setFilterKeys(value ? [value] : []);
              }}
              onSearch={() => {
                confirm();
              }}
            />
          </div>
        );
      },
      onFilterDropdownVisibleChange: (visible) => {
        if (visible) {
          setTimeout(() => inputRef.current.focus(), 150);
        }
      },
    },
    {
      title: '首次得分',
      dataIndex: 'initialScore',
      align: 'center',
      sorter: (a, b) => a.initialScore - b.initialScore,
      render: (col, item) => col >= 0 ? col : '-',
    },
    {
      title: '最好得分',
      dataIndex: 'bestScore',
      align: 'center',
      sorter: (a, b) => a.bestScore - b.bestScore,
    },
  ];
  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    const params = {
      page: current,
      perPage: pageSize,
      ...formParams,
    };
    listProblemsetUsers(problemset.id, params)
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
  function onChangeTable({ current, pageSize }, sorter, filters) {
    const params = {};
    if (sorter.direction) {
      let filed = '';
      if (sorter.field.includes('initial')) {
        filed = 'initial_score';
      } else {
        filed = 'best_score';
      }
      if (sorter.direction == 'descend') {
        Object.assign(params, {orderBy: filed + ' desc'});
      } else {
        Object.assign(params, {orderBy: filed});
      }
    }
    Object.assign(params, filters);
    setFormParams({...formParams, ...params});
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);
  return (
    <>
      <Button onClick={() => setVisible(true)}>
        <IconUser />{problemset.memberCount}
      </Button>
      <Drawer
        width={700}
        title={<span>用户列表</span>}
        visible={visible}
        footer={null}
        onCancel={() => setVisible(false)}
      >
        <Table
          rowKey={r => r.id}
          loading={loading}
          onChange={onChangeTable}
          pagination={pagination}
          columns={problemset.type === 'EXAM' ? examColumns : simpleColumns}
          data={users}
        />
      </Drawer>
    </>
  );
}

export default () => {};
