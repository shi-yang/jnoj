import React, { useEffect, useState } from 'react';
import { Button, Card, Form, Input, Message, Modal, Popover, Radio, Select, Space, Table, TableColumnProps, Typography, Upload } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import { listProblemFiles, createProblemFile, deleteProblemFile, getProblemFile, updateProblemFile, runProblemFile, uploadProblemFile } from '@/api/problem-file';
import locale from './locale';
import styles from './style/tests.module.less';
import { FormatTime } from '@/utils/format';
const FormItem = Form.Item;

const FileType = {
  'solution': '解答方案',
  'attachment': '附件',
  'statement': '题目描述相关文件',
  'package': '打包文件',
  'language': '语言文件',
  'subtask': '子任务定义',
};

const App = (props: any) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
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
      render: col => FileType[col]
    },
    {
      title: '外链',
      dataIndex: 'externalUrl',
      align: 'center',
      render: (col, record) => record.fileType === 'statement' && (
        <Typography.Paragraph copyable>{record.content}</Typography.Paragraph>
      )
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
  function onSubmit() {
    form.validate().then((values) => {
      const formData = new FormData();
      setConfirmLoading(true);
      formData.append('file', values.upload[0].originFile);
      formData.append('fileType', 'statement');
      uploadProblemFile(props.problem.id, formData)
        .then(res => {
          Message.success('上传成功');
          setVisible(false);
        })
        .catch(err => {
          Message.error(err.response.data.message);
        })
        .finally(() => {
          setConfirmLoading(false);
          fetchData();
        });
    });
  }
  useEffect(() => {
    fetchData();
  }, []);
  useEffect(() => {
    form.resetFields();
  }, [visible]);
  return (
    <Card>
      <Button
        type='primary'
        style={{marginBottom: '10px'}}
        onClick={() => setVisible(true)}
      >
        上传
      </Button>
      <Modal visible={visible} onOk={onSubmit} onCancel={() => setVisible(false)}>
        <Form form={form} disabled={confirmLoading}>
          <Form.Item
            label='类型'
            field='type'
            required
            initialValue={'statement'}
            help={<div>类型属于“题目描述相关文件”，可有对应链接用于在题目描述中展示。</div>}
          >
            <Radio.Group>
              <Radio value='statement'>题目描述相关文件</Radio>
            </Radio.Group>
          </Form.Item>
          <Form.Item label='文件' required triggerPropName='fileList' field='upload'>
            <Upload
              multiple={false}
              drag
              limit={1}
              autoUpload={false}
            >
            </Upload>
          </Form.Item>
        </Form>
      </Modal>
      <Table rowKey={r => r.id} loading={loading} columns={columns} data={data} />
    </Card>
  );
};

export default App;
