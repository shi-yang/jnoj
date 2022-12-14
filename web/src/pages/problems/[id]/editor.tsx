import React, { useEffect, useRef, useState } from 'react';
import CodeMirror from '@uiw/react-codemirror';
import { cpp } from '@codemirror/lang-cpp';
import { java } from '@codemirror/lang-java';
import { python } from '@codemirror/lang-python';
import styles from './style/editor.module.less';
import { Button, Card, Form, Grid, Input, Message, ResizeBox, Select, Space, Spin, Tabs, Typography } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createSubmission } from '@/api/submission';
import useStorage from '@/utils/useStorage';
import * as themes from '@uiw/codemirror-themes-all';
import { IconDelete, IconDown, IconPlayArrow, IconPlus, IconShareExternal, IconUp } from '@arco-design/web-react/icon';
import { runRequest, runSandbox } from '@/api/sandbox';
import Highlight from '@/components/Highlight';

const LANG_C = 'C';
const LANG_CPP = 'C++';
const LANG_JAVA = 'Java';
const LANG_PYTHON = 'Python';

function Console({problem, defaultCases, language, source}) {
  const t = useLocale(locale);
  const [casesResult, setCasesResult] = useState([]);
  const [activeTab, setActiveTab] = useState('cases');
  const [loading, setLoading] = useState(false);
  const [compileMsg, setCompileMsg] = useState('');
  const [form] = Form.useForm();
  const [cases, setCases] = useState(defaultCases);
  const onSubmit = () => {
    form.validate().then((values) => {
      setCasesResult([]);
      setActiveTab('result');
      const p = [];
      cases.forEach(value => {
        const data:runRequest = {
          stdin: value,
          language,
          source,
          timeLimit: problem.timeLimit,
          memoryLimit: problem.memoryLimit,
        };
        p.push(runSandbox(data))
      })
      setCompileMsg('');
      setLoading(true);
      Promise.all(p)
        .then(res => {
          res.forEach((value, index) => {
            if (value.data.compileMsg != '') {
              setCompileMsg(value.data.compileMsg);
              return;
            }
            setCasesResult(v => [...v, {stdin: cases[index], ...value.data}]);
          })
        })
        .finally(() => {
          setLoading(false);
        })
    })
  }
  return (
    <ResizeBox
      directions={['top']}
      style={{
        height: 400,
        minWidth: 100,
        maxWidth: '100%',
      }}
      >
      <Spin loading={loading} style={{width: '100%', height: '100%'}} block={false}>
        <Card className={styles['console-container']}>
          <Tabs
            style={{
              margin: -15,
            }}
            activeTab={activeTab}
            onClickTab={(e) => setActiveTab(e)}
            destroyOnHide
            extra={
              <Button type='outline' icon={<IconPlayArrow />} status='success' onClick={onSubmit}>
                {t['console.runCode']}
              </Button>
            }
          >
            <Tabs.TabPane key='cases' title={t['console.testCase']} style={{ width: '100%', padding: '15px' }}>
              <Form
                form={form}
                autoComplete='off'
                initialValues={{
                  cases: cases,
                }}
                onSubmit={onSubmit}
                onValuesChange={(_, v) => {
                  setCases(v.cases);
                }}
              >
                <Form.List field='cases'>
                  {(fields, { add, remove, move }) => {
                    return (
                      <div>
                        {fields.map((item, index) => {
                          return (
                            <Grid.Row key={item.key}>
                              <Grid.Col flex='auto'>
                                <Form.Item
                                  field={item.field}
                                  label={'Case ' + (index + 1)}
                                  rules={[{ required: true }]}
                                >
                                  <Input.TextArea rows={3} />
                                </Form.Item>
                              </Grid.Col>
                              <Grid.Col flex='100px'>
                                <Button
                                  icon={<IconDelete />}
                                  shape='circle'
                                  status='danger'
                                  style={{
                                    margin: '0 20px',
                                  }}
                                  onClick={() => remove(index)}
                                />
                              </Grid.Col>
                            </Grid.Row>
                          );
                        })}
                        <Space size={20}>
                          <Button icon={<IconPlus />} onClick={() => { add(); }}>
                            {t['console.addCase']}
                          </Button>
                        </Space>
                      </div>
                    );
                  }}
                </Form.List>
              </Form>
            </Tabs.TabPane>
            <Tabs.TabPane key='result' title={t['console.result']} style={{ width: '100%', padding: '15px' }}>
              <div>
                { compileMsg === '' ? casesResult.map((item, index) => {
                  return (
                    <div className={styles['sample-test']} key={index}>
                      <div className={styles.input}>
                        <h4>{t['input']}</h4>
                        <pre>{item.stdin}</pre>
                      </div>
                      <div className={styles.output}>
                        <h4>{t['output']}</h4>
                        <pre>{ item.stdout }</pre>
                      </div>
                    </div>
                  )
                }) :
                <>
                  <Typography.Title heading={4}>编译错误</Typography.Title>
                  <Highlight content={compileMsg} />
                </> }
              </div>
            </Tabs.TabPane>
          </Tabs>
        </Card>
      </Spin>
    </ResizeBox>
  )
}

