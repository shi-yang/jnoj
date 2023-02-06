import React, { useEffect, useState } from 'react';
import CodeMirror from '@uiw/react-codemirror';
import { cpp } from '@codemirror/lang-cpp';
import { java } from '@codemirror/lang-java';
import { python } from '@codemirror/lang-python';
import styles from './style/editor.module.less';
import { Button, Message, Select } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createSubmission } from '@/api/submission';
import useStorage from '@/utils/useStorage';
import * as themes from '@uiw/codemirror-themes-all';
import { IconSkin } from '@arco-design/web-react/icon';

const LANG_C = 'C';
const LANG_CPP = 'C++';
const LANG_JAVA = 'Java';
const LANG_PYTHON = 'Python';

export default function App(props) {
  const t = useLocale(locale);
  const [value, setValue] = useState('')
  const [language, setLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [theme, setTheme] = useStorage('CODE_THEME', 'githubLight');
  const languageOptions = [LANG_C, LANG_CPP, LANG_JAVA, LANG_PYTHON];
  const codemirrorLangs = [cpp, cpp, java, python];
  const [extensions, setExtensions] = useState(codemirrorLangs[language]);
  const onChange = React.useCallback((value, viewUpdate) => {
    setValue(value)
  }, []);
  const onChangeLanguage = (e) => {
    setLanguage(e);
    setExtensions(codemirrorLangs[e]);
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
        <CodeMirror
          height="100%"
          style={{
            height: '100%'
          }}
          extensions={extensions}
          theme={themes[theme]}
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
