import { updateProblem } from '@/api/problem';
import { Form, Input, Button, Card, Message } from '@arco-design/web-react';
import { useEffect } from 'react';
const FormItem = Form.Item;

const App = (props) => {
  const [form] = Form.useForm();
  function onSubmit(values) {
    updateProblem(props.problem.id, values).then(res => {
      Message.info('已保存')
    })
  }
  useEffect(() => {
    form.setFieldsValue({
      timeLimit: props.problem.timeLimit,
      memoryLimit: props.problem.memoryLimit,
    })
  }, [])
  return (
    <Card>
      <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
        <FormItem field='timeLimit' label='时间限制' help="限制范围：250ms ~ 15000ms">
          <Input addAfter='ms' />
        </FormItem>
        <FormItem field='memoryLimit' label='内存限制' help="限制范围：4MB ~ 1024MB">
          <Input addAfter='MB' />
        </FormItem>
        <FormItem wrapperCol={{ offset: 5 }}>
          <Button type='primary' htmlType='submit'>保存</Button>
        </FormItem>
      </Form>
    </Card>
  );
};

export default App;
