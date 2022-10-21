import useLocale from '@/utils/useLocale';
import {
  Grid,
  Divider,
  Skeleton,
  Table,
  TableColumnProps,
} from '@arco-design/web-react';
import { IconCalendar } from '@arco-design/web-react/icon';
import { ReactNode, useState } from 'react';
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
export default (props) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState({ data: [], total: 0 })
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'name',
    },
    {
      title: 'Problem Name',
      dataIndex: 'salary',
    },
    {
      title: 'Accepted / Submitted',
      dataIndex: 'address',
    },
    {
      title: 'Solved',
      dataIndex: 'solved',
    },
  ];
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
      <Table columns={columns} data={data.data} />;
    </div>
  )
}
