import { batchCreateContestUsers, calculateContestRating, createContestUser, listContestUsers, updateContestUser } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, Link, Message, Modal, PaginationProps, Popconfirm, Radio, Space, Table, Typography } from '@arco-design/web-react';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from '../context';
import locale from '../locale';
import user from '@/store/reducers/user';
import PermissionWrapper from '@/components/PermissionWrapper';

enum  ContestUserRole {
  ROLE_OFFICIAL_PLAYER = 'ROLE_OFFICIAL_PLAYER',
  ROLE_UNOFFICIAL_PLAYER = 'ROLE_UNOFFICIAL_PLAYER',
  ROLE_VIRTUAL_PLAYER = 'ROLE_VIRTUAL_PLAYER',
  ROLE_WRITER = 'ROLE_WRITER',
  ROLE_ADMIN = 'ROLE_ADMIN',
}

function CreateUserModal({callback}: {callback: () => void}) {
  const contest = useContext(ContestContext);
  const [visible, setVisible] = useState(false);
  const t = useLocale(locale);
  const [form] = Form.useForm();
  function onOk() {
    form.validate().then((values) => {
      const data = {
        users: [],
        role: values.role,
      };
      const users = values.name.split('\n');
      users.forEach(item => {
        const u = item.split(" ");
        if (u[0] === '') {
          return;
        }
        data.users.push({
          username: u[0],
          name: u[1] ?? ''
        });
      });
      batchCreateContestUsers(contest.id, data)
        .then(res => {
          if (res.data.failed.length === 0) {
            Message.success({
              content: (
                <div>
                  所有用户已经成功添加：{res.data.success.length}
                </div>
              )
            });
          } else {
            const failed = res.data.failed.map(item => {
              return item.username + ' ' + item.name;
            });
            const failedReason = res.data.failed.map(item => {
              return item.reason;
            });
            Message.error({
              closable: true,
              duration: 0,
              content: (
                <div>
                  <p>成功添加：{res.data.success.length}</p>
                  <p>以下用户添加失败：</p>
                  <Space>
                    <div>
                      <p>失败用户</p>
                      <Input.TextArea defaultValue={failed.join('\n')} autoSize />
                    </div>
                    <div>
                      <p>失败原因</p>
                      <Input.TextArea defaultValue={failedReason.join('\n')} autoSize />
                    </div>
                  </Space>
                </div>
              )
            });
          }
        })
        .finally(() => {
          callback();
        });
    });
  }
  return (
    <>
      <Button type='primary' onClick={(e) => setVisible(true)}>添加用户</Button>
      <Modal
        visible={visible}
        onOk={onOk}
        onCancel={() => setVisible(false)}
        title='添加用户'
      >
        <Form
          form={form}
        >
          <Form.Item
            label={t['setting.users.name']}
            required
            field='name'
            rules={[{ required: true }]}
            help='您可在此批量添加参赛用户，批量添加要求：每个参赛用户占一行，在每行中，第一个字符串为用户名，第二个字符串为参赛名称（非必须项，如果没有填写则默认取用户昵称），这两个字符串中间用空格分隔'
          >
            <Input.TextArea placeholder='' autoSize={{ minRows: 4 }} />
          </Form.Item>
          <Form.Item label={t['setting.users.role']} required field='role' rules={[{ required: true }]} help='请注意：出题人和管理有同样的权限，均可在任何时候查看全部选手的提交记录'>
            <Radio.Group>
              {Object.keys(ContestUserRole).map((item, index) =>
                <Radio key={index} value={item} disabled={item === 'ROLE_VIRTUAL_PLAYER'}>
                  {t[`setting.users.role.${item}`]}
                </Radio>
              )}
            </Radio.Group>
          </Form.Item>
        </Form>
      </Modal>
    </>
  );
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
              <Radio key={index} value={item} disabled={item === 'ROLE_VIRTUAL_PLAYER'}>
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
      title: t['setting.users.userNickname'],
      dataIndex: 'userNickname',
      align: 'center' as 'center',
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
      title: 'Δ',
      dataIndex: 'newRating',
      align: 'center' as 'center',
      render: (_, record) => {
        const changed = record.newRating - record.oldRating;
        return changed > 0 ? (
          <Typography.Text type='success' bold>+{changed}</Typography.Text>
        ) : (
          <Typography.Text type='secondary' bold>{changed}</Typography.Text>
        );
      }
    },
    {
      title: t['setting.users.rating'],
      dataIndex: 'rating',
      align: 'center' as 'center',
      render: (_, record) => (
        <div>
          <span>{record.oldRating} → {record.newRating}</span>
        </div>
      ),
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
  function onCalculateRating() {
    calculateContestRating(contest.id).then(res => {
      Message.success('已更新');
      fetchData();
    });
  }
  return (
    <Card>
      <div style={{marginBottom: '10px'}}>
        <Space>
          <CreateUserModal callback={fetchData} />
          <UpdateUserModal visible={updateModal.visible} record={updateModal.record} callback={() => updateCallback()} />
          {
            contest.feature.includes('rated') && (
              <PermissionWrapper
                requiredPermissions={[{resource: 'contest', actions: ['write']}]}
              >
                <Popconfirm
                  focusLock
                  title='Rated'
                  content='确定将计算本场比赛参赛用户的积分'
                  onOk={onCalculateRating}
                >
                  <Button type='primary' status='warning'>Rated</Button>
                </Popconfirm>
              </PermissionWrapper>
            )
          }
        </Space>
      </div>
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
