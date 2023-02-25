import { listContestUsers, updateContestUser } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, Link, Message, Modal, PaginationProps, Radio, Table } from '@arco-design/web-react';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from '../context';
import locale from '../locale';

function UpdateUserModal({visible, record, callback}: any) {
  const contest = useContext(ContestContext);
  const [form] = Form.useForm();
  const t = useLocale(locale);
  function onOk() {
    form.validate().then((values) => {
      values.userId = record.userId;
      updateContestUser(contest.id, values)
        .then(res => {
          Message.success('修改成功');
        })
        .finally(() => {
          callback();
        });
    });
  }
  useEffect(() => {
    form.setFieldsValue({
      name: record.name,
      role: record.role,
    });
  }, [record.userId]);
  return (
    <Modal
      title='修改'
      visible={visible}
      onOk={onOk}
      onCancel={() => callback()}
    >
      <Form
        form={form}
      >
        <Form.Item label='参赛名称' required field='name' rules={[{ required: true }]}>
          <Input placeholder='' />
        </Form.Item>
        <Form.Item label='选手角色' required field='role' rules={[{ required: true }]}>
          <Radio.Group>
            <Radio value='ROLE_OFFICIAL_PLAYER'>{t['setting.users.role.ROLE_OFFICIAL_PLAYER']}</Radio>
            <Radio value='ROLE_UNOFFICIAL_PLAYER'>{t['setting.users.role.ROLE_UNOFFICIAL_PLAYER']}</Radio>
            <Radio value='ROLE_WRITER'>{t['setting.users.role.ROLE_WRITER']}</Radio>
            <Radio value='ROLE_ADMIN'>{t['setting.users.role.ROLE_ADMIN']}</Radio>
          </Radio.Group>
        </Form.Item>
      </Form>
    </Modal>
  );
}

function Users() {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState([]);
  const contest = useContext(ContestContext);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100]
  });
  const [updateModal, setUpdateModal] = useState({
    visible: false,
    record: {},
  });
  const columns = [
    {
      title: t['setting.users.userId'],
      dataIndex: 'userId',
      align: 'center' as 'center',
      width: 200,
      render: col => <Link href={`/u/${col}`} target='_blank'>{col}</Link>
    },
    {
      title: t['setting.users.name'],
      dataIndex: 'name',
      align: 'center' as 'center',
    },
    {
      title: t['setting.users.role'],
      dataIndex: 'role',
      align: 'center' as 'center',
      render: col => t[`setting.users.role.${col}`]
    },
    {
      title: t['setting.users.operation'],
      dataIndex: 'operation',
      align: 'center' as 'center',
      render: (_, record) => {
        return (<Button onClick={() => setUpdateModal({visible: true, record: record})}>编辑</Button>);
      }
    }
  ];
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);

  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    listContestUsers(contest.id)
      .then(res => {
        setData(res.data.data);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
      })
      .finally(() => {
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
  function updateCallback() {
    setUpdateModal({visible: false, record: {}});
    fetchData();
  }
  return (
    <Card>
      <UpdateUserModal visible={updateModal.visible} record={updateModal.record} callback={() => updateCallback()} />
      <Table
        rowKey='id'
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={data}
      />
    </Card>
  );
};

export default Users;
