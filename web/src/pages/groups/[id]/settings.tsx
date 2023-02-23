import { updateGroup } from '@/api/group';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, Message, Radio } from '@arco-design/web-react';
import React, { useEffect, useState } from 'react';
import locale from './locale';

export default function Settings({group, callback}: any) {
  const t = useLocale(locale);
  const [form] = Form.useForm();
  const [confirmLoading, setConfirmLoading] = useState(false);
  function onSubmit() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      updateGroup(group.id, values)
        .then(res => {
          Message.success('已保存');
          callback();
        })
        .finally(() => {
          setConfirmLoading(false);
        });
    });
  }
  useEffect(() => {
    let invitationCode = group.invitationCode;
    // 简单生成一个邀请码去初始化
    if (invitationCode === '') {
      invitationCode = Date.now().toString(36);
    }
    form.setFieldsValue({
      name: group.name,
      description: group.description,
      privacy: group.privacy,
      membership: group.membership,
      invitationCode: invitationCode,
    });
  }, []);
  return (
    <Card>
      <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
        <Form.Item label={t['settings.form.name']} required field='name' rules={[{ required: true }]}>
          <Input placeholder='' />
        </Form.Item>
        <Form.Item label={t['settings.form.description']} field='description'>
          <Input.TextArea placeholder='' />
        </Form.Item>
        <Form.Item label={t['settings.form.privacy']} field='privacy' required rules={[{ required: true }]}>
          <Radio.Group>
            <Radio value={0}>{t['settings.form.privacy.private']}</Radio>
            <Radio value={1}>{t['settings.form.privacy.public']}</Radio>
          </Radio.Group>
        </Form.Item>
        <Form.Item label={t['settings.form.membership']} field='membership' required rules={[{ required: true }]}>
          <Radio.Group>
            <Radio value={0}>{t['settings.form.membership.allowAnyone']}</Radio>
            <Radio value={1}>{t['settings.form.membership.invitationCode']}</Radio>
          </Radio.Group>
        </Form.Item>
        <Form.Item shouldUpdate noStyle>
          {(values) => {
            return values.membership === 1 && (
              <Form.Item field='invitationCode' label={t['settings.form.membership.invitationCode']} rules={[{ required: true }]}>
                <Input />
              </Form.Item>
            );
          }}
        </Form.Item>
        <Form.Item wrapperCol={{ offset: 5 }}>
          <Button loading={confirmLoading} type='primary' htmlType='submit'>{t['save']}</Button>
        </Form.Item>
      </Form>
    </Card>
  );
}
