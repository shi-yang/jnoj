import React, { useEffect, useState } from 'react';
import { Form, Input, Button, Card, Radio, InputTag, Grid, Space, Select, Divider, List, Popconfirm, Message, Tag } from '@arco-design/web-react';
import { getProblemVerification, updateProblem, verifyProblem } from '@/api/problem';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { IconArrowFall, IconArrowRise, IconDelete, IconEdit } from '@arco-design/web-react/icon';
import { deleteProblemStatement, listProblemStatements, updateProblemStatement } from '@/api/problem-statement';
import { deleteProblemFile, listProblemFiles, uploadProblemFile } from '@/api/problem-file';
import CreateStatementModal from './create-statement';
import Editor from '@/components/MarkdownEditor';
const VerificationStatus = ['', '待验证', '验证失败', '验证成功'];
const FormItem = Form.Item;

export default function ObjectivePage(props: any) {
  const t = useLocale(locale);
  const [form] = Form.useForm();
  const [statementForm] = Form.useForm();
  const [verification, setVerification] = useState({verificationStatus: 0, verificaitonInfo: []});
  const [statements, setStatements] = useState([]);
  const [current, setCurrent] = useState(0);
  const [attachmentFiles, setAttachmentFiles] = useState([]);
  function fetchStatement() {
    listProblemStatements(props.problem.id).then(res => {
      const d = res.data.data;
      setStatements(res.data.data);
      if (d.length > 0) {
        const statement = d[0];
        statementForm.setFieldsValue({
          name: statement.name,
          legend: statement.legend,
          type: statement.type,
          note: statement.note,
        });
        if (statement.type === 'CHOICE') {
          statementForm.setFieldsValue({
            optionals: statement.input.split(','),
            answer: statement.input.indexOf(statement.output)
          });
        } else if (statement.type === 'MULTIPLE') {
          const choices = statement.input.split(',');
          statementForm.setFieldsValue({
            optionals: choices,
            answer: statement.output.split(',').map(item => choices.indexOf(item))
          });
        } else {
          statementForm.setFieldsValue({
            answer: statement.output
          });
        }
      }
    });
    listProblemFiles(props.problem.id, {fileType: 'statement'})
      .then(res => {
        const { data } = res.data;
        const arr = [];
        data.forEach(item => {
          arr.push({
            uid: item.id,
            name: item.name,
            url: item.content,
          });
        });
        setAttachmentFiles(arr);
      });
  }
  function onSubmit() {
    form.validate().then((values) => {
      const data = {
        timeLimit: 1000,
        memoryLimit: 1024,
        status: values.status,
        source: values.source,
        tags: values.tags,
      };
      updateProblem(props.problem.id, data).then(res => {
        Message.info('已保存');
      });
    });
  }
  function onSubmitStatement() {
    statementForm.validate().then((values) => {
      const data = {
        name: values.name,
        legend: values.legend,
        type: values.type,
        input: '',
        output: '',
        note: values.note,
      };
      if (values.type === 'CHOICE') {
        data.input = values.optionals.join(',');
        data.output = values.optionals[values.answer];
      } else if (values.type === 'MULTIPLE') {
        data.input = values.optionals.join(',');
        data.output = values.answer.map(item => values.optionals[item]).join(',');
      } else {
        data.output = values.answer;
      }
      updateProblemStatement(props.problem.id, statements[current].id, data)
        .then(res => {
          Message.info('已保存');
        });
    });
  }
  function uploadFile(option) {
    const { onError, onSuccess, file } = option;
    const formData = new FormData();
    formData.append('file', file);
    formData.append('fileType', 'statement');
    uploadProblemFile(props.problem.id, formData)
      .then(res => {
        onSuccess();
        Message.success('上传成功');
        fetchStatement();
      })
      .catch(err => {
        onError();
      });
  }
  function removeFile(option) {
    deleteProblemFile(props.problem.id, option.uid)
      .then(res => {
        Message.success('已删除');
        fetchStatement();
      });
  }
  function verify() {
    verifyProblem(props.problem.id).then(res => {
      Message.info('已提交校验，请稍等刷新');
    });
  }
  function editStatement(index) {
    setCurrent(index);
    const statement = statements[index];
    statementForm.setFieldsValue({
      name: statement.name,
      legend: statement.legend,
      type: statement.type,
    });
    if (statement.type === 'CHOICE') {
      statementForm.setFieldsValue({
        optionals: statement.input.split(','),
        answer: statement.input.indexOf(statement.output)
      });
    } else if (statement.type === 'MULTIPLE') {
      const choices = statement.input.split(',');
      statementForm.setFieldsValue({
        optionals: choices,
        answer: statement.output.split(',').map(item => choices.indexOf(item))
      });
    } else {
      statementForm.setFieldsValue({
        answer: statement.output
      });
    }
  }
  function deleteStatement(index) {
    deleteProblemStatement(props.problem.id, statements[index].id)
      .then(res => {
        fetchStatement();
        Message.success('已删除');
      });
  }
  
  useEffect(() => {
    fetchStatement();
    getProblemVerification(props.problem.id)
      .then(res => {
        setVerification(res.data);
      });
    form.setFieldsValue({
      status: props.problem.status,
      source: props.problem.source,
      tags: props.problem.tags
    });
  }, []);

  return (
    <Card>
      <Grid.Row gutter={64} style={{marginTop: '10px'}}>
        <Grid.Col flex='800px'>
          <List
            bordered
            footer={<CreateStatementModal problem={props.problem} callback={fetchStatement} />}
          >
            {statements.map((item, index) => (
              <List.Item key={index} actions={[
                <div key={index}>
                  <Button onClick={() => editStatement(index)}>
                    <IconEdit />
                  </Button>
                  <Divider type='vertical' />
                  <Popconfirm
                    title='Are you sure you want to delete?'
                    onOk={() => deleteStatement(index)}
                  >
                    <Button><IconDelete /></Button>
                  </Popconfirm>
                </div>
              ]}>
                <List.Item.Meta
                  title={item.name}
                  description={(<Tag>{item.language}</Tag>)}
                />
              </List.Item>
            ))}
          </List>
          { statements.length > 0 && (
            <>
              <Divider orientation='left'>题目描述</Divider>
              <Form form={statementForm} autoComplete='off' onSubmit={onSubmitStatement}>
                <FormItem field='name' label={t['name']} required>
                  <Input />
                </FormItem>
                <FormItem field='legend' label={t['legend']}>
                  <Editor
                    imageRequest={{
                      onUpload: uploadFile,
                      onRemove: removeFile,
                    }}
                    imageUploadedFile={attachmentFiles}
                  />
                </FormItem>
                <Divider orientation='left'>类型及答案</Divider>
                <FormItem field='type' label='类型' required>
                  <Radio.Group>
                    <Radio value='CHOICE'>单选题</Radio>
                    <Radio value='MULTIPLE'>多选题</Radio>
                    <Radio value='FILLBLANK'>填空题</Radio>
                  </Radio.Group>
                </FormItem>
                <Form.Item shouldUpdate noStyle>
                  {(values) => {
                    return (values.type === 'CHOICE' || values.type === 'MULTIPLE') ? (
                      <>
                        <Form.Item field='optional' label='选项' required>
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
                        <Form.Item shouldUpdate field='answer' label='答案' required>
                          {(values) => {
                            return (
                              <Select placeholder='Please select' mode={values.type === 'MULTIPLE' ? 'multiple' : null} allowCreate={false} style={{ width: 154 }} allowClear>
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
                      values.type === 'FILLBLANK' && (
                        <>
                          <Form.Item field='answer' label='答案' required>
                            <Input />
                          </Form.Item>
                        </>
                      )
                    );
                  }}
                </Form.Item>
                <FormItem field='note' label='答案解析'>
                  <Editor
                    imageRequest={{
                      onUpload: uploadFile,
                      onRemove: removeFile,
                    }}
                    imageUploadedFile={attachmentFiles}
                  />
                </FormItem>
                <FormItem>
                  <Button type='primary' htmlType='submit' style={{width: '100%'}}>{t['save']}</Button>
                </FormItem>
              </Form>
            </>
          )}
        </Grid.Col>
        <Grid.Col flex='auto'>
          <Form form={form} style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
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
          <Card title='题目校验'>
            <Button onClick={() => verify()}>校验</Button>
            <div>
              <h1></h1>
              <List
                style={{ width: 622 }}
                size='small'
                header={
                  <div>
                    校验状态: {VerificationStatus[verification.verificationStatus]}
                  </div>
                }
                dataSource={verification.verificaitonInfo.map(item => {
                  return {
                    title: item.action,
                    description: item.errorMessage,
                  };
                })}
                render={(item, index) => 
                  <List.Item key={index}>
                    <List.Item.Meta
                      title={item.title}
                      description={item.description}
                    />
                  </List.Item>
                }
              />
            </div>
          </Card>
        </Grid.Col>
      </Grid.Row>
    </Card>
  );
};