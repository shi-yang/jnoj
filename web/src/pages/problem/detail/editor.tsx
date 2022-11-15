import React, { useState } from 'react';
import CodeMirror from '@uiw/react-codemirror';
import { javascript } from '@codemirror/lang-javascript';
import styles from './style/editor.module.less';
import { Button, Message, Select } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createSubmission } from '@/api/submission';

export default function App(props) {
  const t = useLocale(locale);
  const [value, setValue] = useState('')
  const [language, setLanguage] = useState(1);
  const onChange = React.useCallback((value, viewUpdate) => {
    setValue(value)
  }, []);
  const languageOptions = ['C', 'C++', 'Java', 'Python']
  function onChangeLanguage(e) {
    setLanguage(e)
  }
  const onSubmit = () => {
    const data = {
      problemId: props.problem.id,
      source: value,
      language: language,
    }
    createSubmission(data).then(res => {
      Message.success('已提交')
    })
  }
  return (
    <>
      <div className={styles['code-header']}>
        <Select size='large' defaultValue={1} placeholder='请选择语言' style={{ width: 154 }} className={styles['aaa']} onChange={(e) => onChangeLanguage(e)}>
          {languageOptions.map((item, index) => {
            return (
              <Select.Option key={item} value={index}>
                {item}
              </Select.Option>
            )
          })}
        </Select>
      </div>
      <div className={styles.container}>
        <CodeMirror
          height="100%"
          style={{
            height: '100%'
          }}
          extensions={[
            javascript({ jsx: true })
          ]}
          onChange={onChange}
        />
        <div className={styles.footer}>
          <div className={styles.left}>
          </div>
          <div className={styles.right}>
            <Button type='primary' onClick={(e) => onSubmit()}>{t['submit']}</Button>
          </div>
        </div>
      </div>
    </>
  );
}
