import React, { useEffect, useState } from 'react';
import { Modal, Form, Input, Message, Radio } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { getUser, updateUser } from '@/api/admin/user';
import { UserRole } from './constants';
const FormItem = Form.Item;

function App({id, visible, setVisible, callback}: {id: number, visible: boolean, setVisible: any, callback: () => void}) {
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
          role: res.data.role,
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
    >
      <Form
        form={form}
      >
        <FormItem label='用户名' required field='username' rules={[{ required: true }]}>
          <Input placeholder='' />
        </FormItem>
        <FormItem label='密码' required field='password'>
          <Input placeholder='' />
        </FormItem>
        <FormItem label='昵称' field='nickname'>
          <Input placeholder='' />
        </FormItem>
        <FormItem label='角色' field='role' help={t[`user.form.user.role.tip.${UserRole[role]}`]}>
          <Radio.Group
            type='button'
            name='lang'
            onChange={e => setRole(e)}
            style={{ marginRight: 20, marginBottom: 20 }}
          >
            {Object.keys(UserRole).map(key => (
              <Radio key={key} value={UserRole[key]} disabled={UserRole[key] === 'SUPER_ADMIN'}>
                {t[`user.form.user.role.${UserRole[key]}`]}
              </Radio>
            ))}
          </Radio.Group>
        </FormItem>
      </Form>
    </Modal>
  );
}

export default App;
