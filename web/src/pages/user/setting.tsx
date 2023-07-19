import React, { useEffect } from 'react';
import { Button, Card, DatePicker, Form, Grid, Input, Message, Radio, Tabs, Tooltip, Typography } from '@arco-design/web-react';
import FormItem from '@arco-design/web-react/es/Form/form-item';
import TabPane from '@arco-design/web-react/es/Tabs/tab-pane';
import { useAppDispatch, useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { getUserProfile, updateUser, updateUserPassword } from '@/api/user';
import { setUserInfo } from '@/store/reducers/user';
import Head from 'next/head';
import { setting, SettingState } from '@/store/reducers/setting';
import { IconInfoCircle } from '@arco-design/web-react/icon';
import dayjs from 'dayjs';

export default function Setting() {
  const user = useAppSelector(userInfo);
  const settings = useAppSelector<SettingState>(setting);
  const dispatch = useAppDispatch();
  const [profileForm] = Form.useForm();
  const [accountForm] = Form.useForm();
  function onProfileFormSubmit() {
    profileForm.validate().then((values) => {
      if (values.birthday) {
        values.birthday = dayjs(values.birthday);
      }
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
    if (!user.id) {
      return;
    }
    getUserProfile(user.id).then(res => {
      profileForm.setFieldsValue({
        nickname: user.nickname,
        bio: res.data.bio,
        gender: res.data.gender,
        birthday: res.data.birthday,
        location: res.data.location,
        school: res.data.school,
        company: res.data.company,
        job: res.data.job,
      });
    });
  }, [user.id]);
  return (
    <>
      <Head>
        <title>{`账号信息 - ${settings.name}`}</title>
      </Head>
      <div className='container'>
        <Card>
          <Tabs defaultActiveTab='profile' destroyOnHide>
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
                        minLength: 2,
                        maxLength: 16,
                      },
                    ]}
                  >
                    <Input placeholder='请输入你的昵称' />
                  </FormItem>
                  <FormItem field='bio' label='个人介绍'>
                    <Input.TextArea placeholder='关于你的个性、兴趣或经验...' maxLength={100} showWordLimit/>
                  </FormItem>
                  <FormItem
                    field='gender'
                    label='性别'
                  >
                    <Radio.Group>
                      <Radio value={1}>男性</Radio>
                      <Radio value={2}>女性</Radio>
                    </Radio.Group>
                  </FormItem>
                  <FormItem
                    field='birthday'
                    label={(
                      <div>
                        生日<Tooltip content='生日将不会对外展示'><IconInfoCircle /></Tooltip>
                      </div>
                    )}
                  >
                    <DatePicker allowClear={false} />
                  </FormItem>
                  <FormItem
                    field='location'
                    label='现居地'
                  >
                    <Input />
                  </FormItem>
                  <FormItem
                    field='school'
                    label='就读学校'
                  >
                    <Input placeholder='最高学历学校' />
                  </FormItem>
                  <FormItem
                    field='company'
                    label='所在公司'
                  >
                    <Input placeholder='最近工作公司' />
                  </FormItem>
                  <FormItem
                    field='job'
                    label='职位'
                  >
                    <Input placeholder='你的职位' />
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
