import React, { useRef, useState } from 'react';
import CodeMirror from '@uiw/react-codemirror';
import { cpp } from '@codemirror/lang-cpp';
import { java } from '@codemirror/lang-java';
import { python } from '@codemirror/lang-python';
import styles from './style/editor.module.less';
import { Button, Card, Form, Grid, Input, Message, ResizeBox, Select, Space } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createSubmission } from '@/api/submission';
import useStorage from '@/utils/useStorage';
import * as themes from '@uiw/codemirror-themes-all';
import { IconDelete, IconDown, IconPlus, IconUp } from '@arco-design/web-react/icon';

const LANG_C = 'C';
const LANG_CPP = 'C++';
const LANG_JAVA = 'Java';
const LANG_PYTHON = 'Python';

function Console() {
  const t = useLocale(locale);
  const formRef = useRef();
  return (
    <ResizeBox
      directions={['top']}
      height={400}
      style={{
        minWidth: 100,
        maxWidth: '100%',
      }}
      >
      <Card className={styles['console-container']}>
        <Form
          ref={formRef}
          style={{ width: 600 }}
          autoComplete='off'
          initialValues={{
            cases: ['post1'],
          }}
          onSubmit={(v) => {
            console.log(v);
          }}
          onValuesChange={(_, v) => {
            console.log(_, v);
          }}
        >
          <Form.List field='cases'>
            {(fields, { add, remove, move }) => {
              return (
                <div>
                  {fields.map((item, index) => {
                    return (
                      <Grid.Row key={item.key}>
                        <Form.Item
                          field={item.field}
                          label={'Case-' + (index + 1)}
                          style={{
                            width: 470,
                          }}
                          rules={[
                            {
                              required: true,
                            },
                          ]}
                        >
                          <Input.TextArea />
                        </Form.Item>
                        <Button
                          icon={<IconDelete />}
                          shape='circle'
                          status='danger'
                          style={{
                            margin: '0 20px',
                          }}
                          onClick={() => remove(index)}
                        />
                      </Grid.Row>
                    );
                  })}
                  <Space size={20}>
                    <Button icon={<IconPlus />} onClick={() => { add(); }}>
                    </Button>
                  </Space>
                </div>
              );
            }}
          </Form.List>
          <Form.Item style={{ marginTop: 20 }}>
            <Space size={20}>
              <Button type='primary' status='success' htmlType='submit'>
                {t['console.runCode']}
              </Button>
            </Space>
          </Form.Item>
        </Form>
      </Card>
    </ResizeBox>
  )
}

export default function App(props) {
  const t = useLocale(locale);
  const [value, setValue] = useState('')
  const [language, setLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [theme, setTheme] = useStorage('CODE_THEME', 'githubLight');
  const languageOptions = [LANG_C, LANG_CPP, LANG_JAVA, LANG_PYTHON];
  const codemirrorLangs = [cpp, cpp, java, python];
  const [extensions, setExtensions] = useState(codemirrorLangs[language]);
  const [consoleVisible, setConsoleVisible] = useState(false);
  const onChange = React.useCallback((value, viewUpdate) => {
    setValue(value)
  }, []);
  const onChangeLanguage = (e) => {
    setLanguage(e);
    setExtensions(codemirrorLangs[e]);
  }
  const onSubmit = () => {
    const data = {
      problemId: props.problem.id,
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
          addBefore='主题'
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
        <Console />
      )}
      <div className={styles.footer}>
        <div className={styles.left}>
          <Button onClick={() => setConsoleVisible((v) => !v)} icon={consoleVisible ? <IconUp /> : <IconDown />}>
            控制台
          </Button>
        </div>
        <div className={styles.right}>
          <Button type='primary' onClick={(e) => onSubmit()}>{t['submit']}</Button>
        </div>
      </div>
    </div>
  );
}
