import React, { useEffect, useState } from 'react';
import { Alert, Button, Card, Divider, Form, Grid, Input, Link, Message, Modal, PaginationProps, Popconfirm, Popover, Radio, Space, Table, TableColumnProps, Tag, Upload } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/tests.module.less';
import { deleteProblemTests, downloadProblemTest, listProblemTests, sortProblemTests, updateProblemTest, uploadProblemTest } from '@/api/problem-test';
import { FormatStorageSize, FormatTime } from '@/utils/format';
import { SortableContainer, SortableElement, SortableHandle } from 'react-sortable-hoc';
import { IconDragDotVertical } from '@arco-design/web-react/icon';
import { createProblemFile, listProblemFiles, updateProblemFile } from '@/api/problem-file';
const FormItem = Form.Item;

const arrayMoveMutate = (array, from, to) => {
  const startIndex = to < 0 ? array.length + to : to;

  if (startIndex >= 0 && startIndex < array.length) {
    const item = array.splice(from, 1)[0];
    array.splice(startIndex, 0, item);
  }
};

const arrayMove = (array, from, to) => {
  array = [...array];
  arrayMoveMutate(array, from, to);
  return array;
};

const DragHandle = SortableHandle(() => (
  <IconDragDotVertical
    style={{
      cursor: 'move',
      color: '#555',
    }}
  />
));
const SortableWrapper = SortableContainer((props) => {
  return <tbody {...props} />;
});
const SortableItem = SortableElement((props) => {
  return <tr {...props} />;
});

interface SubtaskNode {
  timeLimit?: number,
  memoryLimit?: number,
  score: number,
  tests: number[]
}

const makeSubtaskVisualization = (content:string) => {
  const subtasks:SubtaskNode[] = JSON.parse(content);
  let res = '';
  subtasks.forEach(item => {
    if (item.tests.length === 1) {
      res += '[' + item.tests[0] + '] ';
    } else if (item.tests.length > 1) {
      res += '[' + item.tests[0] + '-' + item.tests[item.tests.length - 1] + '] ';
    }
    res += item.score + (item.timeLimit ? ' ' + item.timeLimit : '') + (item.memoryLimit ? ' ' + item.memoryLimit : '');
    res += '\n';
  });
  return res;
};

const makeVisualizationSubtask = (content:string) => {
  const subtasks:SubtaskNode[] = [];
  const lines = content.split('\n');
  lines.forEach(item => {
    if (item === '') {
      return;
    }
    const node:SubtaskNode = {
      score: 0,
      tests: [],
    };
    const nums = item.match(/\d{1,}/g);
    if (item.indexOf('-') === -1) {
      node.tests.push(Number(nums[0]));
      node.score = Number(nums[1]);
      node.timeLimit = Number(nums[2]) ?? 0;
      node.memoryLimit = Number(nums[3]) ?? 0;
    } else {
      for (let i = Number(nums[0]); i <= Number(nums[1]); i++) {
        node.tests.push(i);
      }
      node.score = Number(nums[2]);
      node.timeLimit = Number(nums[3]) ?? 0;
      node.memoryLimit = Number(nums[4]) ?? 0;
    }
    subtasks.push(node);
  });
  return subtasks;
};

