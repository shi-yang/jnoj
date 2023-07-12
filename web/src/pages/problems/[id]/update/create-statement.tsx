import React, { useEffect, useState } from 'react';
import { Modal, Button, Form, Select, Message } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createProblemStatement, listProblemStatements } from '@/api/problem-statement';
const FormItem = Form.Item;
const Option = Select.Option;

const options = ['中文', 'English'];
function App(props: any) {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const [disabledLanguages, setDisabledLanguages] = useState({});

  function onOk() {
    form.validate().then((res) => {
      setConfirmLoading(true);
      createProblemStatement(props.problem.id, res)
        .then(res => {
          Message.success('Success !');
          props.callback();
        })
        .finally(() => {
          setVisible(false);
          setConfirmLoading(false);
        });
    });
  }

  function fetchData() {
    listProblemStatements(props.problem.id)
      .then(res => {
        const { data } = res.data;
        data.forEach(item => {
          setDisabledLanguages((pre) => ({...pre, [item.language]: true}));
        });
      });
  }
  useEffect(() => {
    fetchData();
    return () => {
      form.clearFields();
      setDisabledLanguages({});
    };
  }, [visible]);
  return (
    <div>
      <Button type="primary" icon={<IconPlus />} onClick={() => setVisible(true)}>
        添加题目描述
      </Button>
      <Modal
        title='添加题目描述'
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form form={form}>
          <FormItem field='language' label={t['language']} required
            rules={[
              {
                required: true,
                type: 'string',
              },
            ]}
          >
            <Select
              placeholder='Please select'
              style={{ width: 154 }}
            >
              {options.map((option, index) => (
                <Option key={option} value={option} disabled={disabledLanguages[option]}>
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
