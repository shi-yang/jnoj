import { useState } from 'react';
import { Modal, Button, Form, Input, DatePicker } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { useNavigate } from 'react-router-dom';
import { createContest } from '@/api/contest';
const FormItem = Form.Item;
const { RangePicker } = DatePicker;

function App() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const navigate = useNavigate();

  function onOk() {
    form.validate().then((values) => {
      const data = {
        name: values.name,
        startTime: new Date(values.time[0]).toISOString(),
        endTime: new Date(values.time[1]).toISOString()
      };
      setConfirmLoading(true);
      createContest(data).then(res => {
        console.log(res)
      }).finally(() => {
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
        title='创建比赛'
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form form={form}>
          <FormItem label='比赛名称' required field='name' rules={[{ required: true }]}>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='比赛时间' required field='time' rules={[{ required: true }]}>
            <RangePicker
              showTime={{
                format: 'HH:mm:ss',
              }}
              format='YYYY-MM-DD HH:mm:ss'
            />
          </FormItem>
        </Form>
      </Modal>
    </div>
  );
}

export default App;
