import React, { useEffect, useState } from 'react';
import {
  Button, Card, Divider, Form,
  Message, Modal, PaginationProps, Popconfirm,
  Select,
  Table, TableColumnProps, Typography
} from '@arco-design/web-react';
import {
  batchAddProblemToProblemset, deleteProblemFromProblemset,
  listProblemsetProblems, sortProblemsetProblems
} from '@/api/problemset';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { IconPlus, IconDragDotVertical } from '@arco-design/web-react/icon';
import { SortableContainer, SortableElement, SortableHandle } from 'react-sortable-hoc';
import ProblemModalList from '@/modules/problem/problem-modal-list';

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
function Problems({problemset}: {problemset:any}) {
  const problemsetId = problemset.id;
  const t = useLocale(locale);
  const [problems, setProblems] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 50,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);

  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    const params = {
      page: current,
      perPage: pageSize,
    };
    listProblemsetProblems(problemsetId, params)
      .then((res) => {
        setProblems(res.data.data);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
        setLoading(false);
      });
  }

  function onChangeTable({ current, pageSize }) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  function removeProblem(pid) {
    deleteProblemFromProblemset(problemsetId, pid)
      .then(res => {
        Message.success('已移除');
        fetchData();
      });
  }

  const columns: TableColumnProps[] = [
    {
      key: 'id',
      title: t['update.table.column.id'],
      dataIndex: 'order',
      align: 'center',
    },
    {
      key: 'problemId',
      title: t['update.table.column.problemId'],
      dataIndex: 'problemId',
      align: 'center',
    },
    {
      key: 'name',
      title: t['update.table.column.name'],
      dataIndex: 'name',
    },
    {
      key: 'action',
      title: t['update.table.column.action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Popconfirm
            focusLock
            title={t['update.table.column.action.remove.tips']}
            onOk={() => {
              removeProblem(record.order);
            }}
            onCancel={() => {
            }}
          >
            <Button>{t['update.table.column.action.remove']}</Button>
          </Popconfirm>
        </>
      ),
    },
  ];
  function onSortEnd({ oldIndex, newIndex }) {
    if (oldIndex !== newIndex) {
      const newData = arrayMove([].concat(problems), oldIndex, newIndex).filter((el) => !!el);
      const ids = newData.map(v => {
        return {
          id: v.id,
          order: v.order
        };
      });
      sortProblemsetProblems(problemsetId, {ids})
        .then(res => {
          Message.success('已保存');
          fetchData();
        })
        .catch((err) => {
          Message.error('保存失败');
        });
    }
  }

  const DraggableContainer = (props) => (
    <SortableWrapper
      useDragHandle
      onSortEnd={onSortEnd}
      helperContainer={() => document.querySelector('.arco-drag-table-container table tbody')}
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
  return (
    <Card>
      <AddProblem problemsetId={problemsetId} callback={fetchData} />
      <Table
        rowKey={r => r.id}
        className='arco-drag-table-container'
        components={components}
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={problems}
      />
    </Card>
  );
}

function AddProblem({problemsetId, callback}: {problemsetId: number, callback?:() => void}) {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      batchAddProblemToProblemset(problemsetId, values)
        .then(res => {
          if (res.data.failedReason.length > 0) {
            Message.error({
              content: (
                <div>
                  {res.data.failedReason.map(v => (
                    <Typography.Paragraph key={v} style={{marginBottom: 0}}>
                      {v}
                    </Typography.Paragraph>
                  ))}
                </div>
              )
            });
          }
          setVisible(false);
          callback();
        })
        .catch(err => {
          Message.error(err.response.data.message);
        })
        .finally(() => {
          setConfirmLoading(false);
        });
    });
  }

  return (
    <div>
      <Button type="primary" style={{ marginBottom: 10 }} icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['update.table.add']}
      </Button>
      <Modal
        title={t['update.table.add']}
        visible={visible}
        onOk={onOk}
        style={{width: 1100}}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <ProblemModalList onChange={(v) => {
          form.setFieldValue('problemIds', v);
        }} />
        <Divider />
        <Form
          form={form}
        >
          <Form.Item  label={t['update.table.add.form.problemId']} required field='problemIds' rules={[{ required: true }]}>
            <Select mode='multiple' allowClear allowCreate></Select>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}

export default Problems;
