import { useState } from 'react';
import { Modal, Form, Message } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';

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

  return (
    <Modal
      title='创建题目描述'
      visible={visible}
      onOk={onOk}
      confirmLoading={confirmLoading}
      onCancel={() => setVisible(false)}
    >
    </Modal>
  );
}

export default App;
