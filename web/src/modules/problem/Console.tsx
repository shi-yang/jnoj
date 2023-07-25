import React from 'react';
import { runRequest, runSandbox } from '@/api/sandbox';
import useLocale from '@/utils/useLocale';
import { Form, ResizeBox, Spin, Card, Tabs, Grid, Input, Button, Space, Typography, Message } from '@arco-design/web-react';
import { IconDelete, IconPlus } from '@arco-design/web-react/icon';
import locale from './locale';
import { useState, useImperativeHandle, forwardRef } from 'react';
import styles from './style/console.module.less';
import Highlight from '@/components/Highlight';

function ConsoleComponent({ problem, language, languageId, source }: any, ref) {
  const t = useLocale(locale);
  const [casesResult, setCasesResult] = useState([]);
  const [activeTab, setActiveTab] = useState('cases');
  const [loading, setLoading] = useState(false);
  const [compileMsg, setCompileMsg] = useState('');
  const [form] = Form.useForm();
  const [cases, setCases] = useState(problem.sampleTests.map(item => item.input));
  useImperativeHandle(ref, () => ({
    runCode: () => {
      onSubmit();
    }
  }));
  const onSubmit = () => {
    form.validate().then((values) => {
      setCasesResult([]);
      setActiveTab('result');
      setCompileMsg('');
      setLoading(true);
      const stdins = [];
      cases.forEach(item => {
        stdins.push(item);
      });
      const req: runRequest = {
        stdin: stdins,
        language,
        source,
        timeLimit: problem.timeLimit,
        memoryLimit: problem.memoryLimit,
      };
      if (languageId !== 0) {
        req.languageId = languageId;
      }
      runSandbox(req).then(res => {
        if (res.data.compileMsg !== '') {
          setCompileMsg(res.data.compileMsg);
          return;
        }
        res.data.results.forEach((value, index) => {
          setCasesResult(v => [...v, { stdin: cases[index], ...value }]);
        });
      }).catch(err => {
        if (err.response.data.reason === 'SUBMISSION_RATE_LIMIT') {
          Message.error('您的提交过于频繁');
        }
      }).finally(() => {
        setLoading(false);
      });
    }).catch(err => {
      console.log(err);
    });
  };
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
                  );
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
  );
}

export default forwardRef(ConsoleComponent);
