import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import { getUserProfile,  getUserProfileCount, getUsers, listUserProfileUserBadges } from '@/api/user';
import {
  Avatar, Button, Card, Descriptions, Divider, Grid, Image, Link, List, Modal, PageHeader, PaginationProps,
  Space, Tooltip, Typography
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
import SubmissionCalHeatmap from '@/components/User/SubmissionCalHeatmap';
import ProblemSolvedProgress from '@/components/User/ProblemSolvedProgress';
import { Line, XAxis, Tooltip as Rtooltip, YAxis, CartesianGrid, Legend, ResponsiveContainer, LineChart } from 'recharts';

function RecentlySubmission({userId}: {userId: number}) {
  const [data, setData] = useState([]);
  useEffect(() => {
    if (userId === 0) {
      return;
    }
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

export default function UserPage() {
  const router = useRouter();
  const t = useLocale(locale);
  const [id, setId] = useState(0);
  const [user, setUser] = useState({username: '', nickname: '', avatar: '', role: ''});
  const settings = useAppSelector<SettingState>(setting);
  const [profile, setProfile] = useState({
    bio: '',
    location: '',
    school: '',
    gender: 0,
  });
  const [profileDescriptionData, setProfileDescriptionData] = useState([]);
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
  const [ratingHistory, setRatingHistory] = useState([]);

  async function fetchData() {
    const response = await getUsers(router.query.id);
    setUser(response.data);
    setId(response.data.id);

    getUserProfile(response.data.id)
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
    listUserProfileUserBadges(response.data.id)
      .then(res => {
        setProfileUserBadges(res.data.data);
      });
    getUserProfileCount(response.data.id).then(res => {
      setProfileCount({
        contestRating: res.data.contestRating,
        problemSolved: res.data.problemSolved,
      });
      setRatingHistory(res.data.contestRankingHistory.map(item => ({
        name: item.name,
        rating: item.rating
      })));
    });
  }

  useEffect(() => {
    fetchData();
  }, [router.query.id]);
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
                        <ResponsiveContainer width="100%" height={150}>
                          <LineChart
                            data={ratingHistory}
                          >
                            <CartesianGrid strokeDasharray="3 3" />
                            <YAxis dataKey="rating" />
                            <Legend />
                            <Rtooltip />
                            <Line dataKey="rating" fill="#2d62f8"/>
                          </LineChart>
                        </ResponsiveContainer>
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
            <SubmissionCalHeatmap id={id} />
            <Divider type='horizontal' />
            {id !== 0 && <ProblemSolvedProgress id={id} />}
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
