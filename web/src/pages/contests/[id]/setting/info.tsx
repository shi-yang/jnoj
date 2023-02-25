import { createContestProblem, deleteContestProblem, listContestProblems, updateContest } from '@/api/contest';
import Editor from '@/components/MarkdownEditor';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, DatePicker, List, Avatar, Modal, Message, Radio, Space, Typography, Popconfirm, Grid } from '@arco-design/web-react';
import { IconDelete, IconPlus } from '@arco-design/web-react/icon';
import dayjs from 'dayjs';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from '../context';
import locale from '../locale';
const { RangePicker } = DatePicker;
import styles from '../style/setting.module.less';

const AddProblem = ({contestId, callback}: {contestId: number, callback: () => void}) => {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  function onOk() {
    form.validate().then((values) => {
      const data = {
        problemId: values.problemId,
      };
      setConfirmLoading(true);
      createContestProblem(contestId, data)
        .then(res => {
          setVisible(false);
          callback();
        })
        .catch(res => {
          Message.error(res.response.data.message || '出错了');
        })
        .finally(() => {
          setConfirmLoading(false);
        });
    });
  }
  return (
    <div>
      <Button type='primary' icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['setting.info.addProblem']}
      </Button>
      <Modal
        title={t['setting.info.addProblem']}
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form form={form}>
          <Form.Item label='题目ID' required field='problemId' rules={[{ required: true }]}>
            <Input placeholder='' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
};

const ContestType = [
  { name: 'ICPC', description: 'International Collegiate Programming Contest', value: 1 },
  { name: 'IOI', description: 'International Olympiad in Informatics', value: 2 },
  { name: 'OI', description: 'Olympiad in Informatics', value: 3 },
];

const ContestPrivacy = [
  { name: '私有', description: '比赛的任何信息、任何时候都仅对参赛用户可见。私有比赛不意味着只有受邀用户才能参加，请注意参赛设置', value: 'PRIVATE'},
  { name: '公开', description: '设为公开时，比赛介绍、榜单全程任何用户均可见，但比赛未结束前，题目、提交信息仅对参赛用户可见', value: 'PUBLIC'},
];

const ContestMembership = [
  { name: '允许任何人', description: '允许任何用户参加', value: 'ALLOW_ANYONE'},
  { name: '邀请码', description: '凭借邀请码参加', value: 'INVITATION_CODE'},
  { name: '小组用户', description: '仅当前归属的小组用户参加', value: 'GROUP_USER'},
];

