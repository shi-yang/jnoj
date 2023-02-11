import { createGroupUser, deleteGroupUser, listGroupUsers, updateGroupUser } from '@/api/group';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, Message, Modal, PaginationProps, Popconfirm, Radio, Table, TableColumnProps } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import { useEffect, useState } from 'react';
import locale from './locale';

export default ({group}) => {
  const t = useLocale(locale);
  const [users, setUsers] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: false,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100]
  });
  const [updateModalVisible, setUpdateModalVisible] = useState(false);
  const [updateUserId, setUpdateUserId] = useState(0);
  function removeUser(userId:number) {
    deleteGroupUser(group.id, userId)
      .then(res => {
        Message.success('已移除');
        fetchData();
      })
  }

  const columns:TableColumnProps[] = [
    {
      title: t['people.column.userId'],
      dataIndex: 'userId',
      align: 'center' as 'center',
    },
    {
      title: t['people.column.nickname'],
      dataIndex: 'nickname',
      align: 'center' as 'center',
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
                removeUser(record.userId)
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
  ]
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
      })
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

function UpdateUserModal({visible, gid, uid, callback}) {
  const t = useLocale(locale);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      updateGroupUser(gid, uid, values)
        .then(res => {
          callback();
          Message.success(t['savedSuccessfully'])
        })
        .catch(err => {
          Message.error(err.response.data.message)
        })
        .finally(() => {
          setConfirmLoading(false);
        })
    });
  }

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
            <Radio value={1}>{t['people.updateUser.form.role.manager']}</Radio>
            <Radio value={2}>{t['people.updateUser.form.role.member']}</Radio>
          </Radio.Group>
        </Form.Item>
      </Form>
    </Modal>
  );
}

function AddUser({group, callback}) {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      createGroupUser(group.id, values)
        .then(res => {
          setVisible(false);
          callback();
          Message.success(t['savedSuccessfully'])
        })
        .catch(err => {
          Message.error(err.response.data.message)
        })
        .finally(() => {
          setConfirmLoading(false);
        })
    });
  }

  return (
    <div>
      <Button type='primary' style={{ marginBottom: 10 }} icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['people.addUser']}
      </Button>
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
          <Form.Item label={t['people.addUser.form.userId']} required field='uid' rules={[{ required: true }]}>
            <Input placeholder='' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}
