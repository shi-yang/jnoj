import { useEffect, useState } from 'react';
import { Card, Link, List } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { listProblemsets } from '@/api/problemset';

function App() {
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
    <Card
      title={t['problemset.title']}
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
  );
}

export default App;
