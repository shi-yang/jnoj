import React, { useEffect, useState } from 'react';
import { Link, Card, List } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { listProblemsets } from '@/api/problemset';
import styles from './style/index.module.less';
import { isLogged } from '@/utils/auth';
export default function Sidebar() {
  const t = useLocale(locale);
  const [problemSets, setProblemSets] = useState([]);
  const [isMounted, setIsMounted] = useState(false);
  useEffect(() => {
    setIsMounted(true);
    fetchData();
  }, []);
  function fetchData() {
    const params = {
      page: 1,
      perPage: 11,
      type: 'SIMPLE',
      my: true,      
    };
    listProblemsets(params)
      .then((res) => {
        const data = res.data.data;
        setProblemSets(data);
      });
  }

  return isMounted && isLogged() && (
    <div className={styles['sidebar']}>
      <Card
        title='我的题单'
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
    </div>
  );
}

