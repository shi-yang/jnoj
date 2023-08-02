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

function RenderObjectiveItem({statement, answer, index}: {statement: any, answer?:any, index: number}) {
  const t = useLocale(locale);
  let choices = [];
  if (statement.input !== '' && (statement.type === 'CHOICE' || statement.type === 'MULTIPLE')) {
    choices = JSON.parse(statement.input);
  }
  let legend = statement.legend;
  if (statement.type === 'FILLBLANK') {
    legend = statement.legend.replace(/{.*?}/g, '`________`');
  }
  return (
    <div>
      <Typography.Title heading={6} style={{marginBottom: 0}}>
        <Tag color='blue'>
          {t[`objective.type.${statement.type}`]}
        </Tag>
      </Typography.Title>
      <Typography.Paragraph>
        <ReactMarkdown
          remarkPlugins={[remarkMath]}
          rehypePlugins={[rehypeKatex, rehypeHighlight]}
        >
          {`${index + 1}. ` + legend}
        </ReactMarkdown>
      </Typography.Paragraph>
      <Typography.Paragraph>
        {(statement.type == 'CHOICE') && (
          <Radio.Group direction='vertical' options={
            choices.map((item, index) => 
              ({label: item, value: index})
            )
          }>
          </Radio.Group>
        )}
        {(statement.type == 'MULTIPLE') && (
          <Checkbox.Group direction='vertical' options={
            choices.map((item, index) => 
              ({label: item, value: item})
            )
          }>
          </Checkbox.Group>
        )}
      </Typography.Paragraph>
      {answer && answer !== '' && (
        <Typography.Paragraph>
        你的回答：<Space split={<Divider type='vertical' />}>{answer.map((item, index) => (<Tag key={index}>{item}</Tag>))}</Space>
        </Typography.Paragraph>
      )}
      {statement.output && statement.output !== '' && (
        <Typography.Paragraph>
        答案：<Space split={<Divider type='vertical' />}>{JSON.parse(statement.output).map((item, index) => (<Tag key={index}>{item}</Tag>))}</Space>
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
              <RenderProgrammingItem statement={item.statement} answer={answer[`problem-${item.problemId}`]} index={index} submission={submissions && submissions[item.problemId]} problem={item} />
            ) : (
              <RenderObjectiveItem statement={item.statement} answer={answer[`problem-${item.problemId}`]} index={index} />
            )
          )}
        </List.Item>
      )}
    />
  );
};

export default ProblemsList;
