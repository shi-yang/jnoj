import React, { useEffect, useRef, useState } from 'react';
import { Anchor, Button, Card, Checkbox, Divider, Empty, Form, Input, Link, List, Message, PageHeader, Popconfirm, Radio, Select, Space, Tag, Tooltip, Typography } from '@arco-design/web-react';
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
import { getProblemLanguage, listProblemLanguages } from '@/api/problem-file';
import { IconSkin } from '@arco-design/web-react/icon';
import duration from 'dayjs/plugin/duration';
import dayjs from 'dayjs';
import Markdown from '@/components/MarkdownView';
dayjs.extend(duration);
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
      <Card className='container' bordered style={{padding: 0}}>
        <Anchor
          affix={false}
          lineless
          direction='horizontal'
          className={styles['arco-anchor-list']}
          style={{ backgroundColor: 'var(--color-bg-2)' }}
        >
          {problems.map((item, index) => (
            <AnchorLink
              key={index}
              className={styles['arco-anchor-item']}
              href={`#problem-${item.problemId}`}
              title={<Button status={answers[`problem-${item.problemId}`] && answers[`problem-${item.problemId}`].every(item => item) ? 'success' : 'default'}>{index + 1}</Button>}
            />
          ))}
        </Anchor>
        <Popconfirm
          focusLock
          style={{zIndex: 10000}}
          title='确认交卷'
          onOk={() => onSubmit()}
        >
          <Button type='primary'>交卷</Button>
        </Popconfirm>
      </Card>
    </div>
  );
}

