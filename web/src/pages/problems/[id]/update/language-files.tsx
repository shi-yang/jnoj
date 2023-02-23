import React, { useEffect, useState } from 'react';
import { Button, Card, Empty, Form, Grid, List, Message, Modal, Popconfirm, Select, Space } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import { createProblemLanguage, deleteProblemLanguage, listProblemLanguages, getProblemLanguage, updateProblemLanguage } from '@/api/problem-file';
import locale from './locale';
import styles from './style/tests.module.less';
import { LanguageMap } from '@/api/submission';
import CodeMirror from '@uiw/react-codemirror';
import { IconDelete, IconEdit } from '@arco-design/web-react/icon';
const FormItem = Form.Item;

// 定义示例模板
const languageDemoTemplate = {
  0: { // C
    userContent:
`int solution(int a, int b) {

}
`,
    mainContent:
`#include <bits/stdc++.h>

@@@

int main() {
    int a, b;
    scanf("%d%d", &a, &b);
    printf("%d", solution(a, b));
    return 0;
}
`
  },
  1: { // CPP
    userContent:
`int solution(int a, int b) {

}
    `,
    mainContent:
`#include <bits/stdc++.h>

@@@

int main() {
    int a, b;
    scanf("%d%d", &a, &b);
    printf("%d", solution(a, b));
    return 0;
}
`
  },
  2: { // Java
    userContent: '',
    mainContent: '',
  },
  3: { // Python
    userContent: '',
    mainContent: '',
  }
};

