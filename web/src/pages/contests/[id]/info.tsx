import { listContestProblems } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import {
  Grid,
  Divider,
  Skeleton,
  Table,
  TableColumnProps,
} from '@arco-design/web-react';
import { IconCalendar, IconCheckCircle, IconUser } from '@arco-design/web-react/icon';
import { ReactNode, useEffect, useState } from 'react';
import locale from './locale';
import styles from './style/info.module.less';
const { Row, Col } = Grid;

type StatisticItemType = {
  icon?: ReactNode;
  title?: ReactNode;
  count?: ReactNode;
  loading?: boolean;
  unit?: ReactNode;
};

function StatisticItem(props: StatisticItemType) {
  const { icon, title, count, loading, unit } = props;
  return (
    <div className={styles.item}>
      <div className={styles.icon}>{icon}</div>
      <div>
        <Skeleton loading={loading} text={{ rows: 2, width: 60 }} animation>
          <div className={styles.title}>{title}</div>
          <div className={styles.count}>
            {count}
            <span className={styles.unit}>{unit}</span>
          </div>
        </Skeleton>
      </div>
    </div>
  );
}

function Info({contest}) {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [problems, setProblems] = useState([])
  const columns: TableColumnProps[] = [
    {
      title: t['info.table.column.problem'],
      dataIndex: 'name',
      render: (col, record, index) => (
        <>
          {String.fromCharCode(65 + record.number)}. {record.name}
        </>
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
      dataIndex: 'solved',
      align: 'center',
      render: (col, record, index) => (
        <>
          {record.is_solved && (<IconCheckCircle />)}
        </>
      ),
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
      <Row>
        <Col flex={1}>
          <StatisticItem
            icon={<IconCalendar />}
            title='题目数量'
            count={problems.length}
            loading={loading}
          />
        </Col>
        <Divider type="vertical" className={styles.divider} />
        <Col flex={1}>
          <StatisticItem
            icon={<IconUser />}
            title='参赛用户'
            count={contest.participantCount}
            loading={loading}
          />
        </Col>
        <Divider type="vertical" className={styles.divider} />
        <Col flex={1}>
          <StatisticItem
            icon={<IconCalendar />}
            title='提交数量'
            count={123}
            loading={loading}
          />
        </Col>
      </Row>
      <Divider />
      <div style={{ maxWidth: '1200px', margin: '0 auto'}}>
        <Table rowKey={r => r.number} columns={columns} data={problems} pagination={false} />
      </div>
    </div>
  )
}

export default Info;
