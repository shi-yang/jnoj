import { listContestUsers, updateContestUser } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, Link, Message, Modal, PaginationProps, Radio, Table } from '@arco-design/web-react';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from '../context';
import locale from '../locale';

enum  ContestUserRole {
  ROLE_OFFICIAL_PLAYER = 'ROLE_OFFICIAL_PLAYER',
  ROLE_UNOFFICIAL_PLAYER = 'ROLE_UNOFFICIAL_PLAYER',
  ROLE_WRITER = 'ROLE_WRITER',
  ROLE_ADMIN = 'ROLE_ADMIN',
}

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
        <Form.Item label={t['setting.users.name']} required field='name' rules={[{ required: true }]}>
          <Input placeholder='' />
        </Form.Item>
        <Form.Item label={t['setting.users.role']} required field='role' rules={[{ required: true }]} help='请注意：出题人和管理有同样的权限，均可在任何时候查看全部选手的提交记录'>
          <Radio.Group>
            {Object.keys(ContestUserRole).map((item, index) =>
              <Radio key={index} value={item}>
                {t[`setting.users.role.${item}`]}
              </Radio>
            )}
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
  const [formParams, setFormParams] = useState({});
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
      render: col => t[`setting.users.role.${col}`],
      filters: Object.keys(ContestUserRole).map(item => {
        return {
          text: t[`setting.users.role.${item}`],
          value: item
        };
      }),
      filterMultiple: false,
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
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);

  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      per_page: pageSize,
      ...formParams,
    };
    setLoading(true);
    listContestUsers(contest.id, params)
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
  function onChangeTable({ current, pageSize }, sorter, filters) {
    console.log('filters', filters);
    setFormParams({...formParams, ...filters});
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
