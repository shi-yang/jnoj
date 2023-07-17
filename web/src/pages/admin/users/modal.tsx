import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Modal, Form, Input, Message, Radio, Divider, List, Button, DatePicker, Space, Popconfirm, Card, Avatar, Select, Spin } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createUserExpiration, createUserUserBadge, deleteUserExpiration, deleteUserUserBadge, getUser, listUserBadges, listUserExpirations, listUserUserBadges, updateUser } from '@/api/admin/user';
import { UserRole, UserStatus } from './constants';
import { FormatTime } from '@/utils/format';
import { IconDelete } from '@arco-design/web-react/icon';
import dayjs from 'dayjs';
const FormItem = Form.Item;
import debounce from 'lodash/debounce';


function CreateUserExpirationModal({userIds, visible, setVisible, callback}: {userIds: number[], visible: boolean, setVisible: (visible: boolean) => void, callback: () => void}) {
  const [form] = Form.useForm();
  const t = useLocale(locale);
  function onOk() {
    form.validate().then((values) => {
      const data = {
        userId: userIds,
        type: values.type,
        startTime: new Date(values.time[0]).toISOString(),
        endTime: new Date(values.time[1]).toISOString(),
        periodValue: values.periodValue,
        endValue: values.endValue,
      };
      createUserExpiration(data).then(() => {
        callback();
        setVisible(false);
        Message.success('添加成功');
      });
    });
  }
  return (
    <Modal
      visible={visible}
      title='添加有效期事件'
      onOk={onOk}
      onCancel={() => setVisible(false)}
      style={{width: 800}}
    >
      <Form
        form={form}
      >
        <FormItem label='类型' required field='type' rules={[{ required: true }]}>
          <Radio.Group
            type='button'
          >
            <Radio value='ROLE'>角色</Radio>
            <Radio value='STATUS'>状态</Radio>
          </Radio.Group>
        </FormItem>
        <FormItem label='时间范围' required field='time' rules={[{ required: true }]}>
          <DatePicker.RangePicker
            showTime={{
              format: 'HH:mm:ss',
            }}
            format='YYYY-MM-DD HH:mm:ss'
            shortcutsPlacementLeft
            shortcuts={[
              {
                text: 'next 2 days',
                value: () => [dayjs(), dayjs().add(2, 'day')],
              },
              {
                text: 'next 7 days',
                value: () => [dayjs(), dayjs().add(1, 'week')],
              },
              {
                text: 'next 30 days',
                value: () => [dayjs(), dayjs().add(1, 'month')],
              },
              {
                text: 'next 6 months',
                value: () => [dayjs(), dayjs().add(6, 'month')],
              },
              {
                text: 'next 12 months',
                value: () => [dayjs(), dayjs().add(1, 'year')],
              },
              {
                text: 'next 10 years',
                value: () => [dayjs(), dayjs().add(10, 'year')],
              },
            ]}
          />
        </FormItem>
        <FormItem label='时间范围期间的值' shouldUpdate required field='periodValue' rules={[{ required: true }]}>
          {(values) => {
            return values.type === 'ROLE' ? (
              <Radio.Group
                type='button'
              >
                {Object.keys(UserRole).map(key => (
                  <Radio key={key} value={UserRole[key]} disabled={UserRole[key] === 'SUPER_ADMIN'}>
                    {t[`user.form.user.role.${UserRole[key]}`]}
                  </Radio>
                ))}
              </Radio.Group>
            ) : (
              <Radio.Group
                type='button'
              >
                {Object.keys(UserStatus).map(key => (
                  <Radio key={key} value={UserStatus[key]}>
                    {t[`user.form.user.status.${UserStatus[key]}`]}
                  </Radio>
                ))}
              </Radio.Group>
            );
          }}
        </FormItem>
        <FormItem label='结束后的值' shouldUpdate required field='endValue' rules={[{ required: true }]}>
          {(values) => {
            return values.type === 'ROLE' ? (
              <Radio.Group
                type='button'
              >
                {Object.keys(UserRole).map(key => (
                  <Radio key={key} value={UserRole[key]} disabled={UserRole[key] === 'SUPER_ADMIN'}>
                    {t[`user.form.user.role.${UserRole[key]}`]}
                  </Radio>
                ))}
              </Radio.Group>
            ) : (
              <Radio.Group
                type='button'
              >
                {Object.keys(UserStatus).map(key => (
                  <Radio key={key} value={UserStatus[key]}>
                    {t[`user.form.user.status.${UserStatus[key]}`]}
                  </Radio>
                ))}
              </Radio.Group>
            );
          }}
        </FormItem>
      </Form>
    </Modal>
  );
}

