import React, { useEffect, useState } from 'react';
import { Button, Card, Form, Input, Message, Modal, Popover, Select, Space, Table, TableColumnProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import { listProblemFiles, createProblemFile, deleteProblemFile, getProblemFile, updateProblemFile, runProblemFile } from '@/api/problem-file';
import locale from './locale';
import styles from './style/tests.module.less';
import { FormatTime } from '@/utils/format';
const FormItem = Form.Item;

const App = (props: any) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [editVisible, setEditVisible] = useState(false);
  const [form] = Form.useForm();
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
  function fetchData() {
    setLoading(true);
    listProblemFiles(props.problem.id)
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
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <Table rowKey={r => r.id} loading={loading} columns={columns} data={data} />
    </Card>
  );
};

export default App;
