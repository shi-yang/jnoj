import React, { useEffect, useState } from 'react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { useRouter } from 'next/router';
import { getProblemset, getProblemsetAnswer, listProblemsetProblems } from '@/api/problemset';
import Head from 'next/head';
import { Anchor, Button, Card, Divider, Link, Typography } from '@arco-design/web-react';
import ExamProblemsList from '@/modules/problemsets/ExamProblemList';
import styles from './styles/index.module.less';
const AnchorLink = Anchor.Link;

function AnswerSheet({problems, answerState}: {problems:any[], answerState: any}) {
  return (
    <div className={styles['answer-sheet-container']}>
      <div className='container'>
        <div className='shadow-sm border-solid border border-slate-100'>
          <Anchor
            affix={false}
            lineless
            direction='horizontal'
            className={styles['arco-anchor-list']}
            style={{ backgroundColor: 'var(--color-bg-2)' }}
          >
            {problems.map((item, index) => (
              <AnchorLink
                key={index}
                className='m-0'
                href={`#problem-${item.problemId}`}
                title={
                  <Button shape='circle' status={answerState[item.problemId] && answerState[item.problemId] === 'correct' ? 'success' : 'danger'}>{index + 1}</Button>
                }
              />
            ))}
          </Anchor>
        </div>
      </div>
    </div>
  );
}

function Page() {
  const router = useRouter();
  const { id, aid } = router.query;
  const [problemset, setProblemset] = useState({id: 0, name: '', description: '', type: '', user: {id:0}});
  const settings = useAppSelector<SettingState>(setting);
  const [answer, setAnswer] = useState({});
  const [problems, setProblems] = useState([]);
  const [answerState, setAnswerState] = useState({});
  const [submissions, setSubmissions] = useState({});
  useEffect(() => {
    fetchData();
  }, []);
  function fetchData() {
    listProblemsetProblems(id, {perPage: 100})
      .then(res => {
        setProblems(res.data.problems);
      });
    getProblemset(id)
      .then((res) => {
        setProblemset(res.data);
      });
    getProblemsetAnswer(id, aid)
      .then((res) => {
        if (res.data.answer !== '') {
          setAnswer(JSON.parse(res.data.answer));
        }
        const state = {};
        if (res.data.correctProblemIds !== '') {
          res.data.correctProblemIds.split(',').forEach(item => {
            state[item] = 'correct';
          });
        }
        if (res.data.wrongProblemIds !== '') {
          res.data.wrongProblemIds.split(',').forEach(item => {
            state[item] = 'wrong';
          });
        }
        if (res.data.unansweredProblemIds !== '') {
          res.data.unansweredProblemIds.split(',').forEach(item => {
            state[item] = 'unAnswered';
          });
        }
        setAnswerState(state);
        // 处理提交
        const subs = {};
        res.data.submissions.forEach(item => {
          subs[item.problemId] = item;
        });
        setSubmissions(subs);
      });
  }
  return (
    <>
      <Head>
        <title>{`${problemset.name} - ${settings.name}`}</title>
      </Head>
      <div className='container'>
        <div>
          <div>
            <Link href={`/problemsets/${id}`}>
              <Typography.Title heading={5}>
                {problemset.name}
              </Typography.Title>
            </Link>
            <div>{problemset.description}</div>
          </div>
          <Divider />
          <Card>
            <AnswerSheet problems={problems} answerState={answerState} />
            <div className='pb-10'>
              <ExamProblemsList problems={problems} answer={answer} submissions={submissions} />
            </div>
          </Card>
        </div>
      </div>
    </>
  );
}

export default Page;
