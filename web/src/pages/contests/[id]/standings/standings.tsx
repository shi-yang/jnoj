import { listContestProblems, listContestSubmissions, getContestStanding } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import { Avatar, Divider, Link, List, Modal, PaginationProps, Space, Switch, Table, TableColumnProps, Tooltip, Typography } from '@arco-design/web-react';
import { IconCheck, IconCheckCircle, IconClockCircle, IconClose, IconCloseCircle, IconQuestionCircle } from '@arco-design/web-react/icon';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from '../context';
import locale from '../locale';
import styles from '../style/standings.module.less';
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
      <>
        <Link href={`/u/${record.userId}`} target='_blank'>
          {
            record.userAvatar !== '' && (
              <Avatar size={18}>
                <img src={record.userAvatar} alt='user avatar' />
              </Avatar>
            )
          } {record.who}
        </Link>
        {record.isVirtual && <sup>虚拟</sup>}
      </>
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

const getTableCellContent = (contestType, col) => {
  if (contestType === ContestType.ContestTypeICPC) {
    return (
      <>
        <strong>{col.isInComp ? col.solvedAt : '-' }</strong>
        <br />
        <small>{col.attempted} {col.attempted == 1 ? ' try' : ' tries'}</small>
      </>
    );
  } else if (contestType === ContestType.ContestTypeIOI) {
    return (
      <>
        <strong>{col.score}</strong>
        <br />
        <small>{col.solvedAt}</small>
      </>
    );
  } else if (contestType === ContestType.ContestTypeOI) {
    return (
      <>
        <strong>{col.score}</strong>
        <br />
        <small>{col.maxScore}</small>
      </>
    );
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
      title: t['standings.table.score'],
      dataIndex: 'score',
      align: 'center',
    });
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
          {col && col.status !== 'UNSUBMIT' && (
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
                {getTableCellContent(contestType, col)}
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
  const [showMatchUsersOnly, setShowMatchUsersOnly] = useState(true);
  const [showVirtualUser, setShowVirtualUser] = useState(true);
  const [autoRefreshStanding, setAutoRefreshStanding] = useState(false);

  function fetchData() {
    setLoading(true);
    const p1 = getContestStanding(contest.id, {
      page: pagination.current,
      perPage: pagination.pageSize,
      isVirtualIncluded: showVirtualUser,
      isOfficial: showMatchUsersOnly,
    });
    const p2 = listContestProblems(contest.id);
    Promise.all([p1, p2])
      .then((values) => {
        const standing = values[0].data;
        const problems = values[1].data.data;
        setColumns(generateTableColumn(problems, contest.type, t));
        setPagination({ ...pagination, total: standing.total });
        setData(standing.data);
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

  useEffect(() => {
    fetchData();
  }, [showMatchUsersOnly, showVirtualUser, pagination.current, pagination.pageSize]);

  let timer = null;
  useEffect(() => {
    if (!autoRefreshStanding) {
      timer && clearInterval(timer);
      return;
    }
    timer = setInterval(() => {
      fetchData();
    }, 30000);
    return () => {
      timer && clearInterval(timer);
    };
  }, [autoRefreshStanding]);
  return (
    <div style={{overflow: 'hidden'}}>
      <div className={styles['table-header']}>
        <div className={styles['table-operation']}>
          {
            contest.runningStatus === 'FINISHED' && (
              <Space>
                <Typography.Text>
                  <Switch defaultChecked checkedIcon={<IconCheck />} uncheckedIcon={<IconClose />} onChange={(e) => setShowMatchUsersOnly(e)} /> 比赛期间榜单
                </Typography.Text>
                <Typography.Text>
                  <Switch defaultChecked checkedIcon={<IconCheck />} uncheckedIcon={<IconClose />} onChange={(e) => setShowVirtualUser(e)} /> 含虚拟参赛
                </Typography.Text>
              </Space>
            )
          }
          {
            contest.runningStatus === 'IN_PROGRESS' && contest.role === 'ROLE_ADMIN' &&
            <span>
              <Tooltip content='勾选将每30s自动更新榜单'>
                <Typography.Text>
                  <Switch checkedIcon={<IconCheck />} uncheckedIcon={<IconClose />} onChange={(e) => setAutoRefreshStanding(e)} /> 自动刷新
                </Typography.Text>
              </Tooltip>
            </span>
          }
        </div>
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
