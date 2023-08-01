import useLocale from '@/utils/useLocale';
import { Typography, Radio, Checkbox, List, Tag, Space, Divider } from '@arco-design/web-react';
import React from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeHighlight from 'rehype-highlight';
import rehypeKatex from 'rehype-katex';
import remarkMath from 'remark-math';
import locale from './locale';
import ProblemContent from '../problem/ProblemContent';

function RenderObjectiveItem({statement, answer, index}: {statement: any, answer?:any, index:number}) {
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
          {legend}
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

function RenderProgrammingItem({statement, answer, index, problem}: {statement: any, answer?:any, index:number, problem:any}) {
  const t = useLocale(locale);
  return (
    <div>
      <Typography.Title heading={5} style={{marginBottom: 0}}>
        <Tag color='blue'>
          {t[`objective.type.${statement.type}`]}
        </Tag>
        {statement.title}
      </Typography.Title>
      <ProblemContent problem={problem} statement={statement} />
    </div>
  );
}

const ProblemsList = ({ problems, answer }: { problems: any[], answer?: any}) => {
  return (
    <List
      dataSource={problems}
      render={(item, index) => (
        <List.Item key={index} id={`problem-${item.problemId}`}>
          {item.statement && (
            item.statement.type === 'CODE' ? (
              <RenderProgrammingItem statement={item.statement} answer={answer} index={index} problem={item} />
            ) : (
              <RenderObjectiveItem statement={item.statement} answer={answer} index={index} />
            )
          )}
        </List.Item>
      )}
    />
  );
};

export default ProblemsList;
