import React, { useEffect, useState } from 'react';
import { Button, Card, Form, Input, Message, Modal, Popover, Select, Space, Table, TableColumnProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import { listProblemFiles, createProblemFile, deleteProblemFile, getProblemFile, updateProblemFile, runProblemFile } from '@/api/problem-file';
import locale from './locale';
import styles from './style/tests.module.less';
import { FormatTime } from '@/utils/format';
const FormItem = Form.Item;

const App = (props) => {
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
      dataIndex: 'type',
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
          <Button type='text' onClick={() => runFile(record.id)}>运行</Button>
          <Button type="text" size="small" onClick={() => edit(record)}>编辑</Button>
          <Modal
            title='编辑'
            visible={editVisible}
            onOk={onEditOk}
            onCancel={() => setEditVisible(false)}
            autoFocus={false}
            focusLock={true}
          >
            <Form
              form={form}
            >
              <FormItem field='id' label='ID' hidden>
                <Input />
              </FormItem>
              <FormItem field='name' label='名称' required>
                <Input />
              </FormItem>
              <FormItem field='content' label='源码' required>
                <Input.TextArea />
              </FormItem>
              <FormItem field='type' label='类型' required>
                <Select defaultValue='model_file'>
                  <Select.Option key='main' value='model_file'>
                    标准解答
                  </Select.Option>
                </Select>
              </FormItem>
            </Form>
          </Modal>
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
    listProblemFiles(props.problem.id, { fileType: 'solution' })
      .then((res) => {
        setData(res.data.data || []);
      })
      .finally(() => {
        setLoading(false);
      });
  }
  function edit(record) {
    getProblemFile(props.problem.id, record.id)
      .then(res => {
        const data = res.data;
        form.setFieldsValue({
          id: data.id,
          name: data.name,
          content: data.content,
          type: data.type,
        })
        setEditVisible(true)
      })
  }
  function deleteFile(id) {
    deleteProblemFile(props.problem.id, id)
      .then(res => {
        Message.success('删除成功');
        fetchData()
      });
  }
  function onOk() {
    form.validate().then((res) => {
      const values = {
        name: res.name,
        content: res.content,
        type: res.type,
        fileType: 'solution'
      };
      createProblemFile(props.problem.id, values)
        .then(res => {
          Message.success('已保存')
          setVisible(false)
          fetchData()
        });
    });
  }
  function onEditOk() {
    form.validate().then((res) => {
      const values = {
        name: res.name,
        content: res.content,
        type: res.type,
      };
      updateProblemFile(props.problem.id, res.id, values)
        .then(res => {
          Message.success('已保存');
          setEditVisible(false);
          fetchData();
        });
    });
  }
  function runFile(id) {
    runProblemFile(id)
      .then(res => {
        Message.info('已运行')
      })
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <div className={styles['button-group']}>
        <Space>
          <Button type='primary' onClick={() => {setVisible(true); form.clearFields}}>添加</Button>
          <Modal
            title='添加'
            visible={visible}
            onOk={onOk}
            onCancel={() => setVisible(false)}
            autoFocus={false}
            focusLock={true}
          >
            <Form
              form={form}
            >
              <FormItem field='name' label='名称' required>
                <Input />
              </FormItem>
              <FormItem field='content' label='源码' required>
                <Input.TextArea rows={10} />
              </FormItem>
              <FormItem field='type' label='类型' required>
                <Select defaultValue='model_solution'>
                  <Select.Option key='main' value='model_solution'>
                    标准解答
                  </Select.Option>
                </Select>
              </FormItem>
            </Form>
          </Modal>
        </Space>
      </div>
      <Table rowKey={r => r.id} loading={loading} columns={columns} data={data} />
    </Card>
  );
};

export default App;
