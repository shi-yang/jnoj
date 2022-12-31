import { listContestProblems, listContestStandings, listContestStatuses, listContestUsers } from '@/api/contest';
import { Table, TableColumnProps, Tag } from '@arco-design/web-react';
import { IconCheckCircle, IconCloseCircle, IconQuestionCircle } from '@arco-design/web-react/icon';
import { useEffect, useState } from 'react';
import styles from './style/standings.module.less';

interface StandingsRow {
  rank: number;
  who: string;
  solved: number;
  score: number;
  [index: number]: []
}

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
  {
    title: 'Score',
    dataIndex: 'score',
    align: 'center',
  },
]

const App = ({contest}) => {
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
    // let res:StandingsRow[] = []
    // 初始化用户排名数据
    let res = {};
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
          score: 0
        }
      })
    })
    let firstBlood = {}
    submissions.forEach(submission => {
      // 已经通过，则直接跳过
      const problemNumber = submission.problemNumber;
      const uid = submission.userId;
      if (res[uid].problem[problemNumber].status === 'CORRECT') {
        return;
      }
      if (submission.status === 'CORRECT') {
        // 记录一血
        if (!firstBlood[problemNumber]) {
          firstBlood[problemNumber] = uid;
          res[uid].problem[problemNumber].isFirstBlood = true;
        }
        res[uid].solved++
      }
      res[uid].problem[problemNumber].score =
        Math.max(res[uid].problem[problemNumber].score, submission.score)
      res[uid].problem[problemNumber].attempted++
      res[uid].problem[problemNumber].status = submission.status
    })
    const arr = []
    Object.keys(res).forEach(key => {
      const item = {
        userId: res[key].userId,
        who: res[key].who,
        solved: res[key].solved,
        score: 0,
      };
      const problems = res[key].problem
      // 计算所得总分
      let score = 0
      Object.keys(problems).forEach(k => {
        item[`problem_${k}`] = problems[k]
        score += problems[k].score
      })
      item.score = score
      arr.push(item)
    })
    arr.sort((a, b) => {
      if (a.solved !== b.solved) {
        return b.solved - a.solved
      }
      return b.score - a.score
    })
    for (let i = 0; i < arr.length; i++) {
      arr[i].rank = i + 1      
    }
    setPagination({ ...pagination, total: arr.length });
    setData(arr);
  }

  const getCellColorClassName = (col) => {
    if (col.status === 'CORRECT') {
      if (col.isFirstBlood) {
        return styles['solved-first']
      }
      return styles['solved']
    } else if (col.status === 'PENDING') {
      return styles['pending']
    } else if (col.status === 'INCORRECT') {
      return styles['attempted']
    }
    return ''
  }

  const generateTableHeader = (problems) => {
    const t = [...basicColumn];
    problems.forEach(problem => {
      t.push({
        title: String.fromCharCode(65 + problem.number),
        dataIndex: `problem_${problem.number}`,
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
                  <strong>{col.attempted}</strong>
                  <br />
                  <small>{col.score}</small>
                </span>
              </div>
            )}
          </>
        ),
      });
    })
    setColumns(t)
  }

  function fetchData() {
    setLoading(true);
    const p1 = listContestStandings(contest.id)
    const p2 = listContestProblems(contest.id)
    const p3 = listContestUsers(contest.id)
    Promise.all([p1, p2, p3])
      .then((values) => {
        const problems = values[1].data.data;
        const users = values[2].data.data;
        const submissions = values[0].data.data;
        generateTableHeader(problems);
        processTeamData(users, problems, submissions);
      })
      .finally(() => {
        setLoading(false);
      })
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
