import React, { useState, useEffect, useRef } from 'react';
import {
  Table,
  PaginationProps,
  Space,
  Typography,
  Link,
  Tooltip,
  Input,
} from '@arco-design/web-react';
import { IconDownload, IconSearch } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import SearchForm from './form';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import CreateModal from './create';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { FormatTime } from '@/utils/format';
import { listProblemsets } from '@/api/problemset';

function Page() {
  const t = useLocale(locale);
  const user = useAppSelector(userInfo);
  const [data, setData] = useState([]);
  const [pagination, setPagination] = useState<PaginationProps>({
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const [loading, setLoading] = useState(true);
  const [formParams, setFormParams] = useState({});
  const [id, setId] = useState(0);
  const [visible, setVisible] = useState(false);
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const inputRef = useRef(null);

  const columns = [
    {
      title: t['searchTable.columns.id'],
      dataIndex: 'id',
      sorter: true,
      align: 'center' as 'center',
      width: 120,
      render: (value, record) => (
      <>
        <Typography.Text copyable>{value}</Typography.Text>
        {record.allowDownload && <Tooltip mini content={t['allowDownload']}>
          <IconDownload />
        </Tooltip>}
      </>
      ),
    },
    {
      title: t['searchTable.columns.name'],
      dataIndex: 'name'
    },
    {
      title: t['searchTable.columns.createdBy'],
      dataIndex: 'username',
      align: 'center' as 'center',
      render: (_, record) => <Link href={`/u/${record.userId}`}>{record.nickname}</Link>,
      filterMultiple: false,
      filterIcon: <IconSearch />,
      filterDropdown: ({ filterKeys, setFilterKeys, confirm }) => {
        return (
          <div className='arco-table-custom-filter'>
            <Input.Search
              ref={inputRef}
              searchButton
              placeholder='Please enter name'
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
      title: t['searchTable.columns.createdAt'],
      dataIndex: 'createdAt',
      align: 'center' as 'center',
      render: col => FormatTime(col, 'YYYY-MM-DD')
    },
    {
      title: t['searchTable.columns.operations'],
      dataIndex: 'operations',
      align: 'center' as 'center',
      width: 200,
      headerCellStyle: { paddingLeft: '15px' },
    },
  ];
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);
  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    listProblemsets({
      page: current,
      perPage: pageSize,
      type: [0, 1],
      ...formParams,
    })
      .then((res) => {
        setData(res.data.data);
        setPagination({
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
      if (sorter.direction == 'descend') {
        Object.assign(params, {orderBy: 'id desc'});
      } else {
        Object.assign(params, {orderBy: 'id'});
      }
    }
    Object.assign(params, filters);
    setFormParams({...formParams, ...params});
    setPagination({
      ...pagination,
      current,
      pageSize,
    });
  }

  function handleSearch(params) {
    setPagination({ ...pagination, current: 1 });
    setFormParams(params);
  }

  return (
    <div>
      <SearchForm onSearch={handleSearch} />
      <div className={styles['button-group']}>
        <Space>
          <CreateModal />
        </Space>
      </div>
      <Table
        rowKey="id"
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={data}
        rowSelection={{
          type: 'radio',
          selectedRowKeys,
          onChange: (selectedRowKeys, selectedRows) => {
            setSelectedRowKeys(selectedRowKeys);
          },
          onSelect: (selected, record, selectedRows) => {
          },
        }}
      />
    </div>
  );
}

export default Page;