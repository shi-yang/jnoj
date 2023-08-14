import useLocale from '@/utils/useLocale';
import { Typography, Radio, Checkbox, List, Tag, Space, Divider, Link } from '@arco-design/web-react';
import React from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeHighlight from 'rehype-highlight';
import rehypeKatex from 'rehype-katex';
import remarkMath from 'remark-math';
import locale from './locale';
import ProblemContent from '../problem/ProblemContent';
import Highlight from '@/components/Highlight';
import SubmissionVerdict from '../submission/SubmissionVerdict';
import SubmissionDrawer from '../submission/SubmissionDrawer';
import Markdown from '@/components/MarkdownView';

function RenderObjectiveItem({statement, answer, index, problem}: {statement: any, answer?:any, index: number, problem:any}) {
  const t = useLocale(locale);
  let choices = [];
  if (statement.input !== '' && (statement.type === 'CHOICE' || statement.type === 'MULTIPLE')) {
    choices = JSON.parse(statement.input);
  }
  let correctAnswers = [];
  if (statement.output !== '') {
    correctAnswers = JSON.parse(statement.output);
  }
  return (
    <div>
      <Space>
        <Tag color='blue'>
          {t[`objective.type.${statement.type}`]}
        </Tag>
        <Tag color='green'>
          分数：{problem.score}
        </Tag>
      </Space>
      <Typography.Paragraph>
        {index + 1}.
        <Markdown
          content={statement.legend}
        />
      </Typography.Paragraph>
      <Typography.Paragraph>
        {(statement.type == 'CHOICE') && (
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
          }>
          </Radio.Group>
        )}
        {(statement.type == 'MULTIPLE') && (
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
          }>
          </Checkbox.Group>
        )}
      </Typography.Paragraph>
      {answer && answer !== '' && (
        <Typography.Paragraph>
        你的回答：
          <Space split={<Divider type='vertical' />}>
            {answer.map((item, index) => (
              <Tag key={index}>
                <div key={index} className='markdown-body markdown-choice'>
                  <ReactMarkdown
                    remarkPlugins={[remarkMath]}
                    rehypePlugins={[rehypeKatex, rehypeHighlight]}
                  >
                    {item}
                  </ReactMarkdown>
                </div>
              </Tag>)
            )}
          </Space>
        </Typography.Paragraph>
      )}
      {statement.output && statement.output !== '' && (
        <Typography.Paragraph>
          答案：
          <Space>
            {correctAnswers.map((item, index) => (
              <div key={index} className='markdown-body markdown-choice'>
                <ReactMarkdown
                  remarkPlugins={[remarkMath]}
                  rehypePlugins={[rehypeKatex, rehypeHighlight]}
                >
                  {item}
                </ReactMarkdown>
              </div>
            ))}
          </Space>
        </Typography.Paragraph>
      )}
      <Typography.Paragraph>
        答案解析：
        <ReactMarkdown
          remarkPlugins={[remarkMath]}
          rehypePlugins={[rehypeKatex, rehypeHighlight]}
        >
          {statement.note}
        </ReactMarkdown>
      </Typography.Paragraph>
    </div>
  );
}

function RenderProgrammingItem({statement, answer, problem, submission, index}: {statement: any, answer?:any, problem:any, submission?:any, index:number}) {
  const t = useLocale(locale);
  const [visible, setVisible] = React.useState(false);
  return (
    <div>
      <Typography.Title heading={6} style={{marginBottom: 0}}>
        <Tag color='blue'>
          {t[`objective.type.${statement.type}`]}
        </Tag>
        {index+1}. {statement.name}
      </Typography.Title>
      <ProblemContent problem={problem} statement={statement} />
      {answer && Array.isArray(answer) && answer.length > 0  && (
        <>
          <Typography.Paragraph>
            你的回答
          </Typography.Paragraph>
          <Highlight content={answer[1]} language={answer[0]} />
          <Space split={<Divider type='vertical' />}>
            <span>测评：{submission.id}</span>
            <span><SubmissionVerdict verdict={submission.verdict} /></span>
            <span>得分：{submission.score}</span>
            <Link onClick={() => setVisible(true)}>查看</Link>
            <SubmissionDrawer visible={visible} id={submission.id} onCancel={() => setVisible(false)} />
          </Space>
        </>
      )}
    </div>
  );
}

const ProblemsList = ({ problems, answer, submissions }: { problems: any[], answer?: any, submissions?:any}) => {
  return (
    <List
      dataSource={problems}
      render={(item, index) => (
        <List.Item key={index} id={`problem-${item.problemId}`}>
          {item.statement && (
            item.statement.type === 'CODE' ? (
              <RenderProgrammingItem
                statement={item.statement}
                answer={answer && answer[`problem-${item.problemId}`]}
                index={index}
                submission={submissions && submissions[item.problemId]}
                problem={item}
              />
            ) : (
              <RenderObjectiveItem
                statement={item.statement}
                answer={answer && answer[`problem-${item.problemId}`]}
                index={index}
                problem={item}
              />
            )
          )}
        </List.Item>
      )}
    />
  );
};

export default ProblemsList;