function UserExpirationList({userId}: {userId: number}) {
  const [visible, setVisible] = useState(false);
  const [userExpirations, setUserExpirations] = useState<any[]>([]);
  const t = useLocale(locale);
  function onDelete(id) {
    deleteUserExpiration(id).then(res => {
      fetchData();
    });
  }
  function fetchData() {
    listUserExpirations({userId: [userId]}).then(res => {
      setUserExpirations(res.data.data);
    });
  }
  useEffect(() => {
    fetchData();
  }, [userId]);
  return (
    <List
      size='small'
      header={
        <div>
          <Space>
            <span>
              设定有效期事件
            </span>
            <Button type='primary' onClick={() => setVisible(true)}>添加</Button>
          </Space>
          <div style={{fontSize: 12, color: 'var(--color-neutral-6)'}}>有效期事件用于设定用户角色、状态的可用时间，在规定时间后自动变更状态</div>
          <CreateUserExpirationModal userIds={[userId]} visible={visible} setVisible={setVisible} callback={fetchData}  />
        </div>
      }
      dataSource={userExpirations}
      render={(item, index) => 
        <List.Item key={index}>
          <Space split={<Divider type='vertical' />}>
            <div>类型：{item.type === 'ROLE' ? t['user.form.user.role'] : t['user.form.user.status']}</div>
            <div>有效期：{FormatTime(item.startTime)} - {FormatTime(item.endTime)}</div>
            <div>有效期内：{item.type === 'ROLE' ? t['user.form.user.role.' + item.periodValue] : t['user.form.user.status.' + item.periodValue]}</div>
            <div>有效期后：{item.type === 'ROLE' ? t['user.form.user.role.' + item.endValue] : t['user.form.user.status.' + item.endValue]}</div>
            <Popconfirm
              focusLock
              content='确定删除?'
              onOk={() => onDelete(item.id)}
            >
              <Button icon={<IconDelete />}>删除</Button>
            </Popconfirm>
          </Space>
        </List.Item>
      }
    />
  );
}

function CreateUserBadgeModal({userId, visible, setVisible, callback}: {userId: number, visible: boolean, setVisible: (visible: boolean) => void, callback: () => void}) {
  const [form] = Form.useForm();
  const t = useLocale(locale);
  const [options, setOptions] = useState([]);
  const [fetching, setFetching] = useState(false);
  const refFetchId = useRef(null);
  useEffect(() => {
    const params = {};
    listUserBadges(params).then(res => {
      const options = res.data.data.map((item) => ({
        label: (
          <div style={{ display: 'flex', alignItems: 'center' }}>
            <Avatar size={24} style={{ marginLeft: 6, marginRight: 12 }}>
              <img alt='avatar' src={item.image} />
            </Avatar>
            <Space>
              {item.type}
              {item.name}
            </Space>
          </div>
        ),
        value: item.id,
      }));
      setOptions(options);
    });
  }, []);
  const debouncedFetchUserBadge = useCallback(
    debounce((inputValue) => {
      refFetchId.current = Date.now();
      const fetchId = refFetchId.current;
      const params = {
        name: inputValue,
      };
      setFetching(true);
      setOptions([]);
      listUserBadges(params).then((res) => {
        const options = res.data.data.map((item) => ({
          label: (
            <div style={{ display: 'flex', alignItems: 'center' }}>
              <Avatar size={24} style={{ marginLeft: 6, marginRight: 12 }}>
                <img alt='avatar' src={item.image} />
              </Avatar>
              <Space>
                {item.type}
                {item.name}
              </Space>
            </div>
          ),
          value: item.id,
        }));
        setFetching(false);
        setOptions(options);
      });
    }, 500),
    []
  );
  function onOk() {
    form.validate().then((values) => {
      const data = {
        badgeId: values.badgeId,
        createdAt: new Date(values.time).toISOString(),
      };
      createUserUserBadge(userId, data).then(() => {
        Message.success('添加成功');
        setVisible(false);
        callback();
      });
    });
  }
  return (
    <Modal
      visible={visible}
      title='添加用户勋章'
      onOk={onOk}
      onCancel={() => setVisible(false)}
      style={{width: 800}}
    >
      <Form
        form={form}
      >
        <FormItem label='勋章' required field='badgeId' rules={[{ required: true }]}>
          <Select
            style={{ width: 345 }}
            showSearch
            options={options}
            placeholder='Search by name'
            filterOption={false}
            notFoundContent={
              fetching ? (
                <div
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                  }}
                >
                  <Spin style={{ margin: 12 }} />
                </div>
              ) : null
            }
            onSearch={debouncedFetchUserBadge}
          />
        </FormItem>
        <FormItem label='获得时间' required field='time' rules={[{ required: true }]}>
          <DatePicker
            showTime={{
              format: 'HH:mm:ss',
            }}
            format='YYYY-MM-DD HH:mm:ss'
          />
        </FormItem>
      </Form>
    </Modal>
  );
}

