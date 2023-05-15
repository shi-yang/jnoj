import React, { useEffect } from 'react';
import { Button, Card, Form, Input, Message, Tabs, Typography } from '@arco-design/web-react';
import FormItem from '@arco-design/web-react/es/Form/form-item';
import TabPane from '@arco-design/web-react/es/Tabs/tab-pane';
import { useAppDispatch, useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { updateUser, updateUserPassword } from '@/api/user';
import { setUserInfo } from '@/store/reducers/user';
import Head from 'next/head';
import { setting, SettingState } from '@/store/reducers/setting';

export default function Setting() {
  const user = useAppSelector(userInfo);
  const settings = useAppSelector<SettingState>(setting);
  const dispatch = useAppDispatch();
  const [profileForm] = Form.useForm();
  const [accountForm] = Form.useForm();
  function onProfileFormSubmit() {
    profileForm.validate().then((values) => {
      updateUser(user.id, values).then(res => {
        dispatch(setUserInfo(values));
        Message.success('已保存');
      });
    });
  }
  function onAccountFormSubmit() {
    accountForm.validate().then((values) => {
      updateUserPassword(user.id, values).then(res => {
        Message.success('已保存');
      }).catch(err => {
        Message.error(err.response.data.message);
      });
    });
  }
  useEffect(() => {
    profileForm.setFieldsValue({
      nickname: user.nickname,
    });
  }, []);
  return (
    <>
      <Head>
        <title>{`账号信息 - ${settings.name}`}</title>
      </Head>
      <div className='container'>
        <Card>
          <Tabs defaultActiveTab='profile'>
            <TabPane key='profile' title='简介'>
              <Card>
                <Form form={profileForm} style={{ width: 600 }} autoComplete='off' onSubmit={onProfileFormSubmit}>
                  <FormItem
                    field='nickname'
                    label='昵称'
                    rules={[
                      {
                        type: 'string',
                        required: true,
                        minLength: 3,
                        maxLength: 16,
                      },
                    ]}
                  >
                    <Input placeholder='请输入你的昵称' />
                  </FormItem>
                  <FormItem wrapperCol={{ offset: 5 }}>
                    <Button type='primary' htmlType='submit'>保存</Button>
                  </FormItem>
                </Form>
              </Card>
            </TabPane>
            <TabPane key='account' title='账号与安全'>
              <Card title='帐号信息'>
                <Form style={{ width: 600 }}>
                  <FormItem field='username' label='用户名'>
                    <Input disabled />
                  </FormItem>
                  <FormItem field='email' label='邮箱'>
                    <Input disabled />
                  </FormItem>
                  <FormItem field='phone' label='手机号'>
                    <Input disabled />
                  </FormItem>
                </Form>
              </Card>
              <Card title='修改密码'>
                <Form form={accountForm} style={{ width: 600 }} autoComplete='off' onSubmit={onAccountFormSubmit}>
                  <FormItem
                    field='oldPassword'
                    label='旧密码'
                    rules={[
                      {
                        type: 'string',
                        required: true,
                        minLength: 6,
                        maxLength: 16,
                      },
                    ]}
                  >
                    <Input.Password />
                  </FormItem>
                  <FormItem
                    field='newPassword'
                    label='新密码'
                    rules={[
                      {
                        type: 'string',
                        required: true,
                        minLength: 6,
                        maxLength: 16,
                      },
                    ]}
                  >
                    <Input.Password />
                  </FormItem>
                  <FormItem wrapperCol={{ offset: 5 }}>
                    <Button type='primary' htmlType='submit'>保存</Button>
                  </FormItem>
                </Form>
              </Card>
            </TabPane>
          </Tabs>
        </Card>
      </div>
    </>
  );
}
