import React, { useEffect, useState } from 'react';
import {
  Avatar,
  Button, Card, Form,
  Link,
  Message, Modal, PaginationProps, Popconfirm,
  Table, TableColumnProps
} from '@arco-design/web-react';
import {
  createProblemsetUser,
  deleteProblemsetUser,
  listProblemsetUsers
} from '@/api/problemset';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { IconPlus } from '@arco-design/web-react/icon';
import { FormatTime } from '@/utils/format';
import SearchInputUsername from '@/components/User/SearchInputUsername';

function Page({problemset}: {problemset:any}) {
  const problemsetId = problemset.id;
  const t = useLocale(locale);
  const [users, setUsers] = useState([]);
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
    listProblemsetUsers(problemsetId, params)
      .then((res) => {
        setUsers(res.data.data);
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
  function removeUser(uid) {
    deleteProblemsetUser(problemsetId, uid)
      .then(res => {
        Message.success('已移除');
        fetchData();
      });
  }

  const columns: TableColumnProps[] = [
    {
      key: 'username',
      title: t['username'],
      dataIndex: 'user.username',
      align: 'center',
    },
    {
      key: 'user',
      title: t['user'],
      dataIndex: 'user',
      align: 'center',
      render: (_, record) => (
        <>
          <Link href={`/u/${record.userId}`} target='_blank'>
            {
              record.user.avatar  && (
                <Avatar size={18}>
                  <img src={record.user.avatar} alt='user avatar' />
                </Avatar>
              )
            } {record.user.nickname}
          </Link>
        </>
      )
    },
    {
      key: 'accepted',
      title: t['acceptedCount'],
      dataIndex: 'acceptedCount',
      align: 'center',
    },
    {
      key: 'initialScore',
      title: t['initialScore'],
      dataIndex: 'initialScore',
      align: 'center',
      render: col => col < 0 ? '-' : col
    },
    {
      key: 'bestScore',
      title: t['bestScore'],
      dataIndex: 'bestScore',
      align: 'center',
    },
    {
      key: 'createdAt',
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: col => FormatTime(col)
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
              removeUser(record.user.id);
            }}
            onCancel={() => {
            }}
          >
            <Button status='warning'>{t['update.table.column.action.remove']}</Button>
          </Popconfirm>
        </>
      ),
    },
  ];

  return (
    <Card>
      <AddUser problemsetId={problemsetId} callback={fetchData} />
      <Table
        rowKey={r => r.id}
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={users}
      />
    </Card>
  );
}

function AddUser({problemsetId, callback}: {problemsetId: number, callback?:() => void}) {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      createProblemsetUser(problemsetId, values)
        .then(res => {
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
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
        style={{width: 800}}
      >
        <Form
          form={form}
        >
          <Form.Item  label={t['user']} required field='username' rules={[{ required: true }]}>
            <SearchInputUsername />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}

export default Page;
