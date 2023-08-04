import React, { useEffect } from 'react';
import { Button, Card, DatePicker, Form, Grid, Input, Message, Progress, Radio, Tabs, Tooltip, Typography, Upload } from '@arco-design/web-react';
import FormItem from '@arco-design/web-react/es/Form/form-item';
import TabPane from '@arco-design/web-react/es/Tabs/tab-pane';
import { useAppDispatch, useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { getUserProfile, updateUser, updateUserPassword, updateUserAvatar } from '@/api/user';
import { setUserInfo } from '@/store/reducers/user';
import Head from 'next/head';
import { setting, SettingState } from '@/store/reducers/setting';
import { IconEdit, IconInfoCircle, IconPlus } from '@arco-design/web-react/icon';
import dayjs from 'dayjs';
import { UploadItem } from '@arco-design/web-react/es/Upload';

function fileToBase64(file, callback) {
  const reader = new FileReader();
  reader.onloadend = function() {
    const base64String = (reader.result as string).split(',')[1];
    callback(base64String);
  };
  reader.readAsDataURL(file);
}

function UserAvatarUpload() {
  const user = useAppSelector(userInfo);
  const [file, setFile] = React.useState<UploadItem>({
    uid: user.avatar,
    url: user.avatar,
  } as UploadItem);
  const cs = `arco-upload-list-item${file && file.status === 'error' ? ' is-error' : ''}`;
  function uploadFile(option) {
    const { onError, onSuccess, file } = option;
    fileToBase64(file, (base64String) => {
      const data = {
        avatar_name: file.name,
        avatar_file: base64String,
      };
      updateUserAvatar(user.id, data)
        .then(res => {
          onSuccess(res.data);
          Message.success('上传成功');
        })
        .catch(err => {
          onError();
          Message.error('上传失败');
        });
    });
  }
  return (
    <div>
      <Upload
        customRequest={uploadFile}
        fileList={file ? [file] : []}
        showUploadList={false}
        accept='image/*'
        onChange={(_, currentFile) => {
          setFile({
            ...currentFile,
            url: URL.createObjectURL(currentFile.originFile),
          });
        }}
        onProgress={(currentFile) => {
          setFile(currentFile);
        }}
      >
        <div className={cs}>
          {file && file.url ? (
            <div className='arco-upload-list-item-picture custom-upload-avatar'>
              <img src={file.url} />
              <div className='arco-upload-list-item-picture-mask'>
                <IconEdit />
              </div>
              {file.status === 'uploading' && file.percent < 100 && (
                <Progress
                  percent={file.percent}
                  type='circle'
                  size='mini'
                  style={{
                    position: 'absolute',
                    left: '50%',
                    top: '50%',
                    transform: 'translateX(-50%) translateY(-50%)',
                  }}
                />
              )}
            </div>
          ) : (
            <div className='arco-upload-trigger-picture'>
              <div className='arco-upload-trigger-picture-text'>
                <IconPlus />
                <div style={{ fontWeight: 600 }}>上传头像</div>
              </div>
            </div>
          )}
        </div>
      </Upload>
    </div>
  );
}

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
                <Grid.Row>
                  <Grid.Col xs={24} md={12}>
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
                  </Grid.Col>
                  <Grid.Col xs={24} md={12}>
                    <UserAvatarUpload />
                  </Grid.Col>
                </Grid.Row>
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
