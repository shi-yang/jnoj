import React, { useState } from 'react';
import { Modal, Button, Form, Input, Message } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createUser } from '@/api/user';
import { useRouter } from 'next/router';
const FormItem = Form.Item;

function App() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      createUser(values)
        .then(res => {
          Message.info('创建成功');
        })
        .finally(() => {
          setConfirmLoading(false);
        });
    });
  }

  return (
    <div>
      <Button type="primary" icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['searchTable.operations.add']}
      </Button>
      <Modal
        title='创建用户'
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
          <FormItem label='密码' required field='password' rules={[{ required: true }]}>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='昵称' field='nickname'>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='手机号' field='phone'>
            <Input placeholder='' />
          </FormItem>
        </Form>
      </Modal>
    </div>
  );
}

export default App;
