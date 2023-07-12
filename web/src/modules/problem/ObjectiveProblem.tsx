import React from 'react';
import { Radio, Typography } from '@arco-design/web-react';
import styles from './style/index.module.less';
import remarkMath from 'remark-math';
import rehypeHighlight from 'rehype-highlight';
import rehypeKatex from 'rehype-katex';
import ReactMarkdown from 'react-markdown';
const { Title, Paragraph } = Typography;
export default function Page({problem, language}: any) {
  const statement = problem.statements[language];
  let choices = [];
  if (statement.type === 'CHOICE') {
    choices = statement.input.split(',');
  } else if (statement.type === 'MULTIPLE') {
    choices = statement.input.split(',');
  }
  return (
    <div>
      <Typography className={styles.content}>
        <Paragraph>
          <ReactMarkdown
            remarkPlugins={[remarkMath]}
            rehypePlugins={[rehypeKatex, rehypeHighlight]}
          >
            {statement.legend}
          </ReactMarkdown>
        </Paragraph>
        <Paragraph>
          {(statement.type == 'CHOICE' || statement.type == 'MULTIPLE') && (
            <Radio.Group direction='vertical' options={
              choices.map((item, index) => 
                ({label: item, value: index})
              )
            }>
            </Radio.Group>
          )}
        </Paragraph>
      </Typography>
    </div>
  );
};
