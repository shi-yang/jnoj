import { Form, Input, Button, Checkbox } from '@arco-design/web-react';
const FormItem = Form.Item;

const App = () => {
  return (
    <Form style={{ width: 600 }} autoComplete='off'>
      <FormItem label='时间限制'>
        <Input />
      </FormItem>
      <FormItem label='内存限制'>
        <Input />
      </FormItem>
      <FormItem wrapperCol={{ offset: 5 }}>
        <Button type='primary'>保存</Button>
      </FormItem>
    </Form>
  );
};

export default App;
