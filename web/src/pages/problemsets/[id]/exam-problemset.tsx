import React, { useEffect, useState } from 'react';
import { Anchor, Button, Card, Checkbox, Divider, Empty, Form, Input, Link, List, Message, Popconfirm, Radio, Space, Tag, Typography } from '@arco-design/web-react';
import { createProblemsetAnswer, getProblemsetAnswer, listProblemsetAnswers, listProblemsetProblems, updateProblemsetAnswer } from '@/api/problemset';
import useLocale from '@/utils/useLocale';
import styles from './style/index.module.less';
import locale from './locale';
import { useRouter } from 'next/router';
import ReactMarkdown from 'react-markdown';
import rehypeHighlight from 'rehype-highlight';
import rehypeKatex from 'rehype-katex';
import remarkMath from 'remark-math';
import { FormatTime } from '@/utils/format';
import ProblemContent from '@/modules/problem/ProblemContent';
import Editor from '@/components/Editor';
import useStorage from '@/utils/useStorage';
const AnchorLink = Anchor.Link;
const themes = [
  'light', 'vs-dark'
];
const languageNameToMonacoLanguage = {
  0: 'c',
  1: 'cpp',
  2: 'java',
  3: 'python'
};

function AnswerSheet({problems, answers, unsubmitAnswerId, problemset}: {problems:any[], answers: any, unsubmitAnswerId:number, problemset:any}) {
  const router = useRouter();
  function onSubmit() {
    updateProblemsetAnswer(problemset.id, unsubmitAnswerId, {submittedAt: new Date()})
      .then((res) => {
        router.reload();
      });
  }
  return (
    <div className={styles['answer-sheet-container']}>
      <div className='container'>
        <Anchor
          affix={false}
          style={{ backgroundColor: 'var(--color-bg-2)', width: '85px' }}
        >
          {problems.map((item, index) => (
            <AnchorLink
              key={index}
              href={`#problem-${item.problemId}`}
              title={<Button status={answers[`problem-${item.problemId}`] ? 'success' : 'default'}>{index + 1}</Button>}
            />
          ))}
          <AnchorLink title={
            <Popconfirm
              focusLock
              title='确认交卷'
              onOk={() => onSubmit()}
            >
              <Button type='primary'>交卷</Button>
            </Popconfirm>
          } />
        </Anchor>
      </div>
    </div>
  );
}

function RenderObjectiveItem({index, statement}: {index: number, statement: any}) {
  const t = useLocale(locale);
  let choices = [];
  if (statement.input !== '') {
    choices = JSON.parse(statement.input);
  }
  let legend = statement.legend;
  if (statement.type === 'FILLBLANK') {
    legend = statement.legend.replace(/{.*?}/g, '`________`');
  }
  return (
    <div>
      <Typography.Title heading={5} style={{marginBottom: 0}}>
        <Tag color='blue'>
          {t[`objective.type.${statement.type}`]}
        </Tag>
        {statement.title}
      </Typography.Title>
      <Typography.Paragraph>
        <ReactMarkdown
          remarkPlugins={[remarkMath]}
          rehypePlugins={[rehypeKatex, rehypeHighlight]}
        >
          {`${index + 1}. ${legend}`}
        </ReactMarkdown>
      </Typography.Paragraph>
      <Typography.Paragraph>
        {(statement.type == 'CHOICE') && (
          <Form.Item field={`problem-${statement.problemId}`}>
            <Radio.Group direction='vertical' options={
              choices.map((item, index) => 
                ({label: item, value: item})
              )
            } />
          </Form.Item>
        )}
        {(statement.type == 'MULTIPLE') && (
          <Form.Item field={`problem-${statement.problemId}`}>
            <Checkbox.Group direction='vertical' options={
              choices.map((item, index) => 
                ({label: item, value: item})
              )
            }/>
          </Form.Item>
        )}
        {statement.type === 'FILLBLANK' && (
          choices.map((item, key) => (
            <Form.Item field={`problem-${statement.problemId}.${key}`} key={key}>
              <Input.TextArea />
            </Form.Item>
          ))
        )}
      </Typography.Paragraph>
    </div>
  );
}

