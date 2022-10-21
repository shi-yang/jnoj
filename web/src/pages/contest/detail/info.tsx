import { listContestProblems } from '@/api/contest';
import useLocale from '@/utils/useLocale';
import {
  Grid,
  Divider,
  Skeleton,
  Table,
  TableColumnProps,
} from '@arco-design/web-react';
import { IconCalendar, IconCheckCircle, IconMinusCircle } from '@arco-design/web-react/icon';
import { ReactNode, useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
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

const columns: TableColumnProps[] = [
  {
    title: 'Problem',
    dataIndex: 'name',
    render: (col, record, index) => (
      <>
        {String.fromCharCode(65 + record.key)}. {record.name}
      </>
    ),
  },
  {
    title: 'Accepted / Submitted',
    dataIndex: 'accpeted',
    align: 'center',
    render: (col, record, index) => (
      <>
        {record.accepted} / {record.attempted}
      </>
    ),
  },
  {
    title: 'Solved',
    dataIndex: 'solved',
    align: 'center',
    render: (col, record, index) => (
      <>
        {record.is_solved && (<IconCheckCircle />)}
      </>
    ),
  },
];
export default () => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [problems, setProblems] = useState([])
  const params = useParams();
  function fetchData() {
    setLoading(true);
    listContestProblems(params.id)
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
            count={123}
            loading={loading}
          />
        </Col>
        <Divider type="vertical" className={styles.divider} />
        <Col flex={1}>
          <StatisticItem
            icon={<IconCalendar />}
            title='题目数量'
            count={456}
            loading={loading}
          />
        </Col>
        <Divider type="vertical" className={styles.divider} />
        <Col flex={1}>
          <StatisticItem
            icon={<IconCalendar />}
            title='题目数量'
            count={123}
            loading={loading}
          />
        </Col>
        <Divider type="vertical" className={styles.divider} />
        <Col flex={1}>
          <StatisticItem
            icon={<IconCalendar />}
            title='题目数量'
            count={234}
            loading={loading}
          />
        </Col>
      </Row>
      <Divider />
      <div style={{ maxWidth: '1200px', margin: '0 auto'}}>
        <Table columns={columns} data={problems} pagination={false} />
      </div>
    </div>
  )
}
