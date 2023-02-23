import { listContestProblems, listContestAllSubmissions, listContestUsers } from '@/api/contest';
import { Table, TableColumnProps } from '@arco-design/web-react';
import { IconCheckCircle, IconCloseCircle, IconQuestionCircle } from '@arco-design/web-react/icon';
import React, { useEffect, useState } from 'react';
import styles from './style/standings.module.less';

enum ContestType {
  ContestTypeICPC = 1,
  ContestTypeIOI = 2,
  ContestTypeOI = 3,
};

const basicColumn:TableColumnProps[] = [
  {
    title: 'Rank',
    dataIndex: 'rank',
    align: 'center',
  },
  {
    title: 'Who',
    dataIndex: 'who',
    align: 'center',
  },
  {
    title: 'Solved',
    dataIndex: 'solved',
    align: 'center',
  },
];

interface TableContentProps {
  // 排名
  rank: number;
  // 用户
  who: string;
  // 用户ID
  userId: number;
  // 解答
  solved: number;
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

const generateTableColumn = (problems, contestType) => {
  const t = [...basicColumn];
  if (contestType === ContestType.ContestTypeICPC) {
    t.push({
      title: 'Penalty',
      dataIndex: 'score',
      align: 'center',
    });
  } else if (contestType === ContestType.ContestTypeIOI) {
    t.push({
      title: 'Score',
      dataIndex: 'score',
      align: 'center',
    });
  } else if (contestType === ContestType.ContestTypeOI) {
    t.push({
      title: 'Max Score',
      dataIndex: 'maxScore',
      align: 'center',
    });
  }
  problems.forEach(problem => {
    t.push({
      title: String.fromCharCode(65 + problem.number),
      dataIndex: `problem.${problem.number}`,
      align: 'center',
      bodyCellStyle: {
        padding: '3px',
        margin: '0',
      },
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
  return t;
};

const App = ({contest}: any) => {
  const [loading, setLoading] = useState(true);
  const [pagination, setPagination] = useState({
    sizeCanChange: true,
    showTotal: true,
    total: 0,
    pageSize: 100,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const [columns, setColumns] = useState<TableColumnProps[]>([]);
  const [data, setData] = useState([]);

  const processTeamData = (users, problems, submissions) => {
    let res = {};
    // 初始化用户排名数据
    users.forEach(user => {
      res[user.userId] = {};
      res[user.userId].userId = user.userId;
      res[user.userId].who = user.nickname;
      res[user.userId].solved = 0;
      res[user.userId].problem = {};
      problems.forEach(p => {
        res[user.userId].problem[p.number] = {
          attempted: 0,
          isFirstBlood: false,
          status: 'UNSUBMIT',
          score: 0,
          maxScore: 0,
        };
      });
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
    for (let i = 0; i < arr.length; i++) {
      arr[i].rank = i + 1;
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
        setColumns(generateTableColumn(problems, contest.type));
        processTeamData(users, problems, submissions);
      })
      .finally(() => {
        setLoading(false);
      });
  }

  function onChangeTable(pagination) {
    const { current, pageSize } = pagination;
    setLoading(true);
    setData(data.slice((current - 1) * pageSize, current * pageSize));
    setPagination((pagination) => ({ ...pagination, current, pageSize }));
    setLoading(false);
  }

  useEffect(() => {
    fetchData();
  }, []);
  return (
    <div>
      <div className={styles['table-legend']}>
        <div>
          <span className={styles['legend-status'] + ' ' + styles['solved-first']}></span>
          <p className={styles['legend-label']}>最快解答</p>
        </div>
        <div>
          <span className={styles['legend-status'] + ' ' + styles['solved']}></span>
          <p className={styles['legend-label']}>正确解答</p>
        </div>
        <div>
          <span className={styles['legend-status'] + ' ' + styles['attempted']}></span>
          <p className={styles['legend-label']}>尝试解答</p>
        </div>
        <div>
          <span className={styles['legend-status'] + ' ' + styles['pending']}></span>
          <p className={styles['legend-label']}>等待测评</p>
        </div>
      </div>
      <Table
        rowKey={(r) => r.rank}
        columns={columns}
        data={data}
        border={false}
        loading={loading}
        pagination={pagination}
        onChange={onChangeTable}
      />
    </div>
  );
};

export default App;
