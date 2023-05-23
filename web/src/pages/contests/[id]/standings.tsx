import { listContestProblems, listContestAllSubmissions, listContestUsers, listContestSubmissions } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import { Badge, Button, Divider, Link, List, Modal, PaginationProps, Table, TableColumnProps } from '@arco-design/web-react';
import { IconCheckCircle, IconClockCircle, IconCloseCircle, IconQuestionCircle } from '@arco-design/web-react/icon';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from './context';
import locale from './locale';
import styles from './style/standings.module.less';
import { FormatTime } from '@/utils/format';
import SubmissionVerdict from '@/modules/submission/SubmissionVerdict';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';

enum ContestType {
  ContestTypeICPC = 'ICPC',
  ContestTypeIOI = 'IOI',
  ContestTypeOI = 'OI',
};

interface TableContentProps {
  // 排名
  rank: number;
  // 用户
  who: string;
  // 用户ID
  userId: number;
  // 解答
  solved: number;
  // 是否参与排名。只有正式选手才参与排名
  isRank?: boolean;
  // 是否虚拟参赛。
  isVirtual?: boolean;
  // 分数 OI、IOI模式
  // ICPC, score = 罚时
  // OI, score = 最后一次提交
  // IOI, score = 最大分数
  score?: number;
  // 最大分数 OI 模式
  maxScore?: number;
  // 题目
  problem?: {
    [key: string]: any
  };
}

const basicColumn = (t: any):TableColumnProps[] => [
  {
    title: t['standings.table.rank'],
    dataIndex: 'rank',
    align: 'center',
    render: (col, record) => record.isRank ? col : '-'
  },
  {
    title: t['standings.table.who'],
    dataIndex: 'who',
    align: 'center',
    render: (_, record) => (
      <Link href={`/u/${record.userId}`} target='_blank'>
        {record.who}{record.isVirtual && <sup>虚拟</sup>}
      </Link>
    )
  },
  {
    title: t['standings.table.solved'],
    dataIndex: 'solved',
    align: 'center',
  },
];

const getCellColorClassName = (col) => {
  if (col.status === 'CORRECT') {
    if (col.isFirstBlood) {
      return styles['solved-first'];
    }
    return styles['solved'];
  } else if (col.status === 'PENDING') {
    return styles['pending'];
  } else if (col.status === 'INCORRECT') {
    return styles['attempted'];
  }
  return '';
};

const generateTableColumn = (problems, contestType, t) => {
  const columns = [...basicColumn(t)];
  if (contestType === ContestType.ContestTypeICPC) {
    columns.push({
      title: t['standings.table.penalty'],
      dataIndex: 'score',
      align: 'center',
    });
  } else if (contestType === ContestType.ContestTypeIOI) {
    columns.push({
      title: t['standings.table.score'],
      dataIndex: 'score',
      align: 'center',
    });
  } else if (contestType === ContestType.ContestTypeOI) {
    columns.push({
      title: t['standings.table.maxScore'],
      dataIndex: 'maxScore',
      align: 'center',
    });
  }
  problems.forEach(problem => {
    columns.push({
      title: String.fromCharCode(65 + problem.number),
      dataIndex: `problem.${problem.number}`,
      align: 'center',
      bodyCellStyle: {
        padding: '3px',
        margin: '0',
      },
      editable: true,
      render: (col, record) => (
        <>
          {col.status !== 'UNSUBMIT' && (
            <div className={styles['table-cell'] + ' ' + getCellColorClassName(col)}>
              {col.status === 'CORRECT' && (
                <IconCheckCircle className={styles['table-cell-icon']} />
              )}
              {col.status === 'PENDING' && (
                <IconQuestionCircle className={styles['table-cell-icon']} />
              )}
              {col.status === 'INCORRECT' && (
                <IconCloseCircle className={styles['table-cell-icon']} />
              )}
              <span className={styles['table-cell-text']}>
                <strong>{col.first}</strong>
                <br />
                <small>{col.second}</small>
              </span>
            </div>
          )}
        </>
      ),
    });
  });
  return columns;
};

