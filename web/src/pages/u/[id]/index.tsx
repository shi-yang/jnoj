import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import { getUserProfileCalendar, getUserProfileCount, getUserProfileProblemSolved, getUsers } from '@/api/user';
import HeatMap from '@uiw/react-heat-map';
import { Card, Collapse, Divider, Grid, Link, List, Pagination, PaginationProps, Progress, Select, Space, Statistic, Tabs, Tag, Tooltip, Typography } from '@arco-design/web-react';
import Head from 'next/head';
import { setting, SettingState } from '@/store/reducers/setting';
import { useAppSelector } from '@/hooks';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import PassValidIcon from '@/assets/icon/pass-valid.svg';
import VIPIcon from '@/assets/icon/vip.svg';
import { listSubmissions } from '@/api/submission';
import SubmissionVerdict from '@/modules/submission/SubmissionVerdict';
import { FormatTime } from '@/utils/format';
import StatisticCard from '@/components/StatisticCard';
import { IconFile, IconTrophy } from '@arco-design/web-react/icon';
import ReactECharts from 'echarts-for-react';


function RecentlySubmission({userId}: {userId: number}) {
  const [data, setData] = useState([]);
  useEffect(() => {
    const params = {
      page: 1,
      perPage: 10,
      userId: userId,
      entityType: 0,
    };
    listSubmissions(params).then(res => {
      setData(res.data.data);
    });
  }, [userId]);
  return (
    <List
      dataSource={data}
      render={(item, index) => (
        <List.Item key={index}>
          <Link href={`/submissions/${item.id}`} target='_blank' style={{display: 'block'}}>
            <div style={{display: 'flex', justifyContent: 'space-between'}}>
              {item.problemName}
              <Space >
                <SubmissionVerdict verdict={item.verdict} />
                <Divider type='vertical' />
                {FormatTime(item.createdAt)}
              </Space>
            </div>
          </Link>
        </List.Item>
      )}
    />
  );
}

function renderItemWithResponsive(item1: React.ReactNode, item2: React.ReactNode, item3: React.ReactNode) {
  return (
    <Grid.Row>
      <Grid.Col xs={24} sm={16} md={16} lg={12}>
        {item1}
      </Grid.Col>
      <Grid.Col xs={12} sm={4} md={4} lg={6}>
        {item2}
      </Grid.Col>
      <Grid.Col xs={12} sm={4} md={4} lg={6} style={{textAlign: 'center'}}>
        {item3}
      </Grid.Col>
    </Grid.Row>
  );
}

