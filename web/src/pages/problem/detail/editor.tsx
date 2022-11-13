import React, { useState } from 'react';
import CodeMirror from '@uiw/react-codemirror';
import { javascript } from '@codemirror/lang-javascript';
import styles from './style/editor.module.less';
import { Button, Message } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createSubmission } from '@/api/submission';

export default function App(props) {
  const t = useLocale(locale);
  const [value, setValue] = useState('')
  const onChange = React.useCallback((value, viewUpdate) => {
    setValue(value)
  }, []);
  const onSubmit = () => {
    const data = {
      problemId: props.problem.id,
      source: value,
      language: props.language,
    }
    createSubmission(data).then(res => {
      Message.success('已提交')
    })
  }
  return (
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
  );
}
