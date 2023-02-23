import React, { useEffect, useState, ReactNode } from 'react';
import { Link, Card, List, Space, Typography } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { listProblemsets } from '@/api/problemset';
import { IconArrowRight, IconList } from '@arco-design/web-react/icon';
import styles from './style/index.module.less';
export default function Sidebar() {
  const t = useLocale(locale);
  const [problemSets, setProblemSets] = useState([]);
  useEffect(() => {
    fetchData();
  }, []);
  function fetchData() {
    const params = {
      page: 1,
      perPage: 5,
    };
    listProblemsets(params)
      .then((res) => {
        setProblemSets(res.data.data);
      });
  }

  return (
    <div className={styles['sidebar']}>
      <Card
        title={t['problemset.title']}
        bordered
        extra={
          <Link href='/problemsets/all'>{t['problemset.sidebar.more']}</Link>
        }
      >
        <List
          hoverable
          dataSource={problemSets}
          render={(item, index) =>
            <List.Item key={index}>
              <Link href={`/problemsets/${item.id}`}>{item.name}</Link>
            </List.Item>
          }
        />
      </Card>
      <Link href='/problems' className={styles['problem-link']}>
        <Card
          className={styles['card-with-icon-hover']}
          hoverable
          bordered
        >
          <Content>
            <span className={styles['icon-hover']}>
              <IconArrowRight
                style={{
                  cursor: 'pointer',
                }}
              />
            </span>
          </Content>
        </Card>
      </Link>
    </div>
  );
}

const Content = ({ children }:{ children: ReactNode }) => {
  const t = useLocale(locale);
  return (
    <Space
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
      }}
    >
      <Space>
        <IconList />
        <Typography.Text>{t['problemList']}</Typography.Text>
      </Space>
      {children}
    </Space>
  );
};
