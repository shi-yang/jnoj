import React, { useState, useEffect } from 'react';
import {
  Table,
  Card,
  PaginationProps,
  Button,
  Space,
  Typography,
  Link,
  Divider,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import CreateModal from './create';
import { listProblems } from '@/api/problem';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import Layout from '../Layout';
const { Title } = Typography;

function Index() {
  const t = useLocale(locale);
  const user = useAppSelector(userInfo);
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
    },
    {
      title: t['searchTable.columns.nickname'],
      dataIndex: 'nickname',
      align: 'center' as 'center',
    },
    {
      title: t['searchTable.columns.operations'],
      dataIndex: 'operations',
      align: 'center' as 'center',
      width: 200,
      headerCellStyle: { paddingLeft: '15px' },
      render: (_, record) => (
        <>
          <Button type='text' size='small'>
            {t['searchTable.columns.operations.view']}
          </Button>
          <Button
            type="text"
            size="small"
          >
            <Link href={`/problems/${record.id}/update`}>{t['searchTable.columns.operations.update']}</Link>
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
    listProblems({
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
          />
        </Card>
      </div>
    </>
  );
}
Index.getLayout = Layout;
export default Index;
