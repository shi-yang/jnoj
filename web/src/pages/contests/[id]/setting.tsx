import { createContestProblem, deleteContestProblem, listContestProblems, updateContest } from "@/api/contest";
import useLocale from "@/utils/useLocale";
import { Button, Card, Form, Input, DatePicker, List, Avatar, Modal, Message, Radio, Space, Typography, Popconfirm } from "@arco-design/web-react";
import { IconDelete, IconPlus } from "@arco-design/web-react/icon";
import { useEffect, useState } from "react";
import locale from "./locale";
const { RangePicker } = DatePicker;
import styles from './style/setting.module.less';

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
        添加题目
      </Button>
      <Modal
        title='添加题目'
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

const Setting = ({contest}) => {
  console.log('setting');
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
    });
    listProblems();
  }, [])
  return (
    <div>
      <Card title='基本信息'>
        <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
          <Form.Item label='比赛名称' required field='name' rules={[{ required: true }]}>
            <Input placeholder='' />
          </Form.Item>
          <Form.Item label='比赛时间' required field='time' rules={[{ required: true }]}>
            <RangePicker
              showTime={{
                format: 'HH:mm:ss',
              }}
              format='YYYY-MM-DD HH:mm:ss'
            />
          </Form.Item>
          <Form.Item label='比赛赛制' required field='type' rules={[{ required: true }]}>
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
            <Button type='primary' htmlType='submit'>保存</Button>
          </Form.Item>
        </Form>
      </Card>
      <Card title='题目列表'>
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
export default Setting;