function TableCell(props: any) {
  const { children, rowData, column, contestId } = props;
  const [visible, setVisible] = useState(false);
  const [modal, contextHolder] = Modal.useModal();
  useEffect(() => {
    if (visible) {
      // 排行榜中点击表格项，展示对应的提交记录列表
      const problemNumber = column.key.split('.')[1];
      listContestSubmissions(contestId, {userId: rowData.userId, problem: problemNumber}).then(res => {
        const submissions = res.data.data;
        modal.info({
          icon: null,
          footer: null,
          closable: true,
          content: (
            <>
              <List
                size='small'
                dataSource={submissions}
                render={(item, index) =>
                  <List.Item key={index}>
                    <IconClockCircle /> {FormatTime(item.createdAt)}
                    <Divider type='vertical' />
                    <SubmissionVerdict verdict={item.verdict} />
                    →
                    <Link href={`/submissions/${item.id}`} target='_blank'>
                      {item.id}
                    </Link>
                  </List.Item>
                }
              />
            </>
          ),
          onCancel() {
            setVisible(false);
          },
        });
      });
    }
  }, [visible]);
  if (!column.editable) {
    return children;
  }
  return (
    <div onClick={() => {setVisible(true);}} style={{cursor: 'pointer'}}>
      {contextHolder}
      {children}
    </div>
  );
}

