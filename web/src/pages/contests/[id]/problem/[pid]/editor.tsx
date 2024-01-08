import React, { useEffect, useRef, useState } from 'react';
import styles from '../../style/editor.module.less';
import { Button, Message, Select } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from '../../locale';
import { createSubmission } from '@/api/submission';
import useStorage from '@/utils/useStorage';
import { IconDown, IconPlayArrow, IconSkin, IconUp } from '@arco-design/web-react/icon';
import Editor from '@/components/Editor';
import { getContestProblemLanguage, listContestProblemLanguages } from '@/api/contest';
import RecentlySubmitted from '@/modules/submission/RecentlySubmitted';
import { isLogged } from '@/utils/auth';
import Console from '@/modules/problem/Console';

export default function App(props: any) {
  const t = useLocale(locale);
  const [value, setValue] = useStorage('CODE', '');
  const [language, setLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [languageList, setLanguageList] = useState([]);
  const [theme, setTheme] = useStorage('CODE_THEME', 'light');
  const [lastSubmissionID, setLastSubmissionID] = useState(0);
  const [languageId, setLanguageId] = useState(0);
  const themes = [
    'light', 'vs-dark'
  ];
  const languageNameToMonacoLanguage = {
    0: 'c',
    1: 'cpp',
    2: 'java',
    3: 'python'
  };
  const [consoleVisible, setConsoleVisible] = useState(false);
  const consoleRef = useRef(null);
  const [isMounted, setIsMounted] = useState(false); 
  const runCode = () => {
    setConsoleVisible(true);
    consoleRef.current.runCode();
  };
  useEffect(() => {
    setIsMounted(true);
    getLanguages();
  }, []);
  const onChange = React.useCallback((value, viewUpdate) => {
    setValue(value);
  }, []);
  const onChangeLanguage = (e) => {
    setLanguage(e);
    // 函数题需要查询对应的语言模板
    if (props.problem.type === 'FUNCTION') {
      const userLang = languageList.find(item => item.languageCode === Number(e));
      getContestProblemLanguage(props.contest.id, props.problem.number, userLang.id)
        .then(res => {
          setLanguageId(userLang.id);
          setValue(res.data.userContent);
        });
    }
  };
  const getLanguages = () => {
    listContestProblemLanguages(props.contest.id, props.problem.number)
      .then(res => {
        const langs = res.data.data;
        setLanguageList(langs);
        const userLang = langs.find(item => {
          return item.languageCode === Number(language);
        });
        if (userLang.id !== 0 && userLang) {
          getContestProblemLanguage(props.contest.id, props.problem.number, userLang.id)
            .then(res => {
              setLanguageId(userLang.id);
              setValue(res.data.userContent);
            });
        }
      });
  };
  const onSubmit = () => {
    const data = {
      problemNumber: props.problem.number,
      entityId: props.contest.id,
      entityType: 'CONTEST',
      source: value,
      language: language,
    };
    createSubmission(data).then(res => {
      setLastSubmissionID(res.data.id);
      Message.success('已提交');
    }).catch(err => {
      if (err.response.data.reason === 'SUBMISSION_RATE_LIMIT') {
        Message.error('您的提交过于频繁');
      }
    });
  };
  return (
    <div className={styles['container']}>
      <div className={styles['code-header']}>
        <Select
          size='large'
          defaultValue={language}
          style={{ width: 154 }}
          onChange={(e) => onChangeLanguage(e)}
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
          defaultValue={theme}
          style={{ width: 70 }}
          onChange={(e) => setTheme(e)}
          triggerProps={{
            autoAlignPopupWidth: false,
            autoAlignPopupMinWidth: true,
          }}
          renderFormat={() => <IconSkin />}
        >
          {themes.map((item, index) => 
            <Select.Option key={index} value={item}>
              {item}
            </Select.Option>
          )}
        </Select>
      </div>
      <div className={styles.container}>
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
          <Console ref={consoleRef} key={props.problem.id} problem={props.problem} language={language} languageId={languageId} source={value} />
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
            {t['run']}
          </Button>
        </div>
        <div className={styles.right}>
          { isLogged()
            &&
            <RecentlySubmitted
              entityId={props.contest.id}
              entityType={1}
              problemId={props.problem.number}
              lastSubmissionID={lastSubmissionID}
            />
          }
          <Button type='primary' onClick={() => onSubmit()}>{t['submit']}</Button>
        </div>
      </div>
    </div>
  );
}
