import React, { useEffect } from 'react';
import { Form, Input, Button, Card, Radio, InputTag, Grid, Space, Select, Divider } from '@arco-design/web-react';
import { updateProblem } from '@/api/problem';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { IconArrowFall, IconArrowRise, IconDelete } from '@arco-design/web-react/icon';
const FormItem = Form.Item;

export default function ObjectivePage(props: any) {
  const t = useLocale(locale);
  const [form] = Form.useForm();
  function onSubmit(values) {
    console.log(form.getFieldValue('optionals'));
  }
  useEffect(() => {
    form.setFieldsValue({
      status: props.problem.status,
      source: props.problem.source,
      tags: props.problem.tags
    });
  }, []);

  return (
    <Card>
      <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
        <FormItem field='type' label='类型'>
          <Radio.Group>
            <Radio value={0}>单选题</Radio>
            <Radio value={1}>多选题</Radio>
            <Radio value={2}>填空题</Radio>
          </Radio.Group>
        </FormItem>
        <Form.Item shouldUpdate noStyle>
          {(values) => {
            return (values.type === 0 || values.type === 1) ? (
              <>
                <Form.Item field='optional' label='选项'>
                  <Form.List
                    rules={[
                      {
                        validator(v, cb) {
                          if (v?.length < 2) {
                            return cb('必须超过两条');
                          }
                          return cb();
                        },
                      },
                    ]}
                    field='optionals'
                  >
                    {(fields, { add, remove, move }) => {
                      return (
                        <div>
                          {fields.map((item, index) => {
                            return (
                              <Grid.Row key={item.key}>
                                <Form.Item
                                  field={item.field}
                                  label={String.fromCharCode(65 + index)}
                                  style={{
                                    width: 370,
                                  }}
                                  rules={[
                                    {
                                      required: true,
                                    },
                                  ]}
                                >
                                  <Input />
                                </Form.Item>
                                <Button
                                  icon={<IconDelete />}
                                  shape='circle'
                                  status='danger'
                                  style={{
                                    margin: '0 20px',
                                  }}
                                  onClick={() => remove(index)}
                                ></Button>
                                <Button
                                  shape='circle'
                                  onClick={() => move(index, index > 0 ? index - 1 : index + 1)}
                                >
                                  {index > 0 ? <IconArrowRise /> : <IconArrowFall />}
                                </Button>
                              </Grid.Row>
                            );
                          })}
                          <Space size={20}>
                            <Button
                              onClick={() => {
                                add();
                              }}
                            >
                              添加选项
                            </Button>
                          </Space>
                        </div>
                      );
                    }}
                  </Form.List>
                </Form.Item>
                <Form.Item shouldUpdate field='answer' label='答案'>
                  {(values) => {
                    return (
                      <Select placeholder='Please select' mode={values.type === 1 ? 'multiple' : null} allowCreate={false} style={{ width: 154 }} allowClear>
                        {values.optionals && values.optionals.map((item, index) => (
                          <Select.Option key={index} value={index}>
                            {item}
                          </Select.Option>
                        ))}
                      </Select>
                    );
                  }}
                </Form.Item>
              </>
            ) : (
              values.type === 2 && (
                <>
                  <Form.Item field='content' label='题目描述'>
                    <Input.TextArea
                      placeholder='Please enter ...'
                    />
                  </Form.Item>
                  <Form.Item field='answer' label='答案'>
                    <Input />
                  </Form.Item>
                </>
              )
            );
          }}
        </Form.Item>
        <Divider />
        <FormItem field='status' label={t['visibleState']} help='公开是指其他人可将此题目加入到他创建的题单或者比赛中。其他人仅有将此题目加入题单、比赛的权限，没有编辑、下载测试数据的权限。'>
          <Radio.Group>
            <Radio value={1}>{t['private']}</Radio>
            <Radio value={2}>{t['public']}</Radio>
          </Radio.Group>
        </FormItem>
        <FormItem field='tags' label={t['tags']}>
          <InputTag saveOnBlur />
        </FormItem>
        <FormItem field='source' label={t['source']}>
          <Input.TextArea rows={2} />
        </FormItem>
        <FormItem wrapperCol={{ offset: 5 }}>
          <Button type='primary' htmlType='submit'>{t['save']}</Button>
        </FormItem>
      </Form>
    </Card>
  );
};
