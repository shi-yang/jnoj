import { useState } from 'react';
import { Modal, Button, Form, Input } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createProblem } from '@/api/problem';
import { useRouter } from 'next/router';
const FormItem = Form.Item;

function App() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      createProblem(values)
        .then(res => {
          router.push(`/problems/update/${res.data.id}`);
        })
        .finally(() => {
          setConfirmLoading(false);
        })
    });
  }

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
          form={form}
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
