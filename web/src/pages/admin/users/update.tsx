import React, { useEffect, useState } from 'react';
import { Modal, Form, Input, Message, Radio, Divider, List, Button, DatePicker, Space, Popconfirm } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createUserExpiration, deleteUserExpiration, getUser, listUserExpirations, updateUser } from '@/api/admin/user';
import { UserRole, UserStatus } from './constants';
import { FormatTime } from '@/utils/format';
import { IconDelete } from '@arco-design/web-react/icon';
const FormItem = Form.Item;

function UserExpirations({userId}: {userId: number}) {
  const [visible, setVisible] = useState(false);
  const [userExpirations, setUserExpirations] = useState<any[]>([]);
  const [form] = Form.useForm();
  const t = useLocale(locale);
  function onOk() {
    form.validate().then((values) => {
      const data = {
        type: values.type,
        startTime: new Date(values.time[0]).toISOString(),
        endTime: new Date(values.time[1]).toISOString(),
        periodValue: values.periodValue,
        endValue: values.endValue,
      };
      createUserExpiration(userId, data)
        .then(res => {
          fetchData();
          setVisible(false);
        });
    });
  }
  function onDelete(id) {
    deleteUserExpiration(id).then(res => {
      fetchData();
    });
  }
  
  function fetchData() {
    listUserExpirations(userId).then(res => {
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
                <Button iconOnly icon={<IconDelete />}></Button>
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
      <Divider />
      <UserExpirations userId={id} />
    </Modal>
  );
}

export default UpdateModal;
