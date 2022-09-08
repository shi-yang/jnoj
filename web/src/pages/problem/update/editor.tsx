import React from "react";
import CodeMirror from "@uiw/react-codemirror";
import { javascript } from "@codemirror/lang-javascript";
import styles from './style/editor.module.less';
import { Button } from "@arco-design/web-react";
import useLocale from '@/utils/useLocale';
import locale from './locale';

export default function App() {
  const t = useLocale(locale);
  const onChange = React.useCallback((value, viewUpdate) => {
    console.log("value:", value);
  }, []);
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
        <div className={styles.left}></div>
        <div className={styles.right}>
          <Button type='primary'>{t['submit']}</Button>
        </div>
      </div>
    </div>
  );
}
