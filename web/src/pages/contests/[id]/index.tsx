import { listContestEvents, listContestProblems } from '@/api/contest';
import { ProblemStatus } from '@/modules/problemsets/list/constants';
import useLocale from '@/utils/useLocale';
import {
  Divider,
  Table,
  TableColumnProps,
  Link,
  Grid,
  List,
  Avatar,
  Popover,
} from '@arco-design/web-react';
import { IconBulb, IconCalendar, IconCodeSquare, IconInfoCircle, IconTrophy, IconUser } from '@arco-design/web-react/icon';
import React, { useContext, useEffect, useRef, useState } from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeKatex from 'rehype-katex';
import remarkMath from 'remark-math';
import ContestContext from './context';
import locale from './locale';
import rehypeHighlight from 'rehype-highlight';
import RegisterContest from '@/modules/contest/RegisterContest';
import StatisticCard from '@/components/StatisticCard';
import ContestLayout from './Layout';
import styles from './style/index.module.less';
import { FormatTime } from '@/utils/format';
import ContestEventModal from '@/modules/contest/ContestEventModal';

function Info() {
  const t = useLocale(locale);
  const contest = useContext(ContestContext);
  const [loading, setLoading] = useState(false);
  const [problems, setProblems] = useState([]);
  const [events, setEvents] = useState([]);
  const contestEventModalRef = useRef(null);
  const columns: TableColumnProps[] = [
    {
      title: t['info.table.column.problem'],
      dataIndex: 'name',
      render: (col, record, index) => (
        <Link href={`/contests/${contest.id}/problem/${String.fromCharCode(65 + record.number)}`}>
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
  function onClickEvent(id) {
    contestEventModalRef.current.run(contest.id, id);
  }
  function fetchData() {
    setLoading(true);
    listContestEvents(contest.id)
      .then(res => {
        setEvents(res.data.data);
      });
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
    <div className={styles['info-container']}>
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
      <div>
        <Grid.Row gutter={48}>
          <Grid.Col span={18}>
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
          </Grid.Col>
          <Grid.Col span={6}>
            <ContestEventModal ref={contestEventModalRef} />
            <List
              hoverable
              dataSource={events}
              render={(item, index) => (
                <List.Item key={index} onClick={() => onClickEvent(item.id)}>
                  <List.Item.Meta
                    avatar={
                      item.type === 'FIRST_SOLVE' ? (
                        <Avatar shape='square' style={{backgroundColor: '#FFC72E'}}><IconBulb  /></Avatar>
                      ) : (
                        <Avatar shape='square' style={{backgroundColor: '#FFC72E'}}><IconTrophy /></Avatar>
                      )
                    }
                    title={item.user.name}
                    description={
                      <Popover
                        content={
                          <span>
                            {FormatTime(item.createdAt)}
                          </span>
                        }
                      >
                        {
                          item.type === 'FIRST_SOLVE' ? (
                            <span>拿下了题目一血</span>
                          ) : (
                            <span>比赛AK!</span>
                          )
                        }
                      </Popover>
                    }
                  />
                </List.Item>
              )}
            />
          </Grid.Col>
        </Grid.Row>
      </div>
    </div>
  );
}

Info.getLayout = ContestLayout;
export default Info;
