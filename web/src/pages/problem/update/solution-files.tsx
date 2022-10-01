import React, { useEffect, useState } from 'react';
import { Button, Card, Space, Table, TableColumnProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/tests.module.less';
import { ListProblemSolutions } from '@/api/problem-solution';

const App = (props) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  function fetchData() {
    setLoading(true);
    ListProblemSolutions(props.problem.id)
      .then((res) => {
        setData(res.data || []);
      })
      .finally(() => {
        setLoading(false);
      });
  }

  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
    },
    {
      title: t['name'],
      dataIndex: 'name',
    },
    {
      title: t['language'],
      dataIndex: 'language',
    },
    {
      title: t['length'],
      dataIndex: 'length',
    },
    {
      title: t['type'],
      dataIndex: 'type',
    },
    {
      title: t['createdAt'],
      dataIndex: 'created_at',
    },
    {
      title: t['action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Button type="text" size="small">查看</Button>
          <Button type="text" size="small">编辑</Button>
          <Button type="text" size="small">删除</Button>
        </>
      ),
    },
  ];

  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <div className={styles['button-group']}>
        <Space>
          <Button type='primary'>添加</Button>
        </Space>
      </div>
      <Table rowKey={r => r.id} loading={loading} columns={columns} data={data} />
    </Card>
  );
};

export default App;
