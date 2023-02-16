import React, { forwardRef, useContext, useEffect, useImperativeHandle, useRef, useState } from 'react';
import styles from './style/editor.module.less';
import { Button, Card, Form, Grid, Input, Message, ResizeBox, Select, Space, Spin, Tabs, Typography } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createSubmission } from '@/api/submission';
import useStorage from '@/utils/useStorage';
import { IconDelete, IconDown, IconPlayArrow, IconPlus, IconShareExternal, IconSkin, IconUp } from '@arco-design/web-react/icon';
import { runRequest, runSandbox } from '@/api/sandbox';
import Highlight from '@/components/Highlight';
import RecentlySubmitted from '@/modules/submission/RecentlySubmitted';
import { isLogged } from '@/utils/auth';
import ProblemContext from './context';
import { getProblemLanguage, listProblemLanguages } from '@/api/problem-file';
import Editor from "@monaco-editor/react";

const themes = [
  'light', 'vs-dark'
];
const languageNameToMonacoLanguage = {
  0: 'c',
  1: 'cpp',
  2: 'java',
  3: 'python'
};

export default function App() {
  const t = useLocale(locale);
  const { problem } = useContext(ProblemContext);
  const [value, setValue] = useState('')
  const [language, setLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [languageId, setLanguageId] = useState(0);
  const [theme, setTheme] = useStorage('CODE_THEME', 'light');
  const [languageList, setLanguageList] = useState([]);
  const [consoleVisible, setConsoleVisible] = useState(false);
  const [cases, setCases] = useState([]);
  const [lastSubmissionID, setLastSubmissionID] = useState(0);
  const [isMounted, setIsMounted] = useState(false); 
  const consoleRef = useRef(null);
  const runCode = () => {
    setConsoleVisible(true);
    consoleRef.current.runCode();
  }
  const onChange = React.useCallback((value, viewUpdate) => {
    setValue(value)
  }, []);
  
  const onChangeLanguage = (e) => {
    setLanguage(e);
    // 函数题需要查询对应的语言模板
    if (problem.type === 'FUNCTION') {
      const lang = languageList.find(item => item.languageCode === Number(e))
      getProblemLanguage(problem.id, lang.id)
        .then(res => {
          setLanguageId(lang.id);
          setValue(res.data.userContent);
        })
    }
  }
  const getLanguages = () => {
    listProblemLanguages(problem.id)
      .then(res => {
        const langs = res.data.data
        setLanguageList(langs);
        const userLang = langs.find(item => item.languageCode === Number(language))
        if (problem.type === 'FUNCTION' && userLang) {
          getProblemLanguage(problem.id, userLang.id)
            .then(res => {
              setLanguageId(userLang.id);
              setValue(res.data.userContent);
            })
        } else {
          setValue('');
        }
      })
  }
  const onSubmit = () => {
    const data = {
      problemId: problem.id,
      source: value,
      language: language,
    };
    createSubmission(data).then(res => {
      Message.success('已提交');
      setLastSubmissionID(res.data.id);
    });
  }
  useEffect(() => {
    if (problem.id === 0) {
      return;
    }
    setCases(problem.sampleTests.map(item => item.input));
    setIsMounted(true);
    getLanguages();
  }, [problem.id])
  return (
    <div className={styles['container']}>
      <div className={styles['code-header']}>
        <Select
          size='large'
          defaultValue={language}
          style={{ width: 154 }}
          onChange={onChangeLanguage}
        >
          {languageList.map((item, index) => {
            return (
              <Select.Option key={index} value={`${item.languageCode}`}>
                {item.languageName}
              </Select.Option>
            )
          })}
        </Select>
        <Select
          size='large'
          defaultValue={theme}
          style={{ width: 70 }}
          onChange={(e) => setTheme(e)}
          triggerProps={{
            autoAlignPopupWidth: false,
            autoAlignPopupMinWidth: true,
          }}
          renderFormat={(option, value) => <IconSkin />}
        >
          {themes.map((item, index) => 
            <Select.Option key={index} value={item}>
              {item}
            </Select.Option>
          )}
        </Select>
      </div>
      <div className={styles['code-editor']}>
        <Editor
          language={languageNameToMonacoLanguage[language]}
          options={{
            automaticLayout: true,
            fontSize: 16
          }}
          value={value}
          theme={theme}
          onChange={onChange}
        />
      </div>
      {
        isMounted &&
        <div style={{display: consoleVisible ? 'block' : 'none'}}>
          <Console ref={consoleRef} problem={problem} defaultCases={cases} language={language} languageId={languageId} source={value} />
        </div>
      }
      <div className={styles.footer}>
        <div className={styles.left}>
          <Button
            icon={consoleVisible ? <IconUp /> : <IconDown />}
            onClick={() => setConsoleVisible((v) => !v)}
          >
            Console
          </Button>
          <Button
            type='outline'
            icon={<IconPlayArrow />}
            status='success'
            onClick={runCode}
          >
            {t['console.runCode']}
          </Button>
        </div>
        <div className={styles.right}>
          { isLogged() && <RecentlySubmitted problemId={problem.id} lastSubmissionID={lastSubmissionID} /> }
          <Button type='primary' status='success' icon={<IconShareExternal />} onClick={(e) => onSubmit()}>
            {t['submit']}
          </Button>
        </div>
      </div>
    </div>
  );
}

function ConsoleComponent({ problem, defaultCases, language, languageId, source }, ref) {
  const t = useLocale(locale);
  const [casesResult, setCasesResult] = useState([]);
  const [activeTab, setActiveTab] = useState('cases');
  const [loading, setLoading] = useState(false);
  const [compileMsg, setCompileMsg] = useState('');
  const [form] = Form.useForm();
  const [cases, setCases] = useState(defaultCases);
  useImperativeHandle(ref, () => ({
    runCode: () => {
      onSubmit()
    }
  }))
  const onSubmit = () => {
    form.validate().then((values) => {
      setCasesResult([]);
      setActiveTab('result');
      const p = [];
      cases.forEach(value => {
        const data: runRequest = {
          stdin: value,
          language,
          source,
          timeLimit: problem.timeLimit,
          memoryLimit: problem.memoryLimit,
        };
        if (languageId !== 0) {
          data.languageId = languageId          
        }
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
            setCasesResult(v => [...v, { stdin: cases[index], ...value.data }]);
          })
        })
        .finally(() => {
          setLoading(false);
        })
    }).catch(err => {
      console.log(err)
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
      <Spin loading={loading} style={{ width: '100%', height: '100%' }} block={false}>
        <Card className={styles['console-container']}>
          <Tabs
            style={{
              margin: -15,
            }}
            activeTab={activeTab}
            onClickTab={(e) => setActiveTab(e)}
            destroyOnHide
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
                {compileMsg === '' ? casesResult.map((item, index) => {
                  return (
                    <div className={styles['sample-test']} key={index}>
                      <div className={styles.input}>
                        <h4>{t['input']}</h4>
                        <pre>{item.stdin}</pre>
                      </div>
                      <div className={styles.output}>
                        <h4>{t['output']}</h4>
                        <pre>{item.stdout}</pre>
                      </div>
                    </div>
                  )
                }) :
                  <>
                    <Typography.Title heading={4}>{t['console.result.compileError']}</Typography.Title>
                    <Highlight content={compileMsg} />
                  </>}
              </div>
            </Tabs.TabPane>
          </Tabs>
        </Card>
      </Spin>
    </ResizeBox>
  )
}

const Console = forwardRef(ConsoleComponent)
