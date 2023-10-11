import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import { getUserProfile, getUserProfileCalendar, getUserProfileCount, getUserProfileProblemSolved, getUsers, listUserProfileUserBadges } from '@/api/user';
import {
  Avatar, Button, Card, Collapse, Descriptions, Divider, Grid, Image, Link, List, Modal, PageHeader, Pagination, PaginationProps,
  Progress, Select, Space, Statistic, Tabs, Tag, Tooltip, Typography
} from '@arco-design/web-react';
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
import { IconBook, IconFile, IconLocation, IconMan, IconMore, IconTrophy, IconUserGroup, IconWoman } from '@arco-design/web-react/icon';
import ReactECharts from 'echarts-for-react';
import CalHeatmap from 'cal-heatmap';
import 'cal-heatmap/cal-heatmap.css';
// @ts-ignore https://github.com/wa0x6e/cal-heatmap/issues/366
import CalTooltip from 'cal-heatmap/plugins/Tooltip';

function RecentlySubmission({userId}: {userId: number}) {
  const [data, setData] = useState([]);
  useEffect(() => {
    const params = {
      page: 1,
      perPage: 10,
      userId: userId,
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

export enum UserBadgeType {
  ACTIVITY = 'ACTIVITY', // 活动勋章
  LEVEL = 'LEVEL', // 等级勋章
  CONTEST = 'CONTEST' // 竞赛勋章
};

function UserBadageListModal({userBadges}: {userBadges: any[]}) {
  const [visible, setVisible] = useState(false);
  return (
    <>
      <Tooltip content='查看更多' position='bottom' >
        <Button
          size='large'
          shape='circle'
          long type='text'
          icon={<IconMore />}
          onClick={(e) => setVisible(true)}
          style={{width: '80px', height: '80px'}}
        />
      </Tooltip>
      <Modal
        visible={visible}
        onCancel={() =>setVisible(false)}
        footer={null}
      >
        <Card title='活动勋章'>
          <Grid.Row style={{textAlign: 'center'}}>
            {userBadges.filter(item => item.type === UserBadgeType.ACTIVITY).map((item, index) => (
              <Grid.Col key={index} span={6}>
                <Image
                  width={80}
                  src={item.image}
                  title={item.name}
                  description={FormatTime(item.createdAt, 'YYYY-MM-DD')}
                  footerPosition='outer'
                  alt='lamp'
                  previewProps={{
                    src: item.imageGif,
                  }}
                />
              </Grid.Col>
            ))}
          </Grid.Row>
        </Card>
        <Card title='等级勋章'>
          <Grid.Row style={{textAlign: 'center'}}>
            {userBadges.filter(item => item.type === UserBadgeType.LEVEL).map((item, index) => (
              <Grid.Col key={index} span={6}>
                <Image
                  width={80}
                  src={item.image}
                  title={item.name}
                  description={FormatTime(item.createdAt, 'YYYY-MM-DD')}
                  footerPosition='outer'
                  alt='lamp'
                  previewProps={{
                    src: item.imageGif,
                  }}
                />
              </Grid.Col>
            ))}
          </Grid.Row>
        </Card>
        <Card title='竞赛勋章'>
          <Grid.Row style={{textAlign: 'center'}}>
            {userBadges.filter(item => item.type === UserBadgeType.CONTEST).map((item, index) => (
              <Grid.Col key={index} span={6}>
                <Image
                  width={80}
                  src={item.image}
                  title={item.name}
                  description={FormatTime(item.createdAt, 'YYYY-MM-DD')}
                  footerPosition='outer'
                  alt='lamp'
                  previewProps={{
                    src: item.imageGif,
                  }}
                />
              </Grid.Col>
            ))}
          </Grid.Row>
        </Card>
      </Modal>
    </>
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

function SubmissionCalHeatmap() {
  const router = useRouter();
  const t = useLocale(locale);
  const { id } = router.query;
  const [calendarSelectYear, setCalendarSelectYear] = useState(0);
  const [calendarOptions, setCalendarOptions] = useState([]);
  const [profileCalendar, setProfileCalendar] = useState({
    submissionCalendar: [],
    totalSubmission: 0,
    totalProblemSolved: 0,
    totalActiveDays: 0,
    start: '',
    end: '',
  });
  const cal = new CalHeatmap();
  useEffect(() => {
    getUserProfileCalendar(id).
      then(res => {
        const { data } = res;
        setProfileCalendar(data);
        paint(data);
        data.activeYears.forEach(item => {
          setCalendarOptions(current => [...current, {
            name: item,
            value: item
          }]);
        });
      });
  }, [id]);
  function paint(data:any) {
    const div = document.getElementById('cal-heatmap');
    if (div) {
      while (div.firstChild) {
        div.removeChild(div.firstChild);
      }
    }
    cal.paint(
      {
        data: {
          source: data.submissionCalendar,
          x: 'date',
          y: 'count',
        },
        date: { start: new Date(data.start), locale: 'zh' },
        range: 12,
        animationDuration: 100,
        scale: { color: { type: 'diverging', scheme: 'PRGn', domain: [-10, 15] } },
        domain: {
          type: 'month',
        },
        subDomain: { type: 'day', radius: 2, height: 12, width: 12 },
        itemSelector: '#cal-heatmap',
      },
      [
        [
          CalTooltip,
          {
            // @ts-ignore
            text: function (date, value, dayjsDate) {
              return (
                (value ? value + '次提交' : '没有提交') + ' - ' + dayjsDate.format('LL')
              );
            },
          },
        ],
      ]
    );
  }
  function onCalendarSelectChange(e) {
    setCalendarSelectYear(e);
    getUserProfileCalendar(id, { year: e })
      .then(res => {
        const { data } = res;
        setProfileCalendar(data);
        paint(data);
      });
  }
  return (
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
      <div id="cal-heatmap"></div>
    </Card>
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
  const [user, setUser] = useState({username: '', nickname: '', avatar: '', role: ''});
  const settings = useAppSelector<SettingState>(setting);
  const [profile, setProfile] = useState({
    bio: '',
    location: '',
    school: '',
    gender: 0,
  });
  const [profileDescriptionData, setProfileDescriptionData] = useState([]);
  const [problemSolvedProgressTab, setProblemSolvedProgressTab] = useState('problemset');
  const [profileProblemsets, setProfileProblemsets] = useState([]);
  const [profileContests, setProfileContests] = useState([]);
  const [profileGroups, setProfileGroups] = useState([]);
  const [profileUserBadges, setProfileUserBadges] = useState([]);
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
  useEffect(() => {
    const { current, pageSize } = pagination;
    if (problemSolvedProgressTab === 'problemset') {
      getUserProfileProblemSolved(id, {type: 'PROBLEMSET', page: current, perPage: pageSize})
        .then(res => {
          setProfileProblemsets(res.data.problemsets);
          setPagination({
            ...pagination,
            current,
            pageSize,
            total: res.data.total,
          });
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
    getUserProfile(id)
      .then(res => {
        const { data } = res;
        setProfile(data);
        const arr = [];
        if (data.location !== '') {
          arr.push({
            label: <IconLocation />,
            value: data.location
          });
        }
        if (data.school !== '') {
          arr.push({
            label: <IconBook />,
            value: data.school
          });
        }
        if (data.gender !== 0) {
          arr.push({
            label: <IconUserGroup />,
            value: data.gender === 1 ? <span><IconMan /> 男</span> : <span><IconWoman /> 女</span>
          });
        }
        setProfileDescriptionData(arr);
      });
    listUserProfileUserBadges(Number(id))
      .then(res => {
        setProfileUserBadges(res.data.data);
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
        <div>
          <PageHeader
            title={
              <div>
                {user.avatar !== '' && (
                  <Avatar size={80}>
                    <img src={user.avatar} alt='avatar' />
                  </Avatar>
                )} {user.nickname}
              </div>
            }
            subTitle={user.username}
            extra={
              <div>
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
            }
          />
        </div>
        <Grid.Row gutter={[24, 12]}>
          <Grid.Col xs={24} md={6}>
            <Card title='个人简介'>
              <Typography.Paragraph>{profile.bio}</Typography.Paragraph>
              <Descriptions
                column={1}
                data={profileDescriptionData}
                labelStyle={{ textAlign: 'right', paddingRight: 36 }}
              />
            </Card>
          </Grid.Col>
          <Grid.Col xs={24} md={18}>
            <Grid.Row gutter={24}>
              <Grid.Col span={12}>
                <Card title='做题'>
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
                  {
                    profileCount.contestRating !== 0 && (
                      <div>
                        <ReactECharts style={{height: '200px'}} option={ratingHistory} />
                      </div>
                    )
                  }
                </Card>
              </Grid.Col>
              <Grid.Col span={12}>
                <Card title='勋章成就'>
                  <Grid.Row style={{textAlign: 'center'}}>
                    <Grid.Col flex='auto'>
                      <Grid.Row justify='center'>
                      {profileUserBadges.length > 0 && (
                        <Grid.Col span={8}>
                          <Image
                            width={80}
                            src={profileUserBadges[0].image}
                            title={profileUserBadges[0].name}
                            description={FormatTime(profileUserBadges[0].createdAt, 'YYYY-MM-DD')}
                            footerPosition='outer'
                            alt='lamp'
                            previewProps={{
                              src: profileUserBadges[0].imageGif,
                            }}
                          />
                        </Grid.Col>
                      )}
                      {profileUserBadges.length > 1 && (
                        <Grid.Col span={8}>
                          <Image
                            width={80}
                            src={profileUserBadges[1].image}
                            title={profileUserBadges[1].name}
                            description={FormatTime(profileUserBadges[1].createdAt, 'YYYY-MM-DD')}
                            footerPosition='outer'
                            alt='lamp'
                            previewProps={{
                              src: profileUserBadges[1].imageGif,
                            }}
                          />
                        </Grid.Col>
                      )}
                      {profileUserBadges.length > 2 && (
                        <Grid.Col span={8}>
                          <Image
                            width={80}
                            src={profileUserBadges[2].image}
                            title={profileUserBadges[2].name}
                            description={FormatTime(profileUserBadges[2].createdAt, 'YYYY-MM-DD')}
                            footerPosition='outer'
                            alt='lamp'
                            previewProps={{
                              src: profileUserBadges[2].imageGif,
                            }}
                          />
                        </Grid.Col>
                      )}
                      </Grid.Row>
                    </Grid.Col>
                    <Grid.Col flex='100px'>
                      <UserBadageListModal userBadges={profileUserBadges} />
                    </Grid.Col>
                  </Grid.Row>
                </Card>
              </Grid.Col>
            </Grid.Row>
            <Divider type='horizontal' />
            <SubmissionCalHeatmap />
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
          </Grid.Col>
        </Grid.Row>
      </div>
    </>
  );
}
