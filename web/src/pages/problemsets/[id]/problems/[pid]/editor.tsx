import React, { useContext, useEffect, useRef, useState } from 'react';
import styles from './style/editor.module.less';
import { Button, Checkbox, Form, Input, Message, Radio, Result, Select } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createSubmission } from '@/api/submission';
import useStorage from '@/utils/useStorage';
import { IconDown, IconPlayArrow, IconShareExternal, IconSkin, IconUp } from '@arco-design/web-react/icon';
import RecentlySubmitted from '@/modules/submission/RecentlySubmitted';
import { isLogged } from '@/utils/auth';
import ProblemContext from './context';
import { getProblemLanguage, listProblemLanguages } from '@/api/problem-file';
import Editor from '@/components/Editor';
import { useRouter } from 'next/router';
import Console from '@/modules/problem/Console';

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
  const [value, setValue] = useState('');
  const [language, setLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [languageId, setLanguageId] = useState(0);
  const [theme, setTheme] = useStorage('CODE_THEME', 'light');
  const [languageList, setLanguageList] = useState([]);
  const [consoleVisible, setConsoleVisible] = useState(false);
  const [lastSubmissionID, setLastSubmissionID] = useState(0);
  const [isMounted, setIsMounted] = useState(false); 
  const consoleRef = useRef(null);
  const router = useRouter();
  const runCode = () => {
    setConsoleVisible(true);
    consoleRef.current.runCode();
  };
  const onChange = React.useCallback((value) => {
    setValue(value);
  }, []);
  const statement = problem.statements[0] ?? problem.statements[0];
  
  const onChangeLanguage = (e) => {
    setLanguage(e);
    // 函数题需要查询对应的语言模板
    if (problem.type === 'FUNCTION') {
      const lang = languageList.find(item => item.languageCode === Number(e));
      getProblemLanguage(problem.id, lang.id)
        .then(res => {
          setLanguageId(lang.id);
          setValue(res.data.userContent);
        });
    }
  };
  const getLanguages = () => {
    listProblemLanguages(problem.id)
      .then(res => {
        const langs = res.data.data;
        setLanguageList(langs);
        const userLang = langs.find(item => item.languageCode === Number(language));
        if (problem.type === 'FUNCTION' && userLang) {
          getProblemLanguage(problem.id, userLang.id)
            .then(res => {
              setLanguageId(userLang.id);
              setValue(res.data.userContent);
            });
        } else {
          setValue('');
        }
      });
  };
  const onSubmit = () => {
    const { id, pid } = router.query;
    const data = {
      problemNumber: pid,
      source: value,
      language: language,
      entityId: id,
      entityType: 'PROBLEMSET'
    };
    createSubmission(data).then(res => {
      setLastSubmissionID(res.data.id);
    }).catch(err => {
      if (err.response.data.reason === 'SUBMISSION_RATE_LIMIT') {
        Message.error('您的提交过于频繁');
      }
    });
  };
  useEffect(() => {
    if (problem.id === 0) {
      return;
    }
    setIsMounted(true);
    getLanguages();
  }, [problem.id]);
  return (
    <div className={styles['container']}>
      <div className={styles['code-header']}>
        <Select
          disabled={problem.type === 'OBJECTIVE'}
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
            );
          })}
        </Select>
        <Select
          size='large'
          disabled={problem.type === 'OBJECTIVE'}
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
        {
          problem.type === 'OBJECTIVE' ? (
            <div>
              <Result
                title={<h1>答题区域</h1>}
                icon={null}
              >
                <Form>
                  {statement.type === 'CHOICE' && (
                    <Form.Item label={'单选题'}>
                      <Radio.Group
                        direction='vertical'
                        onChange={e => setValue(JSON.stringify([e]))}
                      >
                        {statement.input !== '' && JSON.parse(statement.input).map((item, index) => (
                          <Radio key={index} value={item}>
                            {item}
                          </Radio>
                        ))}
                      </Radio.Group>
                    </Form.Item>
                  )}
                  {statement.type === 'MULTIPLE' && (
                    <Form.Item label={'多选题'}>
                      <Checkbox.Group
                        direction='vertical'
                        onChange={e => setValue(Array.isArray(e) ? JSON.stringify(e) : e)}
                      >
                        {statement.input !== '' && JSON.parse(statement.input).map((item, index) => (
                          <Checkbox key={index} value={item}>
                            {item}
                          </Checkbox>
                        ))}
                      </Checkbox.Group>
                    </Form.Item>
                  )}
                  {statement.type === 'FILLBLANK' && (
                    statement.input !== '' && JSON.parse(statement.input).map((item, index) => (
                      <Form.Item label={`填空 ${index+1}`} key={index}>
                        <Input.TextArea placeholder='Please input' onChange={(e) => {
                          setValue(v => {
                            let tmp = [];
                            if (v === '') {
                              tmp = JSON.parse(statement.input);
                              tmp[index] = e;
                              return JSON.stringify(tmp);
                            }
                            const value = JSON.parse(v);
                            value[index] = e;
                            return JSON.stringify(value);
                          });
                        }} />
                      </Form.Item>
                    ))
                  )}
                </Form>
              </Result>
            </div>
          ) : (
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
          )
        }
      </div>
      {
        isMounted &&
        <div style={{display: consoleVisible ? 'block' : 'none'}}>
          <Console ref={consoleRef} key={problem.id} problem={problem} language={language} languageId={languageId} source={value} />
        </div>
      }
      <div className={styles.footer}>
        <div className={styles.left}>
          <Button
            disabled={problem.type === 'OBJECTIVE'}
            icon={consoleVisible ? <IconUp /> : <IconDown />}
            onClick={() => setConsoleVisible((v) => !v)}
          >
            Console
          </Button>
          <Button
            disabled={problem.type === 'OBJECTIVE'}
            type='outline'
            icon={<IconPlayArrow />}
            status='success'
            onClick={runCode}
          >
            {t['console.runCode']}
          </Button>
        </div>
        <div className={styles.right}>
          { isLogged() && <RecentlySubmitted animation={true} problemId={problem.id} lastSubmissionID={lastSubmissionID} /> }
          <Button type='primary' status='success' icon={<IconShareExternal />} onClick={(e) => onSubmit()}>
            {t['submit']}
          </Button>
        </div>
      </div>
    </div>
  );
}