const Color = {
  'NOT_START': 'gray',
  'INCORRECT': 'orange',
  'CORRECT': 'green',
};
export default function UserPage() {
  const router = useRouter();
  const t = useLocale(locale);
  const { id } = router.query;
  const [user, setUser] = useState({username: '', nickname: '', role: ''});
  const settings = useAppSelector<SettingState>(setting);
  const [calendarOptions, setCalendarOptions] = useState([]);
  const [profileCalendar, setProfileCalendar] = useState({
    submissionCalendar: [],
    totalSubmission: 0,
    totalProblemSolved: 0,
    totalActiveDays: 0,
    start: '',
    end: '',
  });
  const [problemSolvedProgressTab, setProblemSolvedProgressTab] = useState('problemset');
  const [profileProblemsets, setProfileProblemsets] = useState([]);
  const [profileContests, setProfileContests] = useState([]);
  const [profileGroups, setProfileGroups] = useState([]);
  const [calendarSelectYear, setCalendarSelectYear] = useState(0);
  const [profileCount, setProfileCount] = useState({
    contestRating: 0,
    problemSolved: 0,
  });
  const [pagination, setPagination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100],
    hideOnSinglePage: true,
    onChange: (current, pageSize) => {
      setPagination({
        ...pagination,
        current,
        pageSize,
      });
    }
  });
  const [ratingHistory, setRatingHistory] = useState({
    grid: { top: 8, right: 8, bottom: 24, left: 36 },
    xAxis: {
      show: false,
      data: [],
    },
    yAxis: {
      type: 'value',
    },
    series: [
      {
        data: [],
        type: 'line',
        smooth: true,
      },
    ],
    tooltip: {
      trigger: 'axis',
    }
  });
  function onCalendarSelectChange(e) {
    setCalendarSelectYear(e);
    getUserProfileCalendar(id, { year: e })
      .then(res => {
        const { data } = res;
        setProfileCalendar(data);
      });
  }
  useEffect(() => {
    const { current, pageSize } = pagination;
    if (problemSolvedProgressTab === 'problemset') {
      getUserProfileProblemSolved(id, {type: 'PROBLEMSET'})
        .then(res => {
          setProfileProblemsets(res.data.problemsets);
        });
    } else if (problemSolvedProgressTab === 'contest') {
      getUserProfileProblemSolved(id, {type: 'CONTEST', page: current, perPage: pageSize})
        .then(res => {
          setProfileContests(res.data.contests);
          setPagination({
            ...pagination,
            current,
            pageSize,
            total: res.data.total,
          });
        });
    } else {
      getUserProfileProblemSolved(id, {type: 'GROUP', page: current, perPage: pageSize})
        .then(res => {
          setProfileGroups(res.data.groups);
          setPagination({
            ...pagination,
            current,
            pageSize,
            total: res.data.total,
          });
        });
    }
  }, [problemSolvedProgressTab, pagination.current, pagination.pageSize]);

  useEffect(() => {
    getUsers(id)
      .then(res => {
        setUser(res.data);
      });
    getUserProfileCalendar(id).
      then(res => {
        const { data } = res;
        setProfileCalendar(data);
        data.activeYears.forEach(item => {
          setCalendarOptions(current => [...current, {
            name: item,
            value: item
          }]);
        });
      });
    getUserProfileProblemSolved(id, {type: 'PROBLEMSET'})
      .then(res => {
        setProfileProblemsets(res.data.problemsets);
      });
    getUserProfileCount(Number(id)).then(res => {
      setProfileCount({
        contestRating: res.data.contestRating,
        problemSolved: res.data.problemSolved,
      });
      setRatingHistory(prevState => ({...prevState,
        xAxis: {
          ...prevState.xAxis,
          data: res.data.contestRankingHistory.map(item => item.name),
        },
        series: [
          {
            ...prevState.series[0],
            data: res.data.contestRankingHistory.map(item => item.rating)
          }
        ],
      }));
    });
  }, [id]);
  return (
    <>
      <Head>
        <title>{`${user.username} - ${settings.name}`}</title>
      </Head>
      <div className='container'>
        <div className={styles['header-container']}>
          <Typography.Title>
            {user.nickname} <Divider type='vertical' /><small>{user.username}</small>
          </Typography.Title>
          {
            (user.role === 'ADMIN' || user.role === 'OFFICIAL_USER' || user.role === 'SUPER_ADMIN') &&
            <Tooltip content={t['officialUser']}>
              <PassValidIcon />
            </Tooltip>
          }
          {
            user.role === 'VIP_USER' &&
            <Tooltip content={t['vipUser']}>
              <VIPIcon />
            </Tooltip>
          }
        </div>
        <Card>
          <StatisticCard
            items={[
              {
                icon: <IconFile fontSize={30} />,
                title: '解题数量',
                count: profileCount.problemSolved,
                loading: false,
              },
              {
                icon: <IconTrophy fontSize={30} />,
                title: '竞赛分数',
                count: profileCount.contestRating,
                loading: false,
              }
            ]}
          />
          <div>
            <ReactECharts style={{height: '200px'}} option={ratingHistory} />
          </div>
        </Card>
        <Divider type='horizontal' />
        <Card
          title={(calendarSelectYear === 0 ? t['pastYear'] : calendarSelectYear) + '年度做题统计'}
          extra={
            <div>
              <Space>
                <Select style={{ width: 154 }} defaultValue={0} onChange={onCalendarSelectChange}>
                  <Select.Option value={0}>
                    {t['pastYear']}
                  </Select.Option>
                  {calendarOptions.map((option, index) => (
                    <Select.Option key={index} value={option.value}>
                      {option.name}
                    </Select.Option>
                  ))}
                </Select>
              </Space>
            </div>
          }
        >
          <Space style={{minWidth: '355px', marginBottom: '20px'}}>
            <Statistic title={t['problemSolved']} value={profileCalendar.totalProblemSolved} groupSeparator style={{ marginRight: 60 }} />
            <Statistic title={t['totalSubmission']} value={profileCalendar.totalSubmission} groupSeparator style={{ marginRight: 60 }} />
            <Statistic title={t['activeDays']} value={profileCalendar.totalActiveDays} groupSeparator style={{ marginRight: 60 }} />
          </Space>
          <HeatMap
            value={profileCalendar.submissionCalendar}
            width={'100%'}
            height={250}
            weekLabels={['日','一','二','三','四','五','六']}
            monthLabels={['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月']}
            rectSize={21}
            rectRender={(props, data) => {
              return (
                <Tooltip key={data.index} content={`${data.date}, ${data.count || 0} 次`}>
                  <rect {...props} />
                </Tooltip>
              );
            }}
            startDate={new Date(profileCalendar.start)}
          />
        </Card>
        <Divider type='horizontal' />
        <Card
          title='做题进度'
        >
          <Tabs type='rounded' destroyOnHide onChange={e => setProblemSolvedProgressTab(e)}>
            <Tabs.TabPane key='problemset' title='题单进度'>
              <Collapse accordion bordered={false}>
                {profileProblemsets.map((item, index) => 
                  <Collapse.Item
                    key={index}
                    name={item.id}
                    header={
                      renderItemWithResponsive(
                        <Link href={`/problemsets/${item.id}`} target='_blank'>{item.name}</Link>,
                        <Progress percent={item.total === 0 ? 0 : Number(Number(item.count * 100 / item.total).toFixed(0))} />,
                        <span>{item.count} / {item.total}</span>
                      )
                    }
                  >
                    <Space wrap>
                      {item.problems.map((problem, index) => (
                        <Link key={index} href={`/problemsets/${item.id}/problems/${problem.id}`}><Tag color={Color[problem.status]}>{problem.id}</Tag></Link>
                      ))}
                    </Space>
                  </Collapse.Item>
                )}
              </Collapse>
            </Tabs.TabPane>
            <Tabs.TabPane key='contest' title='比赛进度'>
              <Collapse accordion bordered={false}>
                {profileContests.map((item, index) => 
                  <Collapse.Item
                    key={index}
                    name={item.id}
                    header={
                      renderItemWithResponsive(
                        <>
                          {item.groupName !== '' && (
                            <>
                              <Link href={`/groups/${item.groupId}`} target='_blank'>{item.groupName}</Link>
                              <Divider type='vertical' />
                            </>
                          )}
                          {<Link href={`/contests/${item.id}`} target='_blank'>{item.name}</Link>}
                        </>,
                        <Progress percent={item.total === 0 ? 0 : Number(Number(item.count * 100 / item.total).toFixed(0))} />,
                        <span>{item.count} / {item.total}</span>
                      )
                    }
                  >
                    <Space wrap>
                      {item.problems.map((problem, index) => (
                        <Link key={index} href={`/contests/${item.id}`}>
                          <Tag color={Color[problem.status]}>{String.fromCharCode(65 + problem.id)}</Tag>
                        </Link>
                      ))}
                    </Space>
                  </Collapse.Item>
                )}
              </Collapse>
              <Pagination {...pagination} />
            </Tabs.TabPane>
            <Tabs.TabPane key='group' title='小组进度'>
              <Collapse accordion bordered={false}>
                {profileGroups.map((item, index) => 
                  <Collapse.Item
                    key={index}
                    name={item.id}
                    header={
                      renderItemWithResponsive(
                        <Link href={`/groups/${item.id}`} target='_blank'>{item.name}</Link>,
                        <Progress percent={item.total === 0 ? 0 : Number(Number(item.count * 100 / item.total).toFixed(0))} />,
                        <span>{item.count} / {item.total}</span>
                      )
                    }
                  >
                    <Collapse accordion bordered={false}>
                      {item.contests.map((contest, index) => 
                        <Collapse.Item
                          key={index}
                          name={contest.id}
                          header={
                            renderItemWithResponsive(
                              <Link href={`/contests/${contest.id}`} target='_blank'>{contest.name}</Link>,
                              <Progress percent={contest.total === 0 ? 0 : Number(Number(contest.count * 100 / contest.total).toFixed(0))} />,
                              <span>{contest.count} / {contest.total}</span>
                            )
                          }
                        >
                          <Space wrap>
                            {contest.problems.map((problem, index) => (
                              <Link key={index} href={`/contests/${contest.id}`}>
                                <Tag color={Color[problem.status]}>{String.fromCharCode(65 + problem.id)}</Tag>
                              </Link>
                            ))}
                          </Space>
                        </Collapse.Item>
                      )}
                    </Collapse>
                  </Collapse.Item>
                )}
              </Collapse>
              <Pagination {...pagination} />
            </Tabs.TabPane>
          </Tabs>
        </Card>
        <Divider type='horizontal' />
        <Card title='最近提交' className='mobile-hide'>
          <RecentlySubmission userId={Number(id)} />
        </Card>
      </div>
    </>
  );
}
