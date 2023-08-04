import useLocale from '@/utils/useLocale';
import { Form, Input, Button, Card, Message, List, Radio, InputTag } from '@arco-design/web-react';
import React, { useEffect, useState } from 'react';
import locale from './locale';
import { updateProblemset } from '@/api/problemset';
import styles from './style/index.module.less';

const GroupMembership = [
  { name: '允许任何人', description: '允许任何用户参加', value: 'ALLOW_ANYONE'},
  { name: '邀请码', description: '凭借邀请码参加', value: 'INVITATION_CODE'},
];

const FormItem = Form.Item;
const App = ({problemset}: {problemset:any}) => {
  const t = useLocale(locale);
  const [form] = Form.useForm();
  function onSubmit(values) {
    updateProblemset(problemset.id, values)
      .then(res => {
        Message.info('已保存');
      })
      .catch(err => {
        Message.error(err.response.data.message);
      });
  }
  useEffect(() => {
    let invitationCode = problemset.invitationCode;
    // 简单生成一个邀请码去初始化
    if (invitationCode === '') {
      invitationCode = Date.now().toString(36);
    }
    form.setFieldsValue({
      name: problemset.name,
      description: problemset.description,
      membership: problemset.membership,
      invitationCode: invitationCode,
    });
  }, []);
  return (
    <Card>
      <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
        <FormItem field='name' label={t['name']}>
          <Input />
        </FormItem>
        <FormItem field='description' label={t['description']}>
          <Input.TextArea placeholder='' />
        </FormItem>
        <Form.Item label={t['membership']} required field='membership' rules={[{ required: true }]}
          help='邀请码情况下，你可在“用户”标签页中查看和管理可刷本题单的用户'
        >
          <Radio.Group>
            {GroupMembership.map((item, index) => {
              return (
                <Radio key={index} value={item.value}  disabled={problemset.id === 1}>
                  {item.name}
                </Radio>
              );
            })}
          </Radio.Group>
        </Form.Item>
        <Form.Item shouldUpdate noStyle>
          {(values) => {
            return values.membership === 'INVITATION_CODE' && (
              <Form.Item field='invitationCode' label={t['invitationCode']} rules={[{ required: true }]}>
                <Input />
              </Form.Item>
            );
          }}
        </Form.Item>
        <FormItem wrapperCol={{ offset: 5 }}>
          <Button type='primary' htmlType='submit'>{t['save']}</Button>
        </FormItem>
      </Form>
    </Card>
  );
};

export default App;
