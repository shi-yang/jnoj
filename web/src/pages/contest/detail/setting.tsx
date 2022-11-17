import { createContestProblem, listContestProblems, updateContest } from "@/api/contest";
import useLocale from "@/utils/useLocale";
import { Button, Card, Form, Input, DatePicker, List, Avatar, Modal, Message } from "@arco-design/web-react";
import { IconDelete, IconPlus } from "@arco-design/web-react/icon";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import locale from "./locale";
const { RangePicker } = DatePicker;

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

const Setting = ({contest}) => {
  const params = useParams();
  const [form] = Form.useForm();
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [problems, setProblems] = useState([]);
  function onSubmit(values) {
    form.validate().then((values) => {
      const data = {
        name: values.name,
        startTime: new Date(values.time[0]).toISOString(),
        endTime: new Date(values.time[1]).toISOString()
      };
      setConfirmLoading(true)
      updateContest(params.id, data)
        .then(res => {
        })
        .finally(() => {
          setConfirmLoading(false);
        })
    });
  }
  function listProblems() {
    listContestProblems(params.id)
      .then(res => {
        setProblems(res.data.data)
      })
  }
  useEffect(() => {
    form.setFieldsValue({
      name: contest.name,
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
            <AddProblem contestId={params.id} callback={listProblems} />
          )}
          render={(item, index) => (
            <List.Item key={index} actions={
              [
                <Button icon={<IconDelete />} shape="circle" type="secondary" />,
              ]
            }>
              <List.Item.Meta
                avatar={<Avatar shape='square'>{String.fromCharCode(65 + item.number)}</Avatar>}
                title={item.title}
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
