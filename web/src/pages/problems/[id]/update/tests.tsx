import React, { useEffect, useState } from 'react';
import { Button, Card, Form, Input, Message, Modal, PaginationProps, Popover, Radio, Space, Switch, Table, TableColumnProps, Upload } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/tests.module.less';
import { deleteProblemTests, listProblemTests, sortProblemTests, updateProblemTest, uploadProblemTest } from '@/api/problem-test';
import { FormatStorageSize, FormatTime } from '@/utils/format';
import { SortableContainer, SortableElement, SortableHandle } from 'react-sortable-hoc';
import { IconDragDotVertical } from '@arco-design/web-react/icon';
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
  function onSortEnd({ oldIndex, newIndex }) {
    if (oldIndex !== newIndex) {
      const newData = arrayMove([].concat(data), oldIndex, newIndex).filter((el) => !!el);
      const ids = newData.map(item => {
        return item.id
      })
      sortProblemTests(props.problem.id, {ids})
        .then(res => {
          Message.success('已保存')
        })
        .catch((err) => {
          Message.error('保存失败')
        })
      setData(newData);
    }
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

  const DraggableRow = (props) => {
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
      dataIndex: 'id',
      align: 'center',
      render: (col, record, index) => {
        return index + 1
      }
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
      render: (col, record) => col && '是'
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
      <Table
        className='drag-table-container'
        rowKey={r => r.id}
        components={components}
        loading={loading}
        columns={columns}
        data={data}
        pagination={pagination}
        rowSelection={{
          type: 'checkbox',
        }}
      />
    </Card>
  );
};

export default App;
