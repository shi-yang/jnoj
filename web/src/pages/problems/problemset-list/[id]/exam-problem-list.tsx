import useLocale from '@/utils/useLocale';
import { Typography, Radio, Checkbox, List, Tag, Space, Button, Message, Popconfirm, Popover, Link } from '@arco-design/web-react';
import React from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeHighlight from 'rehype-highlight';
import rehypeKatex from 'rehype-katex';
import remarkMath from 'remark-math';
import locale from './locale';
import { deleteProblemFromProblemset, sortProblemsetProblems } from '@/api/problemset';
import { IconDown, IconDragDot, IconUp } from '@arco-design/web-react/icon';
import ProblemContent from '@/modules/problem/ProblemContent';
import Markdown from '@/components/MarkdownView';

function RenderObjectiveItem({statement, index}: {statement: any, index: number}) {
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
        <Markdown content={`${index + 1}. ${legend}`} />
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
        答案解析：
        <div className='markdown-body'>
          <ReactMarkdown
            remarkPlugins={[remarkMath]}
            rehypePlugins={[rehypeKatex, rehypeHighlight]}
          >
            {statement.note}
          </ReactMarkdown>
        </div>
      </Typography.Paragraph>
    </div>
  );
}

function RenderProgrammingItem({statement, problem}: {statement: any, problem:any}) {
  const t = useLocale(locale);
  return (
    <div>
      <Typography.Title heading={5} style={{marginBottom: 0}}>
        <Tag color='blue'>
          {t[`objective.type.${statement.type}`]}
        </Tag>
        {statement.name}
      </Typography.Title>
      <ProblemContent problem={problem} statement={statement} />
    </div>
  );
}

const ProblemsList = ({ problemsetId, problems, fetchData }: { problemsetId: number, problems: any[], fetchData: () => void}) => {
  function removeProblem(pid) {
    deleteProblemFromProblemset(problemsetId, pid)
      .then(res => {
        Message.success('已移除');
        fetchData();
      });
  }
  function sortProblem(id, index, type) {
    if (type === 'up' && index === 0) {
      Message.error('无法移动');
      return;
    }
    if (type === 'down' && index === problems.length - 1) {
      Message.error('无法移动');
      return;
    }
    const ids = [];
    if (type === 'up') {
      // 与前一个进行交换
      ids.push({
        id: problems[index].id,
        order: problems[index].order
      }, {
        id: problems[index - 1].id,
        order: problems[index - 1].order
      });
    } else {
      // 与后一个进行交换
      ids.push({
        id: problems[index + 1].id,
        order: problems[index + 1].order
      }, {
        id: problems[index].id,
        order: problems[index].order
      });
    }
    sortProblemsetProblems(problemsetId, {ids})
      .then(res => {
        Message.success('已保存');
        fetchData();
      })
      .catch((err) => {
        Message.error('保存失败');
      });
  }
  return (
    <List
      dataSource={problems}
      render={(item, index) => (
        <List.Item key={index} extra={
          <Space>
            <Link href={`/problems/${item.problemId}/update`}>
              <Button type='text'>编辑</Button>
            </Link>
            <Popconfirm
              focusLock
              content='确定要移除吗?'
              onOk={() => removeProblem(item.order)}
            >
              <Button type='text'>移除</Button>
            </Popconfirm>
            <Popover position='right' content={
              <Space direction='vertical'>
                <Button icon={<IconUp />} disabled={index === 0} onClick={() => sortProblem(item.id, index, 'up')}>上移</Button>
                <Button icon={<IconDown />} disabled={index === problems.length - 1} onClick={() => sortProblem(item.id, index, 'down')}>下移</Button>
              </Space>
            }>
              <Button><IconDragDot /></Button>
            </Popover>
          </Space>
        }>
          {item.statement && (
            item.statement.type === 'CODE' ? (
              <RenderProgrammingItem problem={item} statement={item.statement} />
            ) : (
              <RenderObjectiveItem statement={item.statement} index={index} />
            )
          )}
        </List.Item>
      )}
    />
  );
};

export default ProblemsList;
