import { getProblemVerification, updateProblem, verifyProblem } from '@/api/problem';
import useLocale from '@/utils/useLocale';
import { Form, Input, Button, Card, Message, List, Radio, InputTag } from '@arco-design/web-react';
import { useEffect, useState } from 'react';
import locale from './locale';
const FormItem = Form.Item;
const VerificationStatus = ['', '待验证', '验证失败', '验证成功'];
const App = (props) => {
  const t = useLocale(locale);
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
      source: props.problem.source,
      tags: props.problem.tags
    })
  }, [])
  return (
    <>
      <Card>
        <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
          <FormItem field='timeLimit' label={t['timeLimit']} help="250ms ~ 15000ms">
            <Input addAfter='ms' />
          </FormItem>
          <FormItem field='memoryLimit' label={t['memoryLimit']} help="4MB ~ 1024MB">
            <Input addAfter='MB' />
          </FormItem>
          <FormItem field='status' label={t['visibleState']} help='公开是指其他人可将此题目加入到他创建的题单或者比赛中。其他人仅有将此题目加入题单、比赛的权限，没有编辑、下载测试数据的权限。'>
            <Radio.Group>
              <Radio value={1}>{t['private']}</Radio>
              <Radio value={2}>{t['public']}</Radio>
            </Radio.Group>
          </FormItem>
          <FormItem field='tags' label={t['tags']}>
            <InputTag saveOnBlur />
          </FormItem>
          <FormItem field='source' label={t['source']}>
            <Input.TextArea rows={2} />
          </FormItem>
          <FormItem wrapperCol={{ offset: 5 }}>
            <Button type='primary' htmlType='submit'>{t['save']}</Button>
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
