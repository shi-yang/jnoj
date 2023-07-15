import { getContestProblem } from '@/api/contest';
import React, { useContext, useEffect, useState } from 'react';
import styles from '../../style/problem.module.less';
import { Grid, ResizeBox, Typography } from '@arco-design/web-react';
import Editor from './editor';
import ProblemContent from '@/modules/problem/ProblemContent';
import ContestContext from '../../context';
import ContestLayout from '../../Layout';
import { useRouter } from 'next/router';

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
      .catch(err => {
        console.log(err);
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
      { !loading && (
        <div className={styles['problem-layout']}>
          <Grid.Row className={styles.header} justify='space-between' align='center'>
            <Grid.Col span={24}>
              <Typography.Title className={styles.title} heading={5}>
                {pid} - {problem.statements[language].name}
              </Typography.Title>
            </Grid.Col>
          </Grid.Row>
          <ResizeBox.Split
            max={0.8}
            min={0.2}
            className={styles['resize-box']}
            panes={[
              <div key='first' className={styles.left}>
                {!loading && (
                  <div className={styles['description-content']}>
                    <ProblemContent problem={problem} language={language}></ProblemContent>
                  </div>
                )}
              </div>,
              <div key='second' className={styles.right}>
                <Editor problem={problem} language={language} contest={contest} />
              </div>,
            ]}
          />
        </div>
      )}
    </div>
  );
}

Problem.getLayout = ContestLayout;
export default Problem;