export default function App({problem}) {
  const t = useLocale(locale);
  const [value, setValue] = useState('')
  const [language, setLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [theme, setTheme] = useStorage('CODE_THEME', 'githubLight');
  const languageOptions = [LANG_C, LANG_CPP, LANG_JAVA, LANG_PYTHON];
  const codemirrorLangs = [cpp, cpp, java, python];
  const [extensions, setExtensions] = useState(codemirrorLangs[language]);
  const [consoleVisible, setConsoleVisible] = useState(false);
  const [cases, setCases] = useState([]);
  const onChange = React.useCallback((value, viewUpdate) => {
    setValue(value)
  }, []);
  const onChangeLanguage = (e) => {
    setLanguage(e);
    setExtensions(codemirrorLangs[e]);
  }
  const onSubmit = () => {
    const data = {
      problemId: problem.id,
      source: value,
      language: language,
    };
    createSubmission(data).then(res => {
      Message.success('已提交');
    });
  }
  useEffect(() => {
    setCases(problem.sampleTests.map(item => item.input))
  }, [])
  return (
    <div className={styles['container']}>
      <div className={styles['code-header']}>
        <Select
          size='large'
          defaultValue={language}
          placeholder='请选择语言'
          style={{ width: 154 }}
          onChange={(e) => onChangeLanguage(e)}
        >
          {languageOptions.map((item, index) => {
            return (
              <Select.Option key={item} value={`${index}`}>
                {item}
              </Select.Option>
            )
          })}
        </Select>
        <Select
          size='large'
          addBefore={t['theme']}
          defaultValue={theme}
          placeholder='编辑器主题'
          style={{ width: 200 }}
          onChange={(e) => setTheme(e)}
        >
          {Object.keys(themes).map((item, index) => {
            return (
              <Select.Option key={index} value={item}>
                {item}
              </Select.Option>
            )
          })}
        </Select>
      </div>
      <CodeMirror
        height="100%"
        className={styles['code-editor']}
        extensions={extensions}
        theme={themes[theme]}
        onChange={onChange}
      />
      { consoleVisible && (
        <Console problem={problem} defaultCases={cases} language={language} source={value} />
      )}
      <div className={styles.footer}>
        <div className={styles.left}>
          <Button
            icon={consoleVisible ? <IconUp /> : <IconDown />}
            onClick={() => setConsoleVisible((v) => !v)}
          >
            Console
          </Button>
        </div>
        <div className={styles.right}>
          <Button type='primary' status='success' icon={<IconShareExternal />} onClick={(e) => onSubmit()}>{t['submit']}</Button>
        </div>
      </div>
    </div>
  );
}
