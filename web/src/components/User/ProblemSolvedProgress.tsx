import React, { useEffect, useState } from 'react';
import { Card, Tabs, Collapse, Progress, Space, Tag, Divider, Pagination, Grid } from '@arco-design/web-react';
import { PaginationProps } from '@arco-design/web-react/es/Pagination/pagination';
import Link from 'next/link';
import { getUserProfileProblemSolved } from '@/api/user';

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

export default function ProblemSolvedProgress({id}:{id:Number}) {
  const [profileProblemsets, setProfileProblemsets] = useState([]);
  const [profileContests, setProfileContests] = useState([]);
  const [profileGroups, setProfileGroups] = useState([]);
  const [problemSolvedProgressTab, setProblemSolvedProgressTab] = useState('problemset');
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
  useEffect(() => {
    if (id === 0) {
      return;
    }
    getUserProfileProblemSolved(id, {type: 'PROBLEMSET'})
    .then(res => {
      setProfileProblemsets(res.data.problemsets);
    });
  }, [id]);
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
  return (
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
  );
}
