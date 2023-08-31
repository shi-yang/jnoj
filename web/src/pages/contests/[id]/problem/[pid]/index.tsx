import { getContestProblem } from '@/api/contest';
import React, { useContext, useEffect, useState } from 'react';
import styles from '../../style/problem.module.less';
import { Divider, Empty, Grid, Link, List, Typography } from '@arco-design/web-react';
import Editor from './editor';
import ProblemContent from '@/modules/problem/ProblemContent';
import ContestContext from '../../context';
import ContestLayout from '../../Layout';
import { useRouter } from 'next/router';
import { ProblemStatus } from '@/modules/problemsets/list/constants';

function Problem() {
  const contest = useContext(ContestContext);
  const router = useRouter();
  const [loading, setLoading] = useState(true);
  const [problem, setProblem] = useState({
    statements: [],
    timeLimit: 0,
    memoryLimit: 0,
    sampleTests: []
  });
  const [language, setLanguage] = useState(0);
  const { pid } = router.query;
  function fetchData() {
    setLoading(true);
    getContestProblem(contest.id, String(pid).charCodeAt(0) - 65)
      .then((res) => {
        setProblem(res.data);
      })
      .finally(() => {
        setLoading(false);
      });
  }
  useEffect(() => {
    fetchData();
  }, [contest.id, pid]);
  return (
    <div className={styles['container']}>
      {problem.statements.length > 0 ? (
        <Grid.Row>
          <Grid.Col span={20}>
            { !loading && (
              <div className={styles['problem-layout']}>
                <Grid.Row className={styles.header} justify='space-between' align='center'>
                  <Grid.Col span={24}>
                    <Typography.Title className={styles.title} heading={5}>
                      {pid} - {problem.statements[language].name}
                    </Typography.Title>
                  </Grid.Col>
                </Grid.Row>
                <div className={styles['description-content']}>
                  <ProblemContent problem={problem} statement={problem.statements[language]}></ProblemContent>
                </div>
                <Divider orientation='left'>提交代码</Divider>
                <div style={{height: '600px'}}>
                  <Editor problem={problem} language={language} contest={contest} />
                </div>
              </div>
            )}
          </Grid.Col>
          <Grid.Col span={4}>
            <List
              size='small'
              header={<div>题目列表</div>}
              dataSource={contest.problems}
              render={(item, index) => (
                <List.Item
                  key={index}
                  actions={[
                    <span key='status'>{ProblemStatus[item.status]}</span>
                  ]}
                >
                  <Link href={`/contests/${contest.id}/problem/${String.fromCharCode(65 + item.number)}`}>
                    <Typography.Text ellipsis style={{margin: 0}}>{String.fromCharCode(65 + item.number) + '.' + item.name}</Typography.Text>
                  </Link>
                </List.Item>
              )}
            >
            </List>
          </Grid.Col>
        </Grid.Row>
      ) : (
        <Empty />
      )}
    </div>
  );
}

Problem.getLayout = ContestLayout;
export default Problem;
