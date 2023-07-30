import useLocale from '@/utils/useLocale';
import { Typography, Radio, Checkbox, List, Tag } from '@arco-design/web-react';
import React from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeHighlight from 'rehype-highlight';
import rehypeKatex from 'rehype-katex';
import remarkMath from 'remark-math';
import locale from './locale';

function RenderItem({statement}: {statement: any}) {
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
      <Typography.Paragraph>
        答案：{statement.output}
      </Typography.Paragraph>
      <Typography.Paragraph>
        答案解析：{statement.note}
      </Typography.Paragraph>
    </div>
  );
}

const ProblemsList = ({ problems }: { problems: any[]}) => {
  return (
    <List
      dataSource={problems}
      render={(item, index) => (
        <List.Item key={index}>
          {item.statement && (
            <RenderItem statement={item.statement} />
          )}
        </List.Item>
      )}
    />
  );
};

export default ProblemsList;