function RenderObjectiveItem({index, statement, problem}: {index: number, statement: any, problem:any}) {
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
      <Space>
        <Tag color='blue'>
          {t[`objective.type.${statement.type}`]}
        </Tag>
        <Tag color='green'>
          分数 {problem.score}
        </Tag>
      </Space>
      <Typography.Paragraph>
        <Markdown content={`${index + 1}. ${legend}`} />
      </Typography.Paragraph>
      <Typography.Paragraph>
        {(statement.type == 'CHOICE') && (
          <Form.Item field={`problem-${statement.problemId}`}>
            <Radio.Group direction='vertical' options={
              choices.map((item, index) => 
                ({label: (
                  <div className='markdown-body markdown-choice'>
                    <ReactMarkdown
                      remarkPlugins={[remarkMath]}
                      rehypePlugins={[rehypeKatex, rehypeHighlight]}
                    >
                      {item}
                    </ReactMarkdown>
                  </div>
                ), value: item})
              )
            } />
          </Form.Item>
        )}
        {(statement.type == 'MULTIPLE') && (
          <Form.Item field={`problem-${statement.problemId}`}>
            <Checkbox.Group direction='vertical' options={
              choices.map((item, index) => 
                ({label: (
                  <div className='markdown-body markdown-choice'>
                    <ReactMarkdown
                      remarkPlugins={[remarkMath]}
                      rehypePlugins={[rehypeKatex, rehypeHighlight]}
                    >
                      {item}
                    </ReactMarkdown>
                  </div>
                ), value: item})
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
  const [languageId, setLanguageId] = useState(0);
  const [value, setValue] = useState('');
  const [languageList, setLanguageList] = useState([]);
  const { form, disabled, isSubmitting } = Form.useFormContext();
  useEffect(() => {
    if (problem.problemId === 0) {
      return;
    }
    const v = form.getFieldValue(`problem-${statement.problemId}`);
    if (v && v.every(item => item) && v.length >= 2) {
      setValue(v[1]);
    } else {
      getLanguages();
    }
  }, [problem.problemId]);
  const onChangeLanguage = (e) => {
    setLanguage(e);
    // 函数题需要查询对应的语言模板
    if (problem.type === 'FUNCTION') {
      const lang = languageList.find(item => item.languageCode === Number(e));
      getProblemLanguage(problem.problemId, lang.id)
        .then(res => {
          setLanguageId(lang.id);
          setValue(res.data.userContent);
        });
    }
  };
  const getLanguages = () => {
    listProblemLanguages(problem.problemId)
      .then(res => {
        const langs = res.data.data;
        setLanguageList(langs);
        const userLang = langs.find(item => item.languageCode === Number(language));
        if (problem.type === 'FUNCTION' && userLang) {
          getProblemLanguage(problem.problemId, userLang.id)
            .then(res => {
              setLanguageId(userLang.id);
              setValue(res.data.userContent);
            });
        } else {
          setValue('');
        }
      });
  };
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
        <Input hidden />
      </Form.Item>
      <div className={styles['code-header']}>
        <Select
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
      <Editor
        height={350}
        language={languageNameToMonacoLanguage[language]}
        options={{
          automaticLayout: true,
          fontSize: 16
        }}
        theme={theme}
        value={value}
        onChange={(v) => {
          form.setFieldValue(`problem-${statement.problemId}`, [language, v]);
        }}
      />
      <Button type='primary' htmlType='submit' onSubmit={() => form.submit}>保存代码</Button>
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
  function getDuration(submittedAt, createdAt) {
    return dayjs.duration(dayjs(submittedAt).diff(createdAt, 'seconds'), 'seconds').format('H[h] m[m] s[s]').replace(/\b0+[a-z]+\s*/gi, '').trim();
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
            <Typography.Title heading={4}>
              得分：{item.score}
            </Typography.Title>
            <Space direction='vertical'>
              <Space>
                <span>正确回答：{item.correctProblemIds === '' ? 0 : item.correctProblemIds.split(',').length}</span>
                <span>错误回答：{item.wrongProblemIds === '' ? 0 : item.wrongProblemIds.split(',').length}</span>
                <span>未回答：{item.unansweredProblemIds === '' ? 0 : item.unansweredProblemIds.split(',').length}</span>
              </Space>
              <Space>
                <Typography.Text type='secondary'>开始时间：{FormatTime(item.createdAt)}</Typography.Text>
                <Tooltip content={'提交时间：' + FormatTime(item.submittedAt)}>
                  <Typography.Text type='secondary'>持续时长：{getDuration(item.submittedAt, item.createdAt)}</Typography.Text>
                </Tooltip>
              </Space>
            </Space>
          </List.Item>
        )}
      />
    </Card>
  );
}

function Timer({initialTime}: {initialTime: any}) {
  const t = useLocale(locale);
  const [time, setTime] = useState(0);
  const idRef = useRef(null);
  useEffect(() => {
    setTime(dayjs(new Date()).diff(initialTime, 'seconds'));
  }, [initialTime]);
  useEffect(() => {
    idRef.current = setInterval(() => setTime(v => v + 1), 1000);
    return () => {
      clearInterval(idRef.current);
      idRef.current = null;
    };
  }, []);
  return (
    <Tooltip content={'开始时间：' + FormatTime(initialTime)}>
      <Button>
        {dayjs.duration(time, 'seconds').format('HH:mm:ss')}
      </Button>
    </Tooltip>
  );
};

function Page({problemset}: {problemset:any}) {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [problems, setProblems] = useState([]);
  const [form] = Form.useForm();
  const [answers, setAnswers] = useState({});
  const [answerCreatedAt, setAnswerCreatedAt] = useState(0);
  const [answersList, setAnswersList] = useState([]);
  const [unsubmitAnswerId, setUnsubmitAnswerId] = useState(0);
  useEffect(() => {
    listProblemsetProblems(id, {perPage: 100}).then(res => setProblems(res.data.problems));
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
        setAnswerCreatedAt(res.data.createdAt);
        if (res.data.answer !== '') {
          const ans = JSON.parse(res.data.answer);
          setAnswers(ans);
          form.setFieldsValue(ans);
        }
      });
    }
  }, [unsubmitAnswerId]);
  function onFormChange(_, v) {
    onSubmit(v);
  }
  function onSubmit(v) {
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
    <div>
      <PageHeader
        title={problemset.name}
        style={{ background: 'var(--color-bg-2)' }}
        extra={unsubmitAnswerId !== 0 && <Timer initialTime={answerCreatedAt} />}
      >
        {problemset.description}
      </PageHeader>
      <Divider />
      {unsubmitAnswerId !== 0 ? (
        <div>
          <Card style={{height: 'calc(100vh - 412px)', overflow: 'hidden'}} bodyStyle={{height: '100%', overflow: 'scroll', padding: 0}}>
            <Form form={form} onChange={onFormChange} onSubmit={onSubmit}>
              <List
                dataSource={problems}
                render={(item, index) => (
                  <List.Item key={index} id={`problem-${item.problemId}`}>
                    {item.statement && (
                      item.statement.type === 'CODE' ? (
                        <RenderProgrammingItem index={index} problem={item} statement={item.statement} />
                      ) : (
                        <RenderObjectiveItem index={index} problem={item} statement={item.statement} />
                      )
                    )}
                  </List.Item>
                )}
              />
            </Form>
          </Card>
          <AnswerSheet unsubmitAnswerId={unsubmitAnswerId} problemset={problemset} answers={answers} problems={problems} />
        </div>
      ) : (
        <AnswerHistory problemset={problemset} answers={answersList} />
      )}
    </div>
  );
}

export default Page;
