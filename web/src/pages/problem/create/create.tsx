import { useState } from 'react';
import { Modal, Button, Form, Input, Select, Message, Radio } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createProblem } from '@/api/problem';
import { useNavigate } from 'react-router-dom';
const FormItem = Form.Item;

function App() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const navigate = useNavigate();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      createProblem(values)
        .then(res => {
          navigate(`/problem/update/${res.data.id}`)
        })
        .finally(() => {
          setConfirmLoading(false);
        })
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
        </Form>
      </Modal>
    </div>
  );
}

export default App;