function UserBadgeList({userId}: {userId: number}) {
  const [visible, setVisible] = useState(false);
  const [userBadges, setUserBadges] = useState<any[]>([]);
  const t = useLocale(locale);
  function onDelete(id) {
    deleteUserUserBadge(userId, id).then(res => {
      fetchData();
    });
  }
  function fetchData() {
    listUserUserBadges(userId, {}).then(res => {
      setUserBadges(res.data.data);
    });
  }
  useEffect(() => {
    fetchData();
  }, [userId]);
  return (
    <List
      size='small'
      header={
        <div>
          <Button type='primary' onClick={() => setVisible(true)}>添加</Button>
          <div style={{fontSize: 12, color: 'var(--color-neutral-6)'}}>用户勋章用于激励用户，让用户有成就感</div>
          <CreateUserBadgeModal userId={userId} visible={visible} setVisible={setVisible} callback={fetchData}  />
        </div>
      }
      dataSource={userBadges}
      render={(item, index) => 
        <List.Item key={index}>
          <Space split={<Divider type='vertical' />}>
            <div>
              <Avatar size={24} style={{ marginLeft: 6, marginRight: 12 }}>
                <img alt='avatar' src={item.badge.image} />
              </Avatar>
            </div>
            <div>勋章名称：{item.badge.name}</div>
            <div>勋章类型：{item.badge.type}</div>
            <div>获得时间：{FormatTime(item.createdAt)}</div>
            <Popconfirm
              focusLock
              content='确定删除?'
              onOk={() => onDelete(item.id)}
            >
              <Button icon={<IconDelete />}>删除</Button>
            </Popconfirm>
          </Space>
        </List.Item>
      }
    />
  );
}

function UpdateModal({id, visible, setVisible, callback}: {id: number, visible: boolean, setVisible: any, callback: () => void}) {
  const t = useLocale(locale);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const [role, setRole] = useState('REGULAR_USER');

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      updateUser(id, values)
        .then(res => {
          Message.info('修改成功');
          setVisible(false);
        })
        .finally(() => {
          setConfirmLoading(false);
          callback();
        });
    });
  }

  useEffect(() => {
    if (visible) {
      getUser(id).then(res => {
        form.setFieldsValue({
          nickname: res.data.nickname,
          username: res.data.username,
          realname: res.data.realname,
          password: '',
          role: res.data.role,
          status: res.data.status,
        });
      });
    }
  }, [visible]);

  return (
    <Modal
      title='修改用户'
      visible={visible}
      onOk={onOk}
      confirmLoading={confirmLoading}
      onCancel={() => setVisible(false)}
      style={{width: 1000}}
    >
      <Card title='常规信息'>
        <Form
          form={form}
        >
          <FormItem label='用户名' required field='username' rules={[{ required: true }]}>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='密码' field='password'>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='昵称' field='nickname'>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='真实姓名' field='realname'>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='角色' field='role' help={t[`user.form.user.role.tip.${UserRole[role]}`]}>
            <Radio.Group
              type='button'
              onChange={e => setRole(e)}
            >
              {Object.keys(UserRole).map(key => (
                <Radio key={key} value={UserRole[key]} disabled={UserRole[key] === 'SUPER_ADMIN'}>
                  {t[`user.form.user.role.${UserRole[key]}`]}
                </Radio>
              ))}
            </Radio.Group>
          </FormItem>
          <FormItem label='状态' field='status' help='处于“禁用”的帐号无法登录、使用。'>
            <Radio.Group
              type='button'
            >
              {Object.keys(UserStatus).map(key => (
                <Radio key={key} value={UserStatus[key]}>
                  {t[`user.form.user.status.${UserStatus[key]}`]}
                </Radio>
              ))}
            </Radio.Group>
          </FormItem>
        </Form>
      </Card>
      <Card title='有效期事件'>
        <UserExpirationList userId={id} />
      </Card>
      <Card title='用户勋章'>
        <UserBadgeList userId={id} />
      </Card>
    </Modal>
  );
}

export {UpdateModal, CreateUserExpirationModal};

export default () => {};
