import { listProblemRankings } from '@/api/ranking';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import user from '@/store/reducers/user';
import { Card, Link, PaginationProps, Radio, Table, TableColumnProps } from '@arco-design/web-react'
import Head from 'next/head'
import { useEffect, useState } from 'react';
const columns: TableColumnProps[] = [
  {
    title: '排名',
    dataIndex: 'rank',
  },
  {
    title: '用户',
    dataIndex: 'user',
    render: (col, record) => <Link href={`/u/${record.id}`}>{record.nickname}</Link>
  },
  {
    title: '解答数',
    dataIndex: 'solved',
  },
];
export default () => {
  const settings = useAppSelector<SettingState>(setting);
  const [data, setData] = useState([]);
  useEffect(() => {
    listProblemRankings().then(res => {
      setData(res.data.data);
    })
  }, [])
  return (
    <>
      <Head>
        <title>{`${settings.name}`}</title>
      </Head>
      <div className='container' style={{padding: '20px'}}>
        <Card>
        <Radio.Group
          style={{
            marginBottom: 20,
          }}
          defaultValue={'全部时间'}
          type='button'
          options={['全部时间', '昨天', '近七天', '近一个月', '近一年']}
        />
          <Table columns={columns} data={data} />
        </Card>
      </div>
    </>
  );
}
