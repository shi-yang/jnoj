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
  Popconfirm,
  Message,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import { UpdateModal, CreateModal } from './modal';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import Layout from '../Layout';
import { deleteUserBadge, listUserBadges } from '@/api/admin/user';
import { IconSearch } from '@arco-design/web-react/icon';
import { UserBadgeType } from './constants';
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
      title: t['searchTable.columns.type'],
      dataIndex: 'type',
      align: 'center' as 'center',
      render: col => t[`user.form.user.badge.type.${col}`],
      filters: Object.keys(UserBadgeType).map(item => ({
        text: t[`user.form.user.badge.type.${UserBadgeType[item]}`],
        value: UserBadgeType[item],
      })),
      filterMultiple: false,
    },
    {
      title: t['searchTable.columns.name'],
      dataIndex: 'name',
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
      title: t['searchTable.columns.image'],
      dataIndex: 'image',
      render: col => <img src={col} height={80} alt='image' />
    },
    {
      title: t['searchTable.columns.imageGif'],
      dataIndex: 'imageGif',
      render: col => <img src={col} height={80} alt='imageGif' />
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
          <Divider type="vertical" />
          <Popconfirm
            focusLock
            title='确定删除？'
            content='此操作将删除此勋章极其关联所有用户获得此勋章的记录，删除后数据不可恢复'
            onOk={() => onDelete(record.id)}
          >
            <Button type='primary' status='danger'>
              {t['searchTable.columns.operations.delete']}
            </Button>
          </Popconfirm>
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
    listUserBadges({
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

  function onDelete(id) {
    deleteUserBadge(id).then(res => {
      Message.success('已删除');
      fetchData();
    }).catch(err => {
      Message.error(err.response.data.message);
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