const SettingInfo = () => {
  const contest = useContext(ContestContext);
  const t = useLocale(locale);
  const [form] = Form.useForm();
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [problems, setProblems] = useState([]);
  function onSubmit() {
    form.validate().then((values) => {
      const data = {
        name: values.name,
        startTime: new Date(values.time[0]).toISOString(),
        endTime: new Date(values.time[1]).toISOString(),
        type: values.type,
        privacy: values.privacy,
        membership: values.membership,
        invitationCode: values.invitationCode,
        description: values.description,
      };
      setConfirmLoading(true);
      updateContest(contest.id, data)
        .then(res => {
          Message.success('已保存');
        })
        .finally(() => {
          setConfirmLoading(false);
        });
    });
  }
  function listProblems() {
    listContestProblems(contest.id)
      .then(res => {
        setProblems(res.data.data);
      });
  }
  function deleteProblem(number) {
    deleteContestProblem(contest.id, number)
      .then(res => {
        Message.success('已删除');
        listProblems();
      });
  }
  useEffect(() => {
    let invitationCode = contest.invitationCode;
    // 简单生成一个邀请码去初始化
    if (invitationCode === '') {
      invitationCode = Date.now().toString(36);
    }
    form.setFieldsValue({
      name: contest.name,
      time: [contest.startTime, contest.endTime],
      type: contest.type,
      privacy: contest.privacy,
      membership: contest.membership,
      invitationCode: invitationCode,
      description: contest.description,
    });
    listProblems();
  }, []);
  return (
    <div>
      <Card title={t['setting.info.basicInfo']}>
        <Form form={form} autoComplete='off' onSubmit={onSubmit}>
          <Grid.Row>
            <Grid.Col span={12}>
              <Form.Item label={t['setting.info.contestName']} required field='name' rules={[{ required: true }]}>
                <Input placeholder='' />
              </Form.Item>
              <Form.Item label={t['setting.info.contestTime']} required field='time' rules={[{ required: true }]}>
                <RangePicker
                  showTime={{
                    format: 'HH:mm:ss',
                  }}
                  format='YYYY-MM-DD HH:mm:ss'
                />
              </Form.Item>
              <Form.Item label={t['setting.info.contestPrivacy']} required field='privacy' rules={[{ required: true }]}>
                <Radio.Group className={styles['card-radio-group']}>
                  {ContestPrivacy.map((item, index) => {
                    return (
                      <Radio key={index} value={item.value}>
                        {({ checked }) => {
                          return (
                            <Space
                              align='start'
                              className={styles[`custom-radio-card`] + (checked ?  ' ' + styles['custom-radio-card-checked']: '')}
                            >
                              <div className={styles['custom-radio-card-mask']}>
                                <div className={styles['custom-radio-card-mask-dot']}></div>
                              </div>
                              <div>
                                <div className={styles['custom-radio-card-title']}>{item.name}</div>
                                <Typography.Text type='secondary'>{item.description}</Typography.Text>
                              </div>
                            </Space>
                          );
                        }}
                      </Radio>
                    );
                  })}
                </Radio.Group>
              </Form.Item>
              <Form.Item label={t['setting.info.contestMembership']} required field='membership' rules={[{ required: true }]}>
                <Radio.Group className={styles['card-radio-group']}>
                  {ContestMembership.map((item, index) => {
                    if (item.value === 'GROUP_USER' && contest.owner.type === 'USER') {
                      return;
                    }
                    return (
                      <Radio key={index} value={item.value}>
                        {item.name}
                      </Radio>
                    );
                  })}
                </Radio.Group>
              </Form.Item>
              <Form.Item shouldUpdate noStyle>
                {(values) => {
                  return values.membership === 'INVITATION_CODE' && (
                    <Form.Item field='invitationCode' label={t['setting.info.contestMembership.invitationCode']} rules={[{ required: true }]}>
                      <Input />
                    </Form.Item>
                  );
                }}
              </Form.Item>
              <Form.Item label={t['setting.info.contestType']} required field='type' rules={[{ required: true }]}>
                <Radio.Group className={styles['card-radio-group']}>
                  {ContestType.map((item, index) => {
                    return (
                      <Radio key={index} value={item.value}>
                        {({ checked }) => {
                          return (
                            <Space
                              align='start'
                              className={styles[`custom-radio-card`] + (checked ?  ' ' + styles['custom-radio-card-checked']: '')}
                            >
                              <div className={styles['custom-radio-card-mask']}>
                                <div className={styles['custom-radio-card-mask-dot']}></div>
                              </div>
                              <div>
                                <div className={styles['custom-radio-card-title']}>{item.name}</div>
                                <Typography.Text type='secondary'>{item.description}</Typography.Text>
                              </div>
                            </Space>
                          );
                        }}
                      </Radio>
                    );
                  })}
                </Radio.Group>
              </Form.Item>
            </Grid.Col>
            <Grid.Col span={12}>
              <Form.Item label={t['setting.info.description']} field='description'>
                <Editor />
              </Form.Item>
            </Grid.Col>
          </Grid.Row>
          <Form.Item wrapperCol={{ offset: 5 }}>
            <Button type='primary' htmlType='submit'>{t['save']}</Button>
          </Form.Item>
        </Form>
      </Card>
      <Card title={t['problemList']}>
        <List
          style={{ width: 600 }}
          dataSource={problems}
          header={(
            <AddProblem contestId={contest.id} callback={listProblems} />
          )}
          render={(item, index) => (
            <List.Item key={index} actions={
              [
                <Popconfirm
                  focusLock
                  key={index}
                  title='Are you sure you want to delete?'
                  onOk={() => deleteProblem(item.number)}
                >
                  <Button
                    icon={<IconDelete />}
                    shape='circle'
                    type='secondary'
                  />
                </Popconfirm>
              ]
            }>
              <List.Item.Meta
                avatar={<Avatar shape='square'>{String.fromCharCode(65 + item.number)}</Avatar>}
                title={item.name}
                description={item.description}
              />
            </List.Item>
          )}
        />
      </Card>
    </div>
  );
};
export default SettingInfo;
