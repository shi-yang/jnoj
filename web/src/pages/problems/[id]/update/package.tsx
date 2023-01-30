import React, { useEffect, useState } from 'react';
import { Button, Card, Divider, Message, Popconfirm, Popover, Table, TableColumnProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import { listProblemFiles,  deleteProblemFile } from '@/api/problem-file';
import locale from './locale';
import styles from './style/tests.module.less';
import { FormatStorageSize, FormatTime } from '@/utils/format';
import { packProblem } from '@/api/problem';

const App = (props) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
      align: 'center',
    },
    {
      title: t['name'],
      dataIndex: 'name',
      align: 'center',
    },
    {
      title: t['type'],
      dataIndex: 'fileType',
      align: 'center',
    },
    {
      title: t['size'],
      dataIndex: 'fileSize',
      align: 'center',
      render: col => FormatStorageSize(col)
    },
    {
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: col => FormatTime(col)
    },
    {
      title: t['action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Button onClick={(e) => downloadFile(record)}>下载</Button>
          <Divider type='vertical' />
          <Popover
            trigger='click'
            title='你确定要删除吗？'
            content={
              <span>
                <Button type='text' size='small' onClick={(e) => deleteFile(record.id)}>删除</Button>
              </span>
            }
          >
            <Button>删除</Button>
          </Popover>
        </>
      ),
    },
  ];
  function downloadFile(record) {
    const a = document.createElement('a');
    a.href = record.content;
    a.download = props.problem.id + '.' + record.name;
    a.click();
  }
  function fetchData() {
    setLoading(true);
    listProblemFiles(props.problem.id, { fileType: 'package' })
      .then((res) => {
        setData(res.data.data || []);
      })
      .finally(() => {
        setLoading(false);
      });
  }
  function deleteFile(id) {
    deleteProblemFile(props.problem.id, id)
      .then(res => {
        Message.success('删除成功');
        fetchData();
      });
  }
  function pack() {
    packProblem(props.problem.id)
      .then(res => {
        Message.success('已提交打包');
      })
      .catch(res => {
        Message.error(res.response.data.message)
      })
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <Popconfirm
        focusLock
        title='提交打包？若题目设为公开，题目包将被允许下载'
        onOk={pack}
      >
        <Button
          style={{marginBottom: '10px'}}
        >
          打包
        </Button>
      </Popconfirm>
      <Table rowKey={r => r.id} loading={loading} columns={columns} data={data} />
    </Card>
  );
};

export default App;
