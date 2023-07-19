import React, { useState, useEffect, useRef } from 'react';
import {
  Table,
  Card,
  PaginationProps,
  Button,
  Space,
  Typography,
  Link,
  Divider,
  Input,
  Badge,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import CreateModal from './create';
import { UpdateModal, CreateUserExpirationModal } from './modal';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import Layout from '../Layout';
import { listUsers } from '@/api/admin/user';
import { IconSearch } from '@arco-design/web-react/icon';
import { UserRole, UserStatus } from './constants';
const { Title } = Typography;

function Index() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
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
  const [createUserExpirationModalVisible, setCreateUserExpirationModalVisible] = useState(false);
  const inputRef = useRef(null);
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const columns = [
    {
      title: t['searchTable.columns.id'],
      dataIndex: 'id',
      sorter: true,
      align: 'center' as 'center',
      width: 120,
    },
    {
      title: t['searchTable.columns.username'],
      dataIndex: 'username',
      align: 'center' as 'center',
      render: (_, record) => <Link href={`/u/${record.id}`}>{record.username}</Link>,
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
      title: t['searchTable.columns.nickname'],
      dataIndex: 'nickname',
      align: 'center' as 'center',
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
      title: t['searchTable.columns.realname'],
      dataIndex: 'profile.realname',
      align: 'center' as 'center',
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
      title: t['searchTable.columns.role'],
      dataIndex: 'role',
      align: 'center' as 'center',
      render: col => t[`user.form.user.role.${col}`],
      filters: Object.keys(UserRole).map(item => ({
        text: t[`user.form.user.role.${UserRole[item]}`],
        value: UserRole[item],
      })),
      filterMultiple: false,
    },
    {
      title: t['searchTable.columns.status'],
      dataIndex: 'status',
      align: 'center' as 'center',
      render: col => {
        if (col === 'ENABLE') {
          return <Badge status='success' text={t[`user.form.user.status.${col}`]} />;
        }
        return <Badge status='error' text={t[`user.form.user.status.${col}`]} />;
      },
      filters: Object.keys(UserStatus).map(item => ({
        text: t[`user.form.user.status.${UserStatus[item]}`],
        value: UserStatus[item],
      })),
      filterMultiple: false,
    },
    {
      title: t['searchTable.columns.operations'],
      dataIndex: 'operations',
      align: 'center' as 'center',
      width: 200,
      headerCellStyle: { paddingLeft: '15px' },
      render: (_, record) => (
        <>
          <Button
            type="text"
            size="small"
          >
            <Link onClick={(e) => {setVisible(true); setId(record.id);}}>
              {t['searchTable.columns.operations.update']}
            </Link>
          </Button>
        </>
      ),
    },
  ];
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);
  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    listUsers({
      page: current,
      perPage: pageSize,
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

  return (
    <>
      <Head>
        <title>{`${t['page.title']} - ${settings.name}`}</title>
      </Head>
      <div className={styles['list-container']}>
        <Card className='container'>
          <Title heading={3}>{t['page.title']}</Title>
          <Divider />
          <div className={styles['button-group']}>
            <Space>
              <Button disabled={selectedRowKeys.length === 0} onClick={() => setCreateUserExpirationModalVisible(true)}>添加有效期事件</Button>
              <CreateUserExpirationModal userIds={selectedRowKeys} visible={createUserExpirationModalVisible} setVisible={setCreateUserExpirationModalVisible} callback={fetchData} />
              <CreateModal callback={fetchData} />
              <UpdateModal id={id} visible={visible} setVisible={setVisible} callback={fetchData} />
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
              type: 'checkbox',
              selectedRowKeys,
              onChange: (selectedRowKeys, selectedRows) => {
                setSelectedRowKeys(selectedRowKeys);
              },
            }}
          />
        </Card>
      </div>
    </>
  );
}
Index.getLayout = Layout;
export default Index;
