import { getProblemVerification, updateProblem, verifyProblem } from '@/api/problem';
import { Form, Input, Button, Card, Message, List, Radio } from '@arco-design/web-react';
import { useEffect, useState } from 'react';
const FormItem = Form.Item;
const VerificationStatus = ['', '待验证', '验证失败', '验证成功'];
const App = (props) => {
  const [form] = Form.useForm();
  const [verification, setVerification] = useState({verificationStatus: 0, verificaitonInfo: []});
  function onSubmit(values) {
    updateProblem(props.problem.id, values)
      .then(res => {
        Message.info('已保存')
      })
      .catch(err => {
        Message.error(err.response.data.message)
      })
  }
  function fetchData() {
    getProblemVerification(props.problem.id)
      .then(res => {
        setVerification(res.data);
      })
  }
  function verify() {
    verifyProblem(props.problem.id).then(res => {
      Message.info('已提交校验，请稍等刷新')
    })
  }
  useEffect(() => {
    fetchData()
    form.setFieldsValue({
      status: props.problem.status,
      timeLimit: props.problem.timeLimit,
      memoryLimit: props.problem.memoryLimit,
    })
  }, [])
  return (
    <>
      <Card title='基本信息'>
        <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
          <FormItem field='timeLimit' label='时间限制' help="限制范围：250ms ~ 15000ms">
            <Input addAfter='ms' />
          </FormItem>
          <FormItem field='memoryLimit' label='内存限制' help="限制范围：4MB ~ 1024MB">
            <Input addAfter='MB' />
          </FormItem>
          <FormItem field='status' label='可见状态'>
            <Radio.Group>
              <Radio value={1}>私有</Radio>
              <Radio value={2}>公开</Radio>
            </Radio.Group>
          </FormItem>
          <FormItem wrapperCol={{ offset: 5 }}>
            <Button type='primary' htmlType='submit'>保存</Button>
          </FormItem>
        </Form>
      </Card>
      <Card title='题目校验'>
        <Button onClick={() => verify()}>校验</Button>
        <div>
          <h1></h1>
          <List
            style={{ width: 622 }}
            size='small'
            header={
              <div>
                校验状态: {VerificationStatus[verification.verificationStatus]}
              </div>
            }
            dataSource={verification.verificaitonInfo.map(item => {
              return {
                title: item.action,
                description: item.errorMessage,
              }
            })}
            render={(item, index) => 
              <List.Item key={index}>
                <List.Item.Meta
                  title={item.title}
                  description={item.description}
                />
              </List.Item>
            }
          />
        </div>
      </Card>
    </>
  );
};

export default App;