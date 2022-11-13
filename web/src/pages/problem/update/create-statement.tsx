import { useState } from 'react';
import { Modal, Button, Form, Input, Select, Message, Radio } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createProblemStatement } from '@/api/problem-statement';
const FormItem = Form.Item;
const Option = Select.Option;

const options = ['中文', 'English'];
function App(props) {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();

  function onOk() {
    form.validate().then((res) => {
      setConfirmLoading(true);
      createProblemStatement(props.problem.id, res)
        .then(res => {
          Message.success('Success !');
        })
        .finally(() => {
          setVisible(false);
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
        添加
      </Button>
      <Modal
        title='创建题目描述'
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
          <FormItem field='language' label={t['language']}>
            <Select
              placeholder='Please select'
              style={{ width: 154 }}
              onChange={(value) =>
                console.log(value)
              }
            >
              {options.map((option, index) => (
                <Option key={option} value={option}>
                  {option}
                </Option>
              ))}
            </Select>
          </FormItem>
        </Form>
      </Modal>
    </div>
  );
}

export default App;
