import React, { useEffect, useState } from 'react';
import { Button, Card, Divider, Message, Popover, Space, Table, TableColumnProps, Upload } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/tests.module.less';
import { deleteProblemTests, listProblemTests } from '@/api/problem-test';

const App = (props) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  function fetchData() {
    setLoading(true);
    listProblemTests(props.problem.id).then(res => {
      setData(res.data.data);
    }).finally(() => {
      setLoading(false);
    })
  }
  function createTest(file) {
    console.log(file)
  }
  function deleteTest(id) {
    deleteProblemTests(props.problem.id, id)
  }

  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
    },
    {
      title: t['example'],
      dataIndex: 'example',
    },
    {
      title: t['content'],
      dataIndex: 'content',
    },
    {
      title: t['size'],
      dataIndex: 'size',
    },
    {
      title: t['remark'],
      dataIndex: 'remark',
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
          <Popover
            trigger='click'
            title='你确定要删除吗？'
            content={
              <span>
                <Button type='text' size='small' onClick={(e) => deleteTest(record.id)}>删除</Button>
              </span>
            }
          >
            <Button>删除</Button>
          </Popover>
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
        <Upload
          drag
          multiple
          accept='text/*'
          autoUpload={false}
          onDrop={(e) => createTest(e)}
        />
        </Space>
      </div>
      <Table rowKey={r => r.id} loading={loading} columns={columns} data={data} />
    </Card>
  );
};

export default App;