const App = (props: any) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [form] = Form.useForm();
  const [subtask, setSubtask] = useState({id: 0, content: ''});
  const [subtaskVisible, setSubtaskVisible] = useState(false);
  const [isSampleFirst, setIsSampleFirst] = useState(true);
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [pagination, setPagination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 50,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [50, 100, 200],
  });
  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    listProblemTests(props.problem.id, {page: current, perPage: pageSize}).then(res => {
      setData(res.data.data);
      setPagination({
        ...pagination,
        current,
        pageSize,
        total: res.data.total,
      });
      setIsSampleFirst(res.data.isSampleFirst);
    }).finally(() => {
      setLoading(false);
    });
  }
  function deleteTest(id) {
    deleteProblemTests(props.problem.id, {testIds: [id]})
      .then(() => {
        Message.success('删除成功');
        fetchData();
      });
  }
  function customRequest(option) {
    const { file } = option;
    const formData = new FormData();
    formData.append('file', file);
    uploadProblemTest(props.problem.id, formData)
      .then(res => {
        Message.success('上传成功');
        fetchData();
      });
  }
  function edit(record) {
    form.setFieldsValue({
      id: record.id,
      isExample: record.isExample,
      isTestPoint: record.isTestPoint,
      remark: record.remark,
    });
    setVisible(true);
  }
  function onOk() {
    form.validate().then((res) => {
      const values = {
        isExample: res.isExample,
        isTestPoint: res.isTestPoint,
        remark: res.remark,
      };
      updateProblemTest(props.problem.id, res.id, values)
        .then(res => {
          Message.success('已保存');
          setVisible(false);
          fetchData();
        }).catch(err => {
          Message.error(err.response.data.message);
        });
    });
  }
  function onSortEnd({ oldIndex, newIndex }) {
    if (oldIndex !== newIndex) {
      const newData = arrayMove([].concat(data), oldIndex, newIndex).filter((el) => !!el);
      const ids = newData.map(item => {
        return item.id;
      });
      sortProblemTests(props.problem.id, {ids})
        .then(res => {
          fetchData();
          Message.success('已保存');
        })
        .catch((err) => {
          Message.error('保存失败');
        });
      setData(newData);
    }
  }
  function onTableChange({ current, pageSize }) {
    setPagination({
      ...pagination,
      current,
      pageSize,
    });
  }
  function sortSampleFirst() {
    sortProblemTests(props.problem.id, {setSampleFirst: true})
      .then(res => {
        fetchData();
        Message.success('已保存');
      })
      .catch((err) => {
        Message.error('保存失败');
      });
  }
  function sortTestByName() {
    sortProblemTests(props.problem.id, {sortByName: true})
      .then(res => {
        fetchData();
        Message.success('已保存');
      })
      .catch((err) => {
        Message.error('保存失败');
      });
  }
  function onDeleteTest() {
    deleteProblemTests(props.problem.id, {testIds: selectedRowKeys})
      .then(() => {
        Message.success('删除成功');
        fetchData();
        setSelectedRowKeys([]);
      });
  }
  function onDownloadTest() {
    downloadProblemTest(props.problem.id, {testIds: selectedRowKeys})
      .then(res => {
        setSelectedRowKeys([]);
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'file.zip');
        document.body.appendChild(link);
        link.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(link);
      });
  }

  const DraggableContainer = (props) => (
    <SortableWrapper
      useDragHandle
      onSortEnd={onSortEnd}
      helperContainer={() => document.querySelector('.drag-table-container table tbody')}
      updateBeforeSortStart={({ node }) => {
        const tds = node.querySelectorAll('td');
        tds.forEach((td) => {
          td.style.width = td.clientWidth + 'px';
        });
      }}
      {...props}
    />
  );

  const DraggableRow = (props: any) => {
    const { record, index, ...rest } = props;
    return <SortableItem index={index} {...rest} />;
  };
  const components = {
    header: {
      operations: ({ selectionNode, expandNode }) => [
        {
          node: <th />,
          width: 40,
        },
        {
          name: 'expandNode',
          node: expandNode,
        },
        {
          name: 'selectionNode',
          node: selectionNode,
        },
      ],
    },
    body: {
      operations: ({ selectionNode, expandNode }) => [
        {
          node: (
            <td>
              <div className='arco-table-cell'>
                <DragHandle />
              </div>
            </td>
          ),
          width: 40,
        },
        {
          name: 'expandNode',
          node: expandNode,
        },
        {
          name: 'selectionNode',
          node: selectionNode,
        },
      ],
      tbody: DraggableContainer,
      row: DraggableRow,
    },
  };
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'order',
      align: 'center',
    },
    {
      title: t['tests.table.name'],
      dataIndex: 'name',
      align: 'center'
    },
    {
      title: t['tests.table.isExample'],
      dataIndex: 'isExample',
      align: 'center',
      render: (col, record) => <Space split={<Divider type='vertical' />}>{col && '是'}{!record.isTestPoint && <Tag color='red'>不测评</Tag>}</Space>
    },
    {
      title: t['tests.table.inputPreview'],
      dataIndex: 'inputPreview',
      render: (col) => <pre className={styles['table-pre']}>{col}</pre>
    },
    {
      title: t['tests.table.inputSize'],
      dataIndex: 'inputSize',
      align: 'center',
      render: (col) => FormatStorageSize(col)
    },
    {
      title: t['tests.table.outputPreview'],
      dataIndex: 'outputPreview',
      render: (col) => <pre className={styles['table-pre']}>{col}</pre>
    },
    {
      title: t['tests.table.outputSize'],
      dataIndex: 'outputSize',
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
              <FormItem field='isTestPoint' label='是否测评？'>
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
          <Divider type='vertical' />
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

  function onSaveSubtask() {
    form.validate().then((res) => {
      const content = makeVisualizationSubtask(res.content);
      const values = {
        content: JSON.stringify(content),
        fileType: 'subtask'
      };
      if (subtask.id !== 0) {
        updateProblemFile(props.problem.id, subtask.id, values)
          .then(res => {
            Message.success('已保存');
            setVisible(false);
            fetchData();
          })
          .catch(err => {
            Message.error(err.response.data.message);
          });
      } else {
        createProblemFile(props.problem.id, values)
          .then(res => {
            Message.success('已保存');
            setVisible(false);
            fetchData();
          })
          .catch(err => {
            Message.error(err.response.data.message);
          });
      }
    });
  }
  useEffect(() => {
    listProblemFiles(props.problem.id, {
      fileType: 'subtask'
    }).then(res => {
      const { data } = res;
      if (data.data.length > 0) {
        const subtask = res.data.data[0];
        subtask.content = makeSubtaskVisualization(subtask.content);
        setSubtask(subtask);
      }
    });
  }, []);
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);
  return (
    <Card>
      <Alert
        type='warning'
        content='注意：只需上传标准输入，无需上传输出的答案。标准输出在运行“解答文件-标准解答”时生成。上传测试点后，您可通过拖转表格行，来调整测试点的测评顺序'
      />
      <div className={styles['header']} style={{marginTop: '10px'}}>
        <Grid.Row gutter={24}>
          <Grid.Col span={12}>
            <Upload
              style={{width: '100%'}}
              drag
              multiple
              showUploadList={false}
              customRequest={customRequest}
            >
            </Upload>
          </Grid.Col>
          <Grid.Col span={12}>
            <Button
              onClick={(e) => {
                setSubtaskVisible(true);
                form.setFieldValue('content', subtask.content);
              }}
            >
              配置子任务
            </Button>
            <Modal
              title='配置子任务'
              visible={subtaskVisible}
              style={{width: '1100px'}}
              onOk={onSaveSubtask}
              onCancel={() => setSubtaskVisible(false)}
              autoFocus={false}
              focusLock={true}
            >
              <Grid.Row gutter={24}>
                <Grid.Col span={12}>
                  <Form
                    form={form}
                  >
                    <FormItem field='content' label='配置文件' required>
                      <Input.TextArea rows={8} />
                    </FormItem>
                  </Form>
                </Grid.Col>
                <Grid.Col span={12}>
                  <p>子任务说明，以下为示例代码：</p>
                  <div>
                    <pre>
                      [1-50] 60 1000 254 <br />
                      [50-99] 30 <br />
                      [100] 10 <br />
                    </pre>
                  </div>
                  <p>如以上示例，每一行表示一个子任务。其中，<code>[]</code> 内的数字为测试点的范围，接下来第一个数字为分数（必填），第二个数字为时间限制（非必填，ms），第三个数字为内存限制（非必填，MB）</p>
                </Grid.Col>
              </Grid.Row>
            </Modal>
          </Grid.Col>
        </Grid.Row>
        <Space style={{marginTop: '10px'}} split={<Divider type='vertical' />}>
          <Popconfirm
            disabled={selectedRowKeys.length === 0}
            focusLock
            title='删除选中项'
            content='继续将会把选中项测试点删除，确定？'
            onOk={onDeleteTest}  
          >
            <Button type='primary' status='danger' disabled={selectedRowKeys.length === 0}>删除选中项</Button>
          </Popconfirm>
          <Button onClick={onDownloadTest} disabled={selectedRowKeys.length === 0}>下载选中项</Button>
          <Popconfirm
            focusLock
            title='调整测评顺序'
            content='继续将会按照测试点的名称对测试点调整测评顺序，确定？'
            onOk={sortTestByName}
          >
            <Button>{t['tests.sortTestOrderByTestName']}</Button>
          </Popconfirm>
          {!isSampleFirst && (
            <div>
              {t['tests.sampleNotFirst']}
              <Popconfirm
                focusLock
                title='调整样例测评顺序'
                content='继续将会调整样例的测评顺序到最先，确定？'
                onOk={sortSampleFirst}
              >
                <Button>{t['tests.fixSample']}</Button>
              </Popconfirm>
            </div>
          )}
        </Space>
      </div>
      <Table
        className='drag-table-container'
        rowKey={r => r.id}
        components={components}
        loading={loading}
        columns={columns}
        data={data}
        pagination={pagination}
        onChange={onTableChange}
        rowSelection={{
          type: 'checkbox',
          selectedRowKeys,
          onChange: (selectedRowKeys, selectedRows) => {
            setSelectedRowKeys(selectedRowKeys);
          },
        }}
      />
    </Card>
  );
};

export default App;