function RenderProgrammingItem({index, statement, problem}: {index: number, statement: any, problem:any}) {
  const t = useLocale(locale);
  const [language, setLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [theme, setTheme] = useStorage('CODE_THEME', 'light');
  return (
    <div>
      <Typography.Title heading={5} style={{marginBottom: 0}}>
        <Tag color='blue'>
          {t[`objective.type.${statement.type}`]}
        </Tag>
        {statement.name}
      </Typography.Title>
      <ProblemContent problem={problem} statement={statement} />
      <Form.Item field={`problem-${statement.problemId}`}>
        <Editor
          height={350}
          language={languageNameToMonacoLanguage[language]}
          options={{
            automaticLayout: true,
            fontSize: 16
          }}
          theme={theme}
        />
      </Form.Item>
    </div>
  );
}

function AnswerHistory({problemset, answers}: {problemset:any, answers: any[]}) {
  const router = useRouter();
  function onStart() {
    createProblemsetAnswer(problemset.id).then(res => {
      router.reload();
    });
  }
  return (
    <Card>
      <Empty
        description={<Button type='primary' onClick={onStart}>开始作答</Button>}
      />
      <List
        size='small'
        header={<div>答题历史记录</div>}
        dataSource={answers}
        render={(item, index) => (
          <List.Item key={index} extra={<div><Link href={`/problemsets/${problemset.id}/answer/${item.id}`}>查看</Link></div>}>
            <Space>
              <span>正确回答：{item.correctCount}</span>
              <span>错误回答：{item.wrongCount}</span>
              <span>未回答：{item.unansweredCount}</span>
              <span>开始时间：{FormatTime(item.createdAt)}</span>
              <span>提交时间：{FormatTime(item.submittedAt)}</span>
            </Space>
          </List.Item>
        )}
      />
    </Card>
  );
}

function Page({problemset}: {problemset:any}) {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [problems, setProblems] = useState([]);
  const [form] = Form.useForm();
  const [answers, setAnswers] = useState({});
  const [answersList, setAnswersList] = useState([]);
  const [unsubmitAnswerId, setUnsubmitAnswerId] = useState(0);
  useEffect(() => {
    listProblemsetProblems(id, {perPage: 100}).then(res => setProblems(res.data.data));
    listProblemsetAnswers(problemset.id, {}).then(res => {
      const {data} = res.data;
      data.forEach((item:any) => {
        if (!item.submittedAt) {
          setUnsubmitAnswerId(item.id);
          return;
        }
      });
      setAnswersList(res.data.data);
    });
  }, []);
  useEffect(() => {
    if (unsubmitAnswerId) {
      getProblemsetAnswer(problemset.id, unsubmitAnswerId).then(res => {
        if (res.data.answer !== '') {
          const ans = JSON.parse(res.data.answer);
          setAnswers(ans);
          form.setFieldsValue(ans);
        }
      });
    }
  }, [unsubmitAnswerId]);
  function onFormChange(_, v) {
    Object.keys(v).forEach((key) => {
      if (!Array.isArray(v[key])) {
        v[key] = [v[key]];
      }
    });
    updateProblemsetAnswer(problemset.id, unsubmitAnswerId, {answer: JSON.stringify(v)})
      .catch((err) => {
        Message.error('保存失败');
      });
    setAnswers(v);
  }
  return (
    <>
      <div className='container'>
        <div>
          <div className={styles['header']}>
            <div>
              <Typography.Title>
                {problemset.name}
              </Typography.Title>
            </div>
            <div>{problemset.description}</div>
          </div>
          <Divider />
          {unsubmitAnswerId !== 0 ? (
            <div>
              <AnswerSheet unsubmitAnswerId={unsubmitAnswerId} problemset={problemset} answers={answers} problems={problems} />
              <Card>
                <Form form={form} onChange={onFormChange}>
                  <List
                    dataSource={problems}
                    render={(item, index) => (
                      <List.Item key={index} id={`problem-${item.problemId}`}>
                        {item.statement && (
                          item.statement.type === 'CODE' ? (
                            <RenderProgrammingItem index={index} problem={item} statement={item.statement} />
                          ) : (
                            <RenderObjectiveItem index={index} statement={item.statement} />
                          )
                        )}
                      </List.Item>
                    )}
                  />
                </Form>
              </Card>
            </div>
          ) : (
            <AnswerHistory problemset={problemset} answers={answersList} />
          )}
        </div>
      </div>
    </>
  );
}

export default Page;