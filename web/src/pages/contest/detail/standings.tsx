import { listContestProblems, listContestStandings, listContestStatuses, listContestUsers } from '@/api/contest';
import { Table, TableColumnProps, Tag } from '@arco-design/web-react';
import { IconCheckCircle, IconCloseCircle, IconQuestionCircle } from '@arco-design/web-react/icon';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
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

const App = (props) => {
  const [loading, setLoading] = useState(true);
  const [standings, setStandings] = useState([]);
  const [problems, setProblems] = useState([]);
  const [statuses, setStatuses] = useState([]);
  const [users, setUsers] = useState([]);
  const [pagination, setPagination] = useState({
    sizeCanChange: true,
    showTotal: true,
    total: 0,
    pageSize: 100,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const [columns, setColumns] = useState<TableColumnProps[]>([]);
  const [data, setData] = useState([])
  const params = useParams();

  const processTeamData = (users, problems, statuses) => {
    // let res:StandingsRow[] = []
    let res = {}
    let first_blood = {}
    users.forEach(value => {
      res[value.id] = {}
      res[value.id].user_id = value.id
      res[value.id].who = value.nickname
      res[value.id].solved = 0
      res[value.id].problem = {}
      problems.forEach(p => {
        res[value.id].problem[p.key] = {
          attempted: 0,
          is_first_blood: false,
          status: 'unsubmit',
          score: 0
        }
      })
    })
    statuses.sort((a, b) => {
      return a.interval - b.interval
    })
    statuses.forEach(value => {
      if (res[value.user_id].problem[value.problem_id].status === 'correct') {
        return
      }
      if (value.status === 'correct') {
        if (!first_blood[value.problem_id]) {
          first_blood[value.problem_id] = value.user_id
          res[value.user_id].problem[value.problem_id].is_first_blood = true
        }
        res[value.user_id].solved++
      }
      res[value.user_id].problem[value.problem_id].score =
        Math.max(res[value.user_id].problem[value.problem_id].score, value.score)
      res[value.user_id].problem[value.problem_id].attempted++
      res[value.user_id].problem[value.problem_id].status = value.status
    })
    const arr = []
    Object.keys(res).forEach(key => {
      const item = {
        user_id: res[key].user_id,
        who: res[key].who,
        solved: res[key].solved,
        score: 0
      };
      const problems = res[key].problem
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
    setData(arr)
  }

  const getCellColorClassName = (col) => {
    if (col.status === 'correct') {
      if (col.is_first_blood) {
        return styles['solved-first']
      }
      return styles['solved']
    } else if (col.status === 'pending') {
      return styles['pending']
    } else if (col.status === 'incorrect') {
      return styles['attempted']
    }
    return ''
  }

  function fetchData() {
    setLoading(true);
    const p1 = listContestStandings(params.id)
    const p2 = listContestProblems(params.id)
    const p3 = listContestStatuses(params.id)
    const p4 = listContestUsers(params.id)
    Promise.all([p1, p2, p3, p4])
      .then((values) => {
        const problems = values[1].data.data;
        const t:TableColumnProps[] = [...basicColumn];
        problems.forEach(v => {
          t.push({
            title: String(v.key),
            dataIndex: `problem_${v.key}`,
            align: 'center',
            bodyCellStyle: {
              padding: '3px',
              margin: '0',
            },
            render: (col, record, index) => (
              <>
                {col.status !== 'unsubmit' && (
                  <div className={styles['table-cell'] + ' ' + getCellColorClassName(col)}>
                    {col.status === 'correct' && (
                      <IconCheckCircle className={styles['table-cell-icon']} />
                    )}
                    {col.status === 'pending' && (
                      <IconQuestionCircle className={styles['table-cell-icon']} />
                    )}
                    {col.status === 'incorrect' && (
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
        const users = values[3].data.data;
        const status = values[2].data.data;
        processTeamData(users, problems, status);
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
        pagination={pagination}
        onChange={onChangeTable}
      />
    </div>
  );
};

export default App;
