import { createContestProblem, deleteContestProblem, listContestProblems, updateContest } from "@/api/contest";
import useLocale from "@/utils/useLocale";
import { Button, Card, Form, Input, DatePicker, List, Avatar, Modal, Message, Radio, Space, Typography, Popconfirm } from "@arco-design/web-react";
import { IconDelete, IconPlus } from "@arco-design/web-react/icon";
import { useEffect, useState } from "react";
import locale from "../locale";
const { RangePicker } = DatePicker;
import styles from '../style/setting.module.less';

const AddProblem = ({contestId, callback}) => {
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
          setVisible(false)
          callback()
        })
        .catch(res => {
          Message.error(res.response.data.message || '出错了')
        })
        .finally(() => {
          setConfirmLoading(false);
        })
    });
  }
  return (
    <div>
      <Button type="primary" icon={<IconPlus />} onClick={() => setVisible(true)}>
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
}

const ContestType = [
  { name: 'ICPC', description: 'International Collegiate Programming Contest', value: 1 },
  { name: 'IOI', description: 'International Olympiad in Informatics', value: 2 },
  { name: 'OI', description: 'Olympiad in Informatics', value: 3 },
]

const ContestStatus = [
  { name: '隐藏', description: '仅邀请用户可参加', value: 'HIDDEN', id: 0 },
  { name: '公开', description: '任何人均可参加', value: 'PUBLIC', id: 1 },
  { name: '私有', description: '仅邀请用户可参加', value: 'PRIVATE', id: 2 },
]

const SettingInfo = ({contest}) => {
  const t = useLocale(locale);
  const [form] = Form.useForm();
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [problems, setProblems] = useState([]);
  function onSubmit() {
    form.validate().then((values) => {
      const status = ContestStatus.find(item => item.value === values.status);
      const data = {
        name: values.name,
        startTime: new Date(values.time[0]).toISOString(),
        endTime: new Date(values.time[1]).toISOString(),
        type: values.type,
        status: status.id,
      };
      setConfirmLoading(true)
      updateContest(contest.id, data)
        .then(res => {
          Message.success('已保存')
        })
        .finally(() => {
          setConfirmLoading(false);
        })
    });
  }
  function listProblems() {
    listContestProblems(contest.id)
      .then(res => {
        setProblems(res.data.data)
      })
  }
  function deleteProblem(number) {
    deleteContestProblem(contest.id, number)
      .then(res => {
        Message.success('已删除');
        listProblems()
      })
  }
  useEffect(() => {
    form.setFieldsValue({
      name: contest.name,
      time: [contest.startTime, contest.endTime],
      type: contest.type,
      status: contest.status,
    });
    listProblems();
  }, [])
  return (
    <div>
      <Card title={t['setting.info.basicInfo']}>
        <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
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
          <Form.Item label={t['setting.info.contestStatus']} required field='status' rules={[{ required: true }]}>
            <Radio.Group className={styles['card-radio-group']}>
              {ContestStatus.map((item, index) => {
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
                  title='Are you sure you want to delete?'
                  onOk={() => deleteProblem(item.number)}
                >
                  <Button
                    icon={<IconDelete />}
                    shape="circle"
                    type="secondary"
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
  )
}
export default SettingInfo;
