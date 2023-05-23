import React from 'react';
import Layout from '../Layout';
import { Button, Card, Form, Input, Message } from '@arco-design/web-react';
import Head from 'next/head';
import locale from './locale';
import useLocale from '@/utils/useLocale';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import styles from './style/index.module.less';
import { rejudge } from '@/api/admin/submission';

function Index() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  const [form] = Form.useForm();
  function onSubmit() {
    form.validate().then((values) => {
      rejudge(values)
        .then(res => {
          Message.info('已提交重判');
        });
    });
  }
  return (
    <>
      <Head>
        <title>{`${t['page.title']} - ${settings.name}`}</title>
      </Head>
      <div className={styles['list-container']}>
        <Card className='container'>
          <h3>对提交记录进行重判，以下三个输入框，选填其中一个</h3>
          <Form
            form={form}
            onSubmit={onSubmit}
          >
            <Form.Item label='题目ID' field='problemId'>
              <Input placeholder='' />
            </Form.Item>
            <Form.Item label='比赛ID' field='contestId'>
              <Input placeholder='' />
            </Form.Item>
            <Form.Item label='提交ID' field='submissionId'>
              <Input placeholder='' />
            </Form.Item>
            <Form.Item wrapperCol={{ offset: 5 }}>
              <Button type='primary' htmlType='submit'>
                提交
              </Button>
            </Form.Item>
          </Form>
        </Card>
      </div>
    </>
  );
}

Index.getLayout = Layout;
export default Index;
