import { listProblemRankings } from '@/api/ranking';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import { isLogged } from '@/utils/auth';
import { Avatar, Card, Grid, Link, List, Radio, Table, TableColumnProps } from '@arco-design/web-react';
import Head from 'next/head';
import React, { useEffect, useState } from 'react';
const columns: TableColumnProps[] = [
  {
    title: '排名',
    dataIndex: 'rank',
  },
  {
    title: '用户',
    dataIndex: 'user',
    render: (col, record) => <Link href={`/u/${record.userId}`}>{record.nickname}</Link>,
  },
  {
    title: '解答数',
    dataIndex: 'solved',
  },
];
export default function Index() {
  const settings = useAppSelector<SettingState>(setting);
  const [data, setData] = useState([]);
  const [myRanking, setMyRanking] = useState({rank: 0, nickname: '', userId: 0, solved: 0});
  const [radioValue, setRadioValue] = useState(0);
  useEffect(() => {
    listProblemRankings({type: radioValue}).then(res => {
      setData(res.data.data);
      setMyRanking(res.data.myRanking);
    });
  }, [radioValue]);
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
            defaultValue={0}
            type='button'
            options={[
              {label: '全部时间', value: 0},
              {label: '近24小时', value: 1},
              {label: '近七天', value: 2},
              {label: '近一个月', value: 3},
              {label: '近一年', value: 4},
            ]}
            onChange={e => setRadioValue(e)}
          />
          <Grid.Row>
            <Grid.Col span={12}>
              <List
                header='前三名'
                className='container'
              >
                {
                  data.length > 0
                  && <List.Item>
                    <List.Item.Meta
                      title={<Link href={`/u/${data[0].userId}`}>{data[0].nickname}</Link>}
                      description={data[0].solved}
                      avatar={<Avatar shape='square' style={{ backgroundColor: '#FFD700' }}>1</Avatar>}
                    />
                  </List.Item>
                }
                {
                  data.length > 1
                  && <List.Item>
                    <List.Item.Meta
                      title={<Link href={`/u/${data[1].userId}`}>{data[1].nickname}</Link>}
                      description={data[1].solved}
                      avatar={<Avatar shape='square' style={{ backgroundColor: '#C0C0C0' }}>2</Avatar>}
                    />
                  </List.Item>
                }
                {
                  data.length > 2
                  && <List.Item>
                    <List.Item.Meta
                      title={<Link href={`/u/${data[2].userId}`}>{data[2].nickname}</Link>}
                      description={data[2].solved}
                      avatar={<Avatar shape='square' style={{ backgroundColor: '#B87333' }}>3</Avatar>}
                    />
                  </List.Item>
                }
              </List>
            </Grid.Col>
            <Grid.Col span={12}>
              <List header='我的排名'>
              {
                  isLogged() && myRanking &&
                  <List.Item>
                    <List.Item.Meta
                      title={<Link href={`/u/${myRanking.userId}`}>{myRanking.nickname}</Link>}
                      description={myRanking.solved}
                      avatar={<Avatar shape='square'>{myRanking.rank}</Avatar>}
                    />
                  </List.Item>
                }
              </List>
            </Grid.Col>
          </Grid.Row>
          <Table rowKey={r => r.userId} columns={columns} data={data} />
          <p>注：榜单缓存时间1小时</p>
        </Card>
      </div>
    </>
  );
}
