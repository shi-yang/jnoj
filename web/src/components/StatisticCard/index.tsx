import { Grid, Skeleton } from '@arco-design/web-react';
import React, { ReactNode } from 'react';
import styles from './style/index.module.less';
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

function StatisticCard({items}: {items: StatisticItemType[]}) {
  return (
    <Row>
      {items.map((item, index) => (
        <Col key={index} flex={1} style={{display: 'flex', justifyContent: 'center'}}>
          <StatisticItem
            icon={item.icon}
            title={item.title}
            count={item.count}
            loading={item.loading}
          />
        </Col>
      ))}
    </Row>
  );
}

export default StatisticCard;
