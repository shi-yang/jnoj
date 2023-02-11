import React, { useEffect, useState } from 'react';
import styles from './style/editor.module.less';
import { Button, Message, Select } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createSubmission } from '@/api/submission';
import useStorage from '@/utils/useStorage';
import { IconSkin } from '@arco-design/web-react/icon';
import Editor from "@monaco-editor/react";
import { getContestProblemLanguage, listContestProblemLanguages } from '@/api/contest';

export default function App(props) {
  const t = useLocale(locale);
  const [value, setValue] = useState('')
  const [language, setLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [languageList, setLanguageList] = useState([]);
  const [theme, setTheme] = useStorage('CODE_THEME', 'githubLight');
  const themes = [
    'light', 'vs-dark'
  ];
  const languageNameToMonacoLanguage = {
    0: 'c',
    1: 'cpp',
    2: 'java',
    3: 'python'
  };
  useEffect(() => {
    getLanguages();
  }, []);
  const onChange = React.useCallback((value, viewUpdate) => {
    setValue(value)
  }, []);
  const onChangeLanguage = (e) => {
    setLanguage(e);
  }
  const getLanguages = () => {
    listContestProblemLanguages(props.contest.id, props.problem.number)
      .then(res => {
        const langs = res.data.data
        setLanguageList(langs);
        const userLang = langs.find(item => {
          return item.languageCode === Number(language)
        })
        if (userLang.id !== 0 && userLang) {
          getContestProblemLanguage(props.contest.id, props.problem.number, userLang.id)
            .then(res => {
              setValue(res.data.userContent);
            })
        }
      })
  }
  const onSubmit = () => {
    const data = {
      problemNumber: props.problem.number,
      contestId: props.contest.id,
      source: value,
      language: language,
    };
    createSubmission(data).then(res => {
      Message.success('已提交');
    });
  }
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
          {Object.keys(themes).map((item, index) => {
            if (item.indexOf('Init') === -1) {
              return (
                <Select.Option key={index} value={item}>
                  {item}
                </Select.Option>
              )
            }
          })}
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
      <div className={styles.footer}>
        <div className={styles.left}>
        </div>
        <div className={styles.right}>
          <Button type='primary' onClick={(e) => onSubmit()}>{t['submit']}</Button>
        </div>
      </div>
    </div>
  );
}
