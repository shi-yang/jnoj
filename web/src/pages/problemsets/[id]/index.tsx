import React, { useEffect, useState } from 'react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { useRouter } from 'next/router';
import { getProblemset } from '@/api/problemset';
import Head from 'next/head';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import SimpleProblemset from './simple-problemset';
import ExamProblemset from './exam-problemset';

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
      {problemset.id !== 0 && (
        problemset.type === 'SIMPLE' ? (
          <SimpleProblemset problemset={problemset} />
        ) : (
          <ExamProblemset problemset={problemset} />
        )
      )}
    </div>
  );
}

export default Problem;
