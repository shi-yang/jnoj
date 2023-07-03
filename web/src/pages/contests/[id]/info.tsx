import { listContestProblems } from '@/api/contest';
import { ProblemStatus } from '@/modules/problemsets/list/constants';
import useLocale from '@/utils/useLocale';
import {
  Divider,
  Table,
  TableColumnProps,
  Link,
} from '@arco-design/web-react';
import { IconCalendar, IconCodeSquare, IconInfoCircle, IconUser } from '@arco-design/web-react/icon';
import React, { useContext, useEffect, useState } from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeKatex from 'rehype-katex';
import remarkMath from 'remark-math';
import ContestContext from './context';
import locale from './locale';
import rehypeHighlight from 'rehype-highlight';
import RegisterContest from '@/modules/contest/RegisterContest';
import StatisticCard from '@/components/StatisticCard';

function Info() {
  const t = useLocale(locale);
  const contest = useContext(ContestContext);
  const [loading, setLoading] = useState(false);
  const [problems, setProblems] = useState([]);
  const columns: TableColumnProps[] = [
    {
      title: t['info.table.column.problem'],
      dataIndex: 'name',
      render: (col, record, index) => (
        <Link onClick={(e) => contest.changeProblem(String.fromCharCode(65 + record.number))}>
          {String.fromCharCode(65 + record.number)}. {record.name}
        </Link>
      ),
    },
    {
      title: t['info.table.column.acceptedSubmitted'],
      dataIndex: 'accpeted',
      align: 'center',
      render: (col, record, index) => (
        <>
          {record.acceptedCount} / {record.submitCount}
        </>
      ),
    },
    {
      title: t['info.table.column.isSolved'],
      dataIndex: 'status',
      align: 'center',
      render: (col) => ProblemStatus[col],
    },
  ];
  function fetchData() {
    setLoading(true);
    listContestProblems(contest.id)
      .then((res) => {
        setProblems(res.data.data);
      })
      .finally(() => {
        setLoading(false);
      });
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <div>
      <StatisticCard items={[
        {
          icon: <IconCalendar fontSize={25} />,
          title: t['info.stat.type'],
          count: contest.type,
          loading: loading,
        },
        {
          icon: <IconInfoCircle fontSize={25} />,
          title: t['info.stat.runningStatus'],
          count: t[contest.runningStatus],
          loading: loading,
        },
        {
          icon: <IconCodeSquare fontSize={25} />,
          title: t['info.stat.problem'],
          count: problems.length,
          loading: loading,
        },
        {
          icon: <IconUser fontSize={25} />,
          title: t['info.stat.user'],
          count: contest.participantCount,
          loading: loading,
        }
      ]} />
      <Divider />
      <div style={{ maxWidth: '1200px', margin: '0 auto'}}>
        <Table rowKey={r => r.number} columns={columns} data={problems} pagination={false} />
        <ReactMarkdown
          remarkPlugins={[remarkMath]}
          rehypePlugins={[rehypeKatex, rehypeHighlight]}
        >
          {contest.description}
        </ReactMarkdown>
        <Divider />
        {
          contest.role === 'ROLE_GUEST' && contest.runningStatus === 'FINISHED' && (
            <p>比赛已结束，您可选择 <RegisterContest contest={contest} /></p>
          )
        }
      </div>
    </div>
  );
}

export default Info;
