import useLocale from '@/utils/useLocale';
import { Form, Input, Button, Card, Message, List, Radio, InputTag } from '@arco-design/web-react';
import React, { useEffect, useState } from 'react';
import locale from './locale';
import { updateProblemset } from '@/api/problemset';
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
    form.setFieldsValue({
      name: problemset.name,
      description: problemset.description,
    });
  }, []);
  return (
    <>
      <Card title='基本信息'>
        <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
          <FormItem field='name' label={t['name']}>
            <Input />
          </FormItem>
          <FormItem field='description' label={t['description']}>
            <Input.TextArea placeholder='' />
          </FormItem>
          <FormItem wrapperCol={{ offset: 5 }}>
            <Button type='primary' htmlType='submit'>{t['save']}</Button>
          </FormItem>
        </Form>
      </Card>
    </>
  );
};

export default App;
