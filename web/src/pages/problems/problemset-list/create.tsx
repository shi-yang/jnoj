import React, { useState } from 'react';
import { Modal, Button, Form, Input, Radio, Space, Typography, Message } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createProblem } from '@/api/problem';
import { useRouter } from 'next/router';
const FormItem = Form.Item;
import styles from './style/index.module.less';

function CreateModal() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    Message.error('暂不可用');
    // form.validate().then((values) => {
    //   setConfirmLoading(true);
    //   createProblem(values)
    //     .then(res => {
    //       router.push(`/problems/${res.data.id}/update`);
    //     })
    //     .finally(() => {
    //       setConfirmLoading(false);
    //     });
    // });
  }

  return (
    <div>
      <Button type="primary" icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['searchTable.operations.add']}
      </Button>
      <Modal
        title='创建题单'
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form
          form={form}
        >
          <FormItem label='名称' required field='name' rules={[{ required: true }]}>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='类型' required field='type' defaultValue={0} help='类型创建后不可修改' rules={[{ required: true }]}>
            <Radio.Group className={styles['card-radio-group']}>
              {[
                { name: '普通模式', value: 0, help: '只是将各种题目单纯整合到一个题单里面，用户可随时、不限制时间地刷题'},
                { name: '试卷模式', value: 1, help: '在普通模式的基础上有额外的功能：每道题目都可有分数限制，类似于考试，可限制考试时间，用户进入题单即开始计时，用户需要交卷才能得知答案'},
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

export default CreateModal;