const App = (props: any) => {
  const t = useLocale(locale);
  const [languages, setLanguages] = useState([]);
  const [visible, setVisible] = useState(false);
  const [form] = Form.useForm();
  const [selectedLanguage, setSelectedLanguage] = useState(null);
  const [selectOptions, setSelectOptions] = useState([]);
  function fetchData() {
    listProblemLanguages(props.problem.id)
      .then((res) => {
        const langs = res.data.data;
        const options = [];
        Object.keys(LanguageMap).forEach(item => {
          const found = langs.find(v => {
            return v.languageCode === Number(item);
          });
          if (!found) {
            options.push(item);
          }
        });
        setLanguages(langs || []);
        setSelectOptions(options);
      });
  }
  function edit(id) {
    getProblemLanguage(props.problem.id, id)
      .then(res => {
        const data = res.data;
        form.setFieldsValue({
          language: data.languageCode,
          mainContent: data.mainContent,
          userContent: data.userContent,
        });
        setSelectedLanguage(id);
      });
  }
  function deleteLanguage(id) {
    deleteProblemLanguage(props.problem.id, id)
      .then(res => {
        Message.success('删除成功');
        fetchData();
      });
  }
  function onOk() {
    form.validate().then((res) => {
      createProblemLanguage(props.problem.id, res)
        .then(() => {
          Message.success('创建成功');
          form.resetFields();
          setVisible(false);
          fetchData();
        });
    }).catch(() => {
      Message.error('form validate error');
    });
  }
  function onSave() {
    form.validate().then((res) => {
      const values = {
        userContent: res.userContent,
        mainContent: res.mainContent,
      };
      updateProblemLanguage(props.problem.id, selectedLanguage, values)
        .then(() => {
          Message.success('已保存');
        });
    });
  }
  function onLanguageChange(e) {
    form.setFieldValue('mainContent', languageDemoTemplate[e].mainContent);
    form.setFieldValue('userContent', languageDemoTemplate[e].userContent);
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <>
      <Card title='语言文件' >
        <div className={styles['button-group']}>
          <Space>
            <Button type='primary' onClick={() => {setVisible(true); form.clearFields; }}>添加</Button>
            <Modal
              title='添加'
              style={{ width: 1000 }}
              visible={visible}
              onOk={onOk}
              onCancel={() => setVisible(false)}
              autoFocus={false}
              focusLock={true}
            >
              <Form
                form={form}
                layout='vertical'
              >
                <Grid.Row gutter={20}>
                  <Grid.Col span={24}>
                    <FormItem field='language' label='语言' required>
                      <Select onChange={onLanguageChange}>
                        {
                          selectOptions.map(item => {
                            return (
                              <Select.Option key={item} value={item}>
                                {LanguageMap[item]}
                              </Select.Option>
                            );
                          })
                        }
                      </Select>
                    </FormItem>
                  </Grid.Col>
                  <Grid.Col span={12}>
                    <FormItem field='mainContent' label='裁判测试程序' required rules={[
                      {required: true},
                      {
                        validator: async (value, callback) => {
                          return new Promise((resolve) => {
                            if (value.indexOf('@@@') === -1) {
                              callback('必须包含一个 @@@');
                              resolve();
                            } else {
                              resolve();
                            }
                          });
                        },
                      }
                    ]}>
                      <CodeMirror
                        height="300px"
                      />
                    </FormItem>
                  </Grid.Col>
                  <Grid.Col span={12}>
                    <FormItem field='userContent' label='用户待补全函数' required rules={[{required: true}]}>
                      <CodeMirror
                        height="300px"
                      />
                    </FormItem>
                  </Grid.Col>
                  <Grid.Col span={24}>
                    <p>
                      说明：
                    </p>
                    <p>
                      1. 在函数题中，用户只需要完成<b>用户待补全函数</b>的内容，不需要处理输入输出；
                      输入输出均在<b>裁判测试程序</b>中处理，并调用<b>用户待补全函数</b>。
                    </p>
                    <p>
                      2. 在函数题中，<b>用户待补全函数</b>将替换<b>裁判测试程序</b>中的 <code>@@@</code> 部分，
                      组成一个源码文件进行编译执行。其它与标准输入输出无差别。
                    </p>
                  </Grid.Col>
                </Grid.Row>
              </Form>
            </Modal>
          </Space>
        </div>
        <div>
          <Grid.Row gutter={20}>
            <Grid.Col flex='400px'>
              <List
                header='支持的语言列表'
                dataSource={languages}
                render={(item, index) => (
                  <List.Item key={index} actions={[
                    <div key={index}>
                      <Button onClick={() => edit(item.id)}>
                        <IconEdit />
                      </Button>,
                      <Popconfirm
                        title='Are you sure you want to delete?'
                        onOk={() => deleteLanguage(item.id)}
                      >
                        <Button><IconDelete /></Button>
                      </Popconfirm>
                    </div>
                  ]}>
                    <List.Item.Meta
                      title={item.languageName}
                    />
                  </List.Item>
                )}
              />
            </Grid.Col>
            <Grid.Col flex='auto'>
              { selectedLanguage === null ? 
                    <Empty />
                  :
                  <Form
                    form={form}
                    layout='vertical'
                    onSubmit={onSave}
                  >
                    <Grid.Row gutter={20}>
                      <Grid.Col span={24}>
                        <FormItem field='language' label='语言' required>
                          <Select disabled>
                            {
                              languages.map(item => {
                                return (
                                  <Select.Option key={item.id} value={item.languageCode}>
                                    {item.languageName}
                                  </Select.Option>
                                );
                              })
                            }
                          </Select>
                        </FormItem>
                      </Grid.Col>
                      <Grid.Col span={12}>
                        <FormItem field='mainContent' label='裁判测试程序' required rules={[
                          {required: true},
                          {
                            validator: async (value, callback) => {
                              return new Promise((resolve) => {
                                if (value.indexOf('@@@') === -1) {
                                  callback('必须包含 @@@');
                                  resolve();
                                } else {
                                  resolve();
                                }
                              });
                            },
                          }
                        ]}>
                          <CodeMirror
                            height="300px"
                          />
                        </FormItem>
                      </Grid.Col>
                      <Grid.Col span={12}>
                        <FormItem field='userContent' label='用户待补全函数' required rules={[{required: true}]}>
                          <CodeMirror
                            height="300px"
                          />
                        </FormItem>
                      </Grid.Col>
                      <Grid.Col span={24}>
                        <FormItem>
                          <Button type='primary' htmlType='submit'>{t['save']}</Button>
                        </FormItem>
                      </Grid.Col>
                      <Grid.Col span={24}>
                        <p>
                          说明：
                        </p>
                        <p>
                          1. 在函数题中，用户只需要完成<b>用户待补全函数</b>的内容，不需要处理输入输出；
                          输入输出均在<b>裁判测试程序</b>中处理，并调用<b>用户待补全函数</b>。
                        </p>
                        <p>
                          2. 在函数题中，<b>用户待补全函数</b>将替换<b>裁判测试程序</b>中的 <code>@@@</code> 部分，
                          组成一个源码文件进行编译执行。其它与标准输入输出无差别。
                        </p>
                      </Grid.Col>
                    </Grid.Row>
                  </Form>
              }
            </Grid.Col>
          </Grid.Row>
        </div>
      </Card>
    </>
  );
};

export default App;
