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
import Join from './join';

function Page() {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [problemset, setProblemset] = useState({id: 0, name: '', description: '', type: '', membership: '', user: {id:0}, role: 'GUEST'});
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
    <div className='container' style={{padding: '20px'}}>
      <Head>
        <title>{`${problemset.name} - ${t['page.title']} - ${settings.name}`}</title>
      </Head>
      {problemset.id !== 0 && (
        <>
          {problemset.role === 'GUEST' && problemset.membership === 'INVITATION_CODE' ? (
            <div>
              <Join problemset={problemset} />
            </div>
          ) : (
            <div>
              {problemset.type === 'SIMPLE' ? (
                <SimpleProblemset problemset={problemset} />
              ) : (
                <ExamProblemset problemset={problemset} />
              )}
            </div>
          )}
        </>
      )}

    </div>
  );
}

export default Page;
