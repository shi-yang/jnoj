import { updateGroup } from '@/api/group';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, Message } from '@arco-design/web-react';
import { useEffect, useState } from 'react';
import locale from './locale';

export default ({group, callback}) => {
  const t = useLocale(locale);
  const [form] = Form.useForm();
  const [confirmLoading, setConfirmLoading] = useState(false);
  function onSubmit() {
    form.validate().then((values) => {
      const data = {
        name: values.name,
        description: values.description,
      };
      setConfirmLoading(true);
      updateGroup(group.id, data)
        .then(res => {
          Message.success('已保存');
          callback();
        })
        .finally(() => {
          setConfirmLoading(false);
        })
    });
  }
  useEffect(() => {
    form.setFieldsValue({
      name: group.name,
      description: group.description,
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
        <Form.Item wrapperCol={{ offset: 5 }}>
          <Button loading={confirmLoading} type='primary' htmlType='submit'>{t['save']}</Button>
        </Form.Item>
      </Form>
    </Card>
  );
}
