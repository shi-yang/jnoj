import { createGroupUser, deleteGroupUser, getGroupUser, listGroupUsers, updateGroupUser } from '@/api/group';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, Link, Message, Modal, PaginationProps, Popconfirm, Radio, Table, TableColumnProps } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import { useRouter } from 'next/router';
import React, { useContext, useEffect, useState } from 'react';
import context from './context';
import Layout from './Layout';
import locale from './locale';

function People() {
  const t = useLocale(locale);
  const group = useContext(context);
  const [users, setUsers] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: false,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100],
    hideOnSinglePage: true,
    onChange: (current, pageSize) => {
      setPatination({
        ...pagination,
        current,
        pageSize,
      });
    }
  });
  const [updateModalVisible, setUpdateModalVisible] = useState(false);
  const [updateUserId, setUpdateUserId] = useState(0);
  function removeUser(userId:number) {
    deleteGroupUser(group.id, userId)
      .then(res => {
        Message.success('已移除');
        fetchData();
      });
  }

  const columns:TableColumnProps[] = [
    {
      title: t['people.column.nickname'],
      dataIndex: 'nickname',
      align: 'center' as 'center',
      render: (col, record) => <Link href={`/u/${record.userId}`}>{col}</Link>
    },
    {
      title: t['people.column.role'],
      dataIndex: 'role',
      align: 'center' as 'center',
      render: (col) => t[`role.${col}`]
    },
    {
      title: t['people.column.action'],
      dataIndex: 'action',
      align: 'center' as 'center',
      render: (_, record) => (
        <>
          { record.role !== 'ADMIN' && (group.role === 'ADMIN' || group.role === 'MANAGER') &&
            <Popconfirm
              focusLock
              title={t['people.column.action.remove?']}
              onOk={() => {
                removeUser(record.userId);
              }}
            >
              <Button type='text'>{t['people.column.action.remove']}</Button>
            </Popconfirm>
          }
          {
            (group.role === 'ADMIN' || group.role === 'MANAGER') && (record.role !== 'ADMIN') &&
            <Button type='text' onClick={() => {
              setUpdateUserId(record.userId);
              setUpdateModalVisible(true);
            }}>{t['people.column.action.edit']}</Button>
          }
        </>
      )
    }
  ];
  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      perPage: pageSize,
    };
    listGroupUsers(group.id, params)
      .then(res => {
        setUsers(res.data.data);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: Number(res.data.total),
        });
      });
  }
  function callback() {
    setUpdateModalVisible(false);
    fetchData();
  }
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);
  return (
    <Card>
      <AddUser group={group} callback={fetchData} />
      <UpdateUserModal visible={updateModalVisible} gid={group.id} uid={updateUserId} callback={callback} />
      <Table
        rowKey={r => r.id}
        data={users}
        columns={columns}
        pagination={pagination}
      />
    </Card>
  );
}

People.getLayout = Layout;
export default People;

function UpdateUserModal({visible, gid, uid, callback}: any) {
  const t = useLocale(locale);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      updateGroupUser(gid, uid, values)
        .then(res => {
          callback();
          Message.success(t['savedSuccessfully']);
        })
        .catch(err => {
          Message.error(err.response.data.message);
        })
        .finally(() => {
          setConfirmLoading(false);
        });
    });
  }

  useEffect(() => {
    if (!visible) {
      return;
    }
    getGroupUser(gid, uid).then(res => {
      form.setFieldsValue({
        role: res.data.role,
        nickname: res.data.nickname,
      });
    });
  }, [visible]);

  return (
    <Modal
      title={t['all.createProblemset']}
      visible={visible}
      onOk={onOk}
      confirmLoading={confirmLoading}
      onCancel={callback}
    >
      <Form
        form={form}
      >
        <Form.Item label={t['people.updateUser.form.role']} required field='role' rules={[{ required: true }]}>
          <Radio.Group>
            <Radio value='MANAGER'>{t['people.updateUser.form.role.manager']}</Radio>
            <Radio value='MEMBER'>{t['people.updateUser.form.role.member']}</Radio>
          </Radio.Group>
        </Form.Item>
        <Form.Item label={t['people.updateUser.form.nickname']} required field='nickname' rules={[{ required: true }]}>
          <Input />
        </Form.Item>
      </Form>
    </Modal>
  );
}

function AddUser({group, callback}: any) {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      createGroupUser(group.id, values)
        .then(res => {
          setVisible(false);
          callback();
          Message.success(t['savedSuccessfully']);
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
      {
        group.role !== 'GUEST' ? 
        <Button type='primary' style={{ marginBottom: 10 }} icon={<IconPlus />} onClick={() => setVisible(true)}>
          {t['people.addUser']}
        </Button>
        : 
        <Button type='primary' style={{ marginBottom: 10 }} icon={<IconPlus />} onClick={() => router.push(`/groups/${group.id}/join`)}>
          {t['people.joinGroup']}
        </Button>
      }
      <Modal
        title={t['all.createProblemset']}
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form
          form={form}
        >
          <Form.Item label={t['people.addUser.form.username']} required field='username' rules={[{ required: true }]}>
            <Input placeholder='' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}
