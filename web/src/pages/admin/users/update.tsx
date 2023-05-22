import React, { useEffect, useState } from 'react';
import { Modal, Form, Input, Message } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { getUser, updateUser } from '@/api/admin/user';
import { useRouter } from 'next/router';
const FormItem = Form.Item;

function App({id, visible, setVisible, callback}) {
  const t = useLocale(locale);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      updateUser(id, values)
        .then(res => {
          Message.info('创建成功');
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
      </Form>
    </Modal>
  );
}

export default App;
