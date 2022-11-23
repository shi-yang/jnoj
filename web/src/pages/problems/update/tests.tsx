import React, { useEffect, useState } from 'react';
import { Button, Card, Form, Input, Message, Modal, PaginationProps, Popover, Radio, Space, Switch, Table, TableColumnProps, Upload } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/tests.module.less';
import { deleteProblemTests, listProblemTests, updateProblemTest, uploadProblemTest } from '@/api/problem-test';
import { FormatStorageSize, FormatTime } from '@/utils/format';
const FormItem = Form.Item;

const App = (props) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [form] = Form.useForm();
  const [pagination, setPagination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 50,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    listProblemTests(props.problem.id, {page: current, pageSize}).then(res => {
      setData(res.data.data);
      setPagination({
        ...pagination,
        current,
        pageSize,
        total: res.data.total,
      });
    }).finally(() => {
      setLoading(false);
    })
  }
  function deleteTest(id) {
    deleteProblemTests(props.problem.id, id)
      .then(res => {
        Message.success('删除成功');
        fetchData()
      })
  }
  function customRequest(option) {
    const { onProgress, onError, onSuccess, file } = option;
    const formData = new FormData();
    formData.append('file', file);
    uploadProblemTest(props.problem.id, formData)
      .then(res => {
        Message.success('上传成功');
        fetchData();
      })
  }
  function edit(record) {
    console.log(record);
    form.setFieldsValue({
      id: record.id,
      isExample: record.isExample,
      remark: record.remark,
    })
    setVisible(true);
  }
  function onOk() {
    form.validate().then((res) => {
      const values = {
        isExample: res.isExample,
        remark: res.remark,
      }
      updateProblemTest(props.problem.id, res.id, values)
        .then(res => {
          Message.success('已保存')
          setVisible(false)
          fetchData()
        })
    });
  }

  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
      align: 'center',
      render: (col, record, index) => {
        return index + 1
      }
    },
    {
      title: t['isExample'],
      dataIndex: 'isExample',
      align: 'center',
      render: (col, record) => col && '是'
    },
    {
      title: t['content'],
      dataIndex: 'content',
    },
    {
      title: t['size'],
      dataIndex: 'inputSize',
      align: 'center',
      render: (col) => FormatStorageSize(col)
    },
    {
      title: t['remark'],
      dataIndex: 'remark',
      align: 'center',
    },
    {
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: (col) => FormatTime(col)
    },
    {
      title: t['action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Button onClick={() => edit(record)} type='primary'>
            编辑
          </Button>
          <Modal
            title='编辑'
            visible={visible}
            onOk={onOk}
            onCancel={() => setVisible(false)}
            autoFocus={false}
            focusLock={true}
          >
            <Form
              form={form}
            >
              <FormItem field='id' label='ID' hidden>
                <Input />
              </FormItem>
              <FormItem field='isExample' label='是否样例？'>
                <Radio.Group>
                  <Radio value={true}>是</Radio>
                  <Radio value={false}>否</Radio>
                </Radio.Group>
              </FormItem>
              <FormItem field='remark' label='备注'>
                <Input />
              </FormItem>
            </Form>
          </Modal>
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
  }, [pagination.current, pagination.pageSize]);
  return (
    <Card>
      <div className={styles['button-group']}>
        <Upload
          style={{width: '100%'}}
          drag
          multiple
          showUploadList={false}
          customRequest={customRequest}
        >
        </Upload>
      </div>
      <Table rowKey={r => r.id} loading={loading} columns={columns} data={data} />
    </Card>
  );
};

export default App;
