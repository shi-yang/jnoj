import React, { useState } from 'react';
import { Modal, Button, Form, Input, Radio, Space, Typography } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createProblem } from '@/api/problem';
import { useRouter } from 'next/router';
const FormItem = Form.Item;
import styles from './style/index.module.less';

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
          router.push(`/problems/${res.data.id}/update`);
        })
        .finally(() => {
          setConfirmLoading(false);
        });
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
          <FormItem label='题目类型' required field='type' defaultValue={0} help='题目类型创建后不可修改' rules={[{ required: true }]}>
            <Radio.Group className={styles['card-radio-group']}>
              {[
                { name: '标准输入输出题', value: 0, help: '用户需要完成标准输入输出'},
                { name: '函数题', value: 1, help: '用户只需要补全函数'}
              ].map((item) => {
                return (
                  <Radio key={item.value} value={item.value}>
                    {({ checked }) => {
                      return (
                        <Space
                          align='start'
                          className={`${styles['custom-radio-card']} ${checked ? styles['custom-radio-card-checked'] : ''}`}
                        >
                          <div className={styles['custom-radio-card-mask']}>
                            <div className={styles['custom-radio-card-mask-dot']}></div>
                          </div>
                          <div>
                            <div className={styles['custom-radio-card-title']}>{item.name}</div>
                            <Typography.Text type='secondary'>{item.help}</Typography.Text>
                          </div>
                        </Space>
                      );
                    }}
                  </Radio>
                );
              })}
            </Radio.Group>
          </FormItem>
        </Form>
      </Modal>
    </div>
  );
}

export default App;
