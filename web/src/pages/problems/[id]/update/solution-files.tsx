import React, { useEffect, useState } from 'react';
import { Button, Card, Form, Input, Message, Modal, Popover, Select, Space, Table, TableColumnProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import { listProblemFiles, createProblemFile, deleteProblemFile, getProblemFile, updateProblemFile, runProblemFile, listProblemLanguages, getProblemLanguage } from '@/api/problem-file';
import locale from './locale';
import styles from './style/tests.module.less';
import { FormatTime } from '@/utils/format';
import SubmissionList from '@/modules/submission/SubmissionList';
const FormItem = Form.Item;
import CodeMirror from '@uiw/react-codemirror';
import { LanguageMap } from '@/api/submission';

const App = ({problem}: any) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [editVisible, setEditVisible] = useState(false);
  const [form] = Form.useForm();
  const [languageOptions, setLanguageOptions] = useState([]);
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
      align: 'center',
    },
    {
      title: t['name'],
      dataIndex: 'name',
      align: 'center',
    },
    {
      title: t['language'],
      dataIndex: 'language',
      align: 'center',
      render: col => LanguageMap[col]
    },
    {
      title: t['type'],
      dataIndex: 'type',
      align: 'center',
    },
    {
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: col => FormatTime(col)
    },
    {
      title: t['action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Button type='text' onClick={() => runFile(record.id)}>运行</Button>
          <Button type="text" size="small" onClick={() => edit(record)}>编辑</Button>
          <Modal
            title='编辑'
            visible={editVisible}
            onOk={onEditOk}
            onCancel={() => setEditVisible(false)}
            autoFocus={false}
            focusLock={true}
          >
            <Form
              form={form}
            >
              <FormItem field='id' label='ID' hidden>
                <Input />
              </FormItem>
              <FormItem field='name' label='名称' required>
                <Input />
              </FormItem>
              <FormItem field='content' label='源码' required>
                <CodeMirror
                  height="100%"
                />
              </FormItem>
              <FormItem field='type' label='类型' required>
                <Select defaultValue='model_solution'>
                  <Select.Option key='main' value='model_solution'>
                    标准解答
                  </Select.Option>
                </Select>
              </FormItem>
            </Form>
          </Modal>
          <Popover
            trigger='click'
            title='你确定要删除吗？'
            content={
              <span>
                <Button type='text' size='small' onClick={(e) => deleteFile(record.id)}>删除</Button>
              </span>
            }
          >
            <Button>删除</Button>
          </Popover>
        </>
      ),
    },
  ];
  function fetchData() {
    setLoading(true);
    listProblemLanguages(problem.id)
      .then(res => {
        const langs = res.data.data;
        setLanguageOptions(langs);
      });
    listProblemFiles(problem.id, { fileType: 'solution' })
      .then((res) => {
        setData(res.data.data || []);
      })
      .finally(() => {
        setLoading(false);
      });
  }
  function edit(record) {
    getProblemFile(problem.id, record.id)
      .then(res => {
        const data = res.data;
        form.setFieldsValue({
          id: data.id,
          name: data.name,
          content: data.content,
          type: data.type,
        });
        setEditVisible(true);
      });
  }
  function onLanguageChange(e) {
    if (problem.type === 'FUNCTION') {
      const lang = languageOptions.find(item => {
        return item.languageCode === Number(e);
      });
      getProblemLanguage(problem.id, lang.id)
        .then(res => {
          form.setFieldValue('content', res.data.userContent);
        });
    }
  }
  function deleteFile(id) {
    deleteProblemFile(problem.id, id)
      .then(res => {
        Message.success('删除成功');
        fetchData();
      });
  }
  function onOk() {
    form.validate().then((res) => {
      const values = {
        name: res.name,
        content: res.content,
        type: res.type,
        fileType: 'solution',
        language: res.language
      };
      createProblemFile(problem.id, values)
        .then(res => {
          Message.success('已保存');
          setVisible(false);
          fetchData();
        });
    });
  }
  function onEditOk() {
    form.validate().then((res) => {
      const values = {
        name: res.name,
        content: res.content,
        type: res.type,
      };
      updateProblemFile(problem.id, res.id, values)
        .then(res => {
          Message.success('已保存');
          setEditVisible(false);
          fetchData();
        });
    });
  }
  function runFile(id) {
    runProblemFile(id)
      .then(res => {
        Message.info('已提交');
      });
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <>
      <Card title='解答文件'>
        <div className={styles['button-group']}>
          <Space>
            <Button type='primary' onClick={() => {form.resetFields(); setVisible(true);}}>添加</Button>
            <Modal
              title='添加'
              style={{width: '800px'}}
              visible={visible}
              onOk={onOk}
              onCancel={() => setVisible(false)}
              autoFocus={false}
              focusLock={true}
            >
              <Form
                form={form}
              >
                <FormItem field='name' label='名称' required>
                  <Input />
                </FormItem>
                <FormItem field='language' label='语言' required>
                  <Select onChange={onLanguageChange}>
                    {languageOptions.map((item, index) => {
                      return (
                        <Select.Option key={index} value={`${item.languageCode}`}>
                          {item.languageName}
                        </Select.Option>
                      );
                    })}
                  </Select>
                </FormItem>
                <FormItem field='content' label='源码' required>
                  <CodeMirror
                    height="400px"
                  />
                </FormItem>
                <FormItem field='type' label='类型' required>
                  <Select defaultValue='model_solution'>
                    <Select.Option key='main' value='model_solution'>
                      标准解答
                    </Select.Option>
                  </Select>
                </FormItem>
              </Form>
            </Modal>
          </Space>
        </div>
        <Table rowKey={r => r.id} loading={loading} columns={columns} data={data} />
      </Card>
      <Card title={'运行信息'}>
        <SubmissionList pid={problem.id} entityType={2} />
      </Card>
    </>
  );
};

export default App;
