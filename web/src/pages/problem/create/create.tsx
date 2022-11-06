import { useState } from 'react';
import { Modal, Button, Form, Input, Select, Message, Radio } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
const FormItem = Form.Item;

function App() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();

  function onOk() {
    form.validate().then((res) => {
      setConfirmLoading(true);
      setTimeout(() => {
        Message.success('Success !');
        setVisible(false);
        setConfirmLoading(false);
      }, 1500);
    });
  }

  const formItemLayout = {
    labelCol: {
      span: 4,
    },
    wrapperCol: {
      span: 20,
    },
  };
  return (
    <div>
      <Button type="primary" icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['searchTable.operations.add']}
      </Button>
      <Modal
        title='创建题目'
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form
          {...formItemLayout}
          form={form}
          labelCol={{
            style: { flexBasis: 90 },
          }}
          wrapperCol={{
            style: { flexBasis: 'calc(100% - 90px)' },
          }}
        >
          <FormItem label='题目名称' required field='name' rules={[{ required: true }]}>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='题目类型' field='type'>
            <Radio.Group defaultValue='1'>
              <Radio value='1'>标准输入输出</Radio>
              <Radio value='2' disabled>函数题目</Radio>
            </Radio.Group>
          </FormItem>
        </Form>
      </Modal>
    </div>
  );
}

export default App;
