import React, { useEffect, useState } from 'react';
import { Card, Divider, Typography } from '@arco-design/web-react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { useRouter } from 'next/router';
import { getProblemset, listProblemsetProblems } from '@/api/problemset';
import SimpleProblemList from '@/modules/problemsets/list';
import ExamProblemList from '@/modules/problemsets/ExamProblemList';
import Head from 'next/head';
import useLocale from '@/utils/useLocale';
import styles from './style/index.module.less';
import locale from './locale';

function ExamProblem({problemsetID}: {problemsetID:number}) {
  const [problems, setProblems] = useState([]);
  useEffect(() => {
    listProblemsetProblems(problemsetID, {perPage: 100}).then(res => setProblems(res.data.data));
  }, []);
  return (
    <Card>
      <ExamProblemList problems={problems} />
    </Card>
  );
}

function Problem() {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [problemset, setProblemset] = useState({id: 0, name: '', description: '', type: '', user: {id:0}});
  const settings = useAppSelector<SettingState>(setting);
  useEffect(() => {
    fetchData();
  }, []);
  function fetchData() {
    getProblemset(id)
      .then((res) => {
        setProblemset(res.data);
      });
  }
  return (
    <div className='container'>
      <Head>
        <title>{`${problemset.name} - ${t['page.title']} - ${settings.name}`}</title>
      </Head>
      <div>
        <div className={styles['header']}>
          <div>
            <Typography.Title>
              {problemset.name}
            </Typography.Title>
          </div>
          <div>{problemset.description}</div>
        </div>
        <Divider />
        {problemset.type === 'SIMPLE' ? (
          <SimpleProblemList problemsetID={Number(id)} />
        ) : (
          <ExamProblem problemsetID={Number(id)} />
        )}
      </div>
    </div>
  );
}

export default Problem;
