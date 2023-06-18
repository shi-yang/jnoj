import React, { useState } from 'react';
import { Modal, Button, Form, Input, Message, Radio, Space } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { batchCreateUser, createUser } from '@/api/admin/user';
const FormItem = Form.Item;

function App({callback}: {callback: () => void}) {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const [method, setMethod] = useState('one');

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      // 单一方式创建用户
      if (method === 'one') {
        createUser(values)
        .then(res => {
          Message.info('创建成功');
          setVisible(false);
        })
        .finally(() => {
          setConfirmLoading(false);
          callback();
        });
      } else {
        const close = Message.info({
          closable: true,
          duration: 0,
          content: '正在后台生成用户，你需要批量创建的用户量越多，耗时越长，请等待结果返回！'
        });
        // 批量创建用户
        const data = {
          users: [],
        };
        const users = values.user.split('\n');
        users.forEach(item => {
          const u = item.split(" ");
          if (u[0] === '') {
            return;
          }
          data.users.push({
            username: u[0],
            nickname: u[1] ?? ''
          });
        });
        batchCreateUser(data).then(res => {
          close();
          setVisible(false);
          const success = [];
          const failed = [];
          res.data.success.forEach(item => {
            success.push(`用户名：${item.username} 密码：${item.password}`);
          });
          res.data.failed.forEach(item => {
            failed.push(`用户名：${item.username} 失败原因：${item.reason}`);
          });
          Message.info({
            style: {width: '400px'},
            closable: true,
            duration: 0,
            content: (
              <div>
                {
                  success.length > 0 && (
                    <>
                      <p><strong>关闭此对话框前，请及时保存好以下密码到本地，关闭后将无法通过任何方式查看密码</strong></p>
                      <p>添加成功：{res.data.success.length}</p>
                      <div>
                        <Input.TextArea defaultValue={success.join('\n')} autoSize />
                      </div>
                    </>
                  )
                }
                {
                  failed.length > 0 && (
                    <>
                      <p>添加失败：{res.data.failed.length}</p>
                      <div>
                        <Input.TextArea defaultValue={failed.join('\n')} autoSize />
                      </div>
                    </>
                  )
                }
              </div>
            )
          });
        })
        .finally(() => {
          setConfirmLoading(false);
          callback();
        });
      }
    });
  }

  return (
    <div>
      <Button type='primary' icon={<IconPlus />} onClick={() => setVisible(true)}>
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
          <Form.Item
            label='创建方式'
            field='type'
            defaultValue={'one'}
            help={method === 'many' && '组织线下比赛时，可使用批量创建帐号功能，将帐号提供给队伍进行使用，批量创建时，用户名建议采用统一的前缀，以方便后续管理。如果用户名已经在系统中，将创建失败。'}
          >
            <Radio.Group options={[{value: 'one', label: '单一创建'}, {value: 'many', label: '批量创建'}]} onChange={setMethod}></Radio.Group>
          </Form.Item>
          {
            method === 'one' ? (
              <>
                <FormItem label='用户名' required field='username' rules={[{ required: true }]}>
                  <Input placeholder='' />
                </FormItem>
                <FormItem label='密码' required field='password' rules={[{ required: true }]}>
                  <Input placeholder='' />
                </FormItem>
                <FormItem label='昵称' field='nickname'>
                  <Input placeholder='' />
                </FormItem>
                <FormItem label='真实姓名' field='realname'>
                  <Input placeholder='' />
                </FormItem>
                <FormItem label='手机号' field='phone'>
                  <Input placeholder='' />
                </FormItem>
              </>
            ) : (
              <>
                <Form.Item
                  required
                  label='用户'
                  field='user'
                  rules={[{ required: true }]}
                  help='您可在此批量添加用户，批量添加要求：每个用户占一行，在每行中，第一个字符串为用户名，第二个字符串为用户昵称（非必须项，如果没有填写则默认取用户名），这两个字符串中间用空格分隔，用户的密码会由系统随机生成，并在创建成功后返回'
                >
                  <Input.TextArea placeholder='' autoSize={{ minRows: 4 }} />
                </Form.Item>
              </>
            )
          }
        </Form>
      </Modal>
    </div>
  );
}

export default App;