const App = () => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(true);
  const [pagination, setPagination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    total: 0,
    pageSize: 100,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [100, 200, 500, 1000]
  });
  const [columns, setColumns] = useState<TableColumnProps[]>([]);
  const [data, setData] = useState([]);
  const user = useAppSelector(userInfo);
  const contest = useContext(ContestContext);

  const processTeamData = (users, problems, submissions) => {
    let res = {};
    // 初始化用户排名数据
    users.forEach(user => {
      const initUser = {
        userId: user.userId,
        who: user.name,
        solved: 0,
        problem: {},
        isRank: user.role === 'ROLE_OFFICIAL_PLAYER',
        isVirtual: !!user.virtualStart,
      };
      problems.forEach(p => {
        initUser.problem[p.number] = {
          attempted: 0,
          isFirstBlood: false,
          status: 'UNSUBMIT',
          score: 0,
          maxScore: 0,
        };
      });
      res[user.userId] = initUser;
    });
    // 记录一血
    let firstBlood = {};
    submissions.forEach(submission => {
      const problemNumber = submission.problem;
      const uid = submission.userId;
      // 已经通过，则直接跳过
      if (res[uid].problem[problemNumber].status === 'CORRECT') {
        return;
      }
      res[uid].problem[problemNumber].attempted++;
      if (submission.status === 'CORRECT') {
        if (!firstBlood[problemNumber]) {
          firstBlood[problemNumber] = uid;
          res[uid].problem[problemNumber].isFirstBlood = true;
        }
        res[uid].solved++;
      }
      // 分数
      if (contest.type === ContestType.ContestTypeICPC) {
        // ICPC 尝试次数会有20分罚时，加上本题通过时间，即为分数
        if (submission.status === 'CORRECT') {
          res[uid].problem[problemNumber].score = (res[uid].problem[problemNumber].attempted - 1) * 20 + submission.score;
        }
      } else if (contest.type === ContestType.ContestTypeIOI) {
        // IOI 取最大
        res[uid].problem[problemNumber].score =
          Math.max(res[uid].problem[problemNumber].score, submission.score);
      } else if (contest.type === ContestType.ContestTypeOI) {
        // OI 取最后一次
        res[uid].problem[problemNumber].score = submission.score;
        res[uid].problem[problemNumber].maxScore =
          Math.max(res[uid].problem[problemNumber].maxScore, submission.score);
      }
      res[uid].problem[problemNumber].status = submission.status;
    });
    const arr:TableContentProps[] = [];
    Object.keys(res).forEach((key, index) => {
      const item:TableContentProps = {
        rank: index,
        userId: res[key].userId,
        who: res[key].who,
        solved: res[key].solved,
        score: 0,
        maxScore: 0,
        problem: {},
        isRank: res[key].isRank,
        isVirtual: res[key].isVirtual,
      };
      const problems = res[key].problem;
      // 计算所得总分
      let score = 0;
      let maxScore = 0;
      Object.keys(problems).forEach(k => {
        item.problem[k] = problems[k];
        score += problems[k].score;
        maxScore += problems[k].maxScore;
        // 不同榜单，显示不同内容
        if (contest.type === ContestType.ContestTypeICPC) {
          item.problem[k].first = problems[k].score;
          item.problem[k].second = problems[k].attempted + (problems[k].attempted == 1 ? ' try' : ' tries');
        } else if (contest.type === ContestType.ContestTypeIOI) {
          item.problem[k].first = problems[k].score;
          item.problem[k].second = problems[k].attempted + (problems[k].attempted == 1 ? ' try' : ' tries');
        } else {
          item.problem[k].first = problems[k].score;
          item.problem[k].second = problems[k].maxScore;
        }
      });
      item.maxScore = maxScore;
      item.score = score;
      arr.push(item);
    });
    arr.sort((a, b) => {
      if (contest.type === ContestType.ContestTypeICPC) {
        if (a.solved !== b.solved) {
          return b.solved - a.solved;
        }
        return a.score - b.score;
      } else if (contest.type === ContestType.ContestTypeIOI) {
        return b.score - a.score;
      } else {
        if (a.score !== b.score) {
          return b.score - a.score;
        }
        return b.maxScore - a.maxScore;
      }
    });
    let rank = 1;
    for (let i = 0; i < arr.length; i++) {
      if (arr[i].isRank) {
        arr[i].rank = rank;
        rank++;
      }
    }
    setPagination({ ...pagination, total: arr.length });
    setData(arr);
  };

  function fetchData() {
    setLoading(true);
    const p1 = listContestAllSubmissions(contest.id);
    const p2 = listContestProblems(contest.id);
    const p3 = listContestUsers(contest.id);
    Promise.all([p1, p2, p3])
      .then((values) => {
        const problems = values[1].data.data;
        const users = values[2].data.data;
        const submissions = values[0].data.data;
        setColumns(generateTableColumn(problems, contest.type, t));
        processTeamData(users, problems, submissions);
      })
      .finally(() => {
        setLoading(false);
      });
  }

  function onChangeTable(pagination) {
    const { current, pageSize } = pagination;
    setLoading(true);
    setPagination((pagination) => ({ ...pagination, current, pageSize }));
    setLoading(false);
  }

  useEffect(() => {
    fetchData();
  }, []);
  return (
    <div style={{overflow: 'hidden'}}>
      <div className={styles['table-legend']}>
        <div>
          <span className={styles['legend-status'] + ' ' + styles['solved-first']}></span>
          <p className={styles['legend-label']}>{t['standings.solvedFirst']}</p>
        </div>
        <div>
          <span className={styles['legend-status'] + ' ' + styles['solved']}></span>
          <p className={styles['legend-label']}>{t['standings.solved']}</p>
        </div>
        <div>
          <span className={styles['legend-status'] + ' ' + styles['attempted']}></span>
          <p className={styles['legend-label']}>{t['standings.attempted']}</p>
        </div>
        <div>
          <span className={styles['legend-status'] + ' ' + styles['pending']}></span>
          <p className={styles['legend-label']}>{t['standings.pending']}</p>
        </div>
      </div>
      <Table
        rowKey={(r) => r.userId}
        components={{
          body: {
            cell: (props) => TableCell({...props, contestId: contest.id})
          }
        }}
        columns={columns}
        data={data}
        border={false}
        loading={loading}
        pagination={pagination}
        onChange={onChangeTable}
        rowClassName={(record) => (record.userId === user.id ? styles['info-row'] : '')}
      />
    </div>
  );
};

export default App;
