import { Form, Input, Button, Checkbox, Card } from '@arco-design/web-react';
const FormItem = Form.Item;

const App = () => {
  return (
    <Card>
      <Form style={{ width: 600 }} autoComplete='off'>
        <FormItem label='时间限制' help="限制范围：250ms ~ 15000ms">
          <Input addAfter='ms' />
        </FormItem>
        <FormItem label='内存限制' help="限制范围：4MB ~ 1024MB">
          <Input addAfter='MB' />
        </FormItem>
        <FormItem wrapperCol={{ offset: 5 }}>
          <Button type='primary'>保存</Button>
        </FormItem>
      </Form>
    </Card>
  );
};

export default App;
