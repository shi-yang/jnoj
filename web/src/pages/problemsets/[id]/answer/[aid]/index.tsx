import React, { useEffect, useState } from 'react';
import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { useRouter } from 'next/router';
import { getProblemset, getProblemsetAnswer, listProblemsetProblems } from '@/api/problemset';
import Head from 'next/head';
import { Card, Divider, Link, Typography } from '@arco-design/web-react';
import ExamProblemsList from '@/modules/problemsets/ExamProblemList';
function Page() {
  const router = useRouter();
  const { id, aid } = router.query;
  const [problemset, setProblemset] = useState({id: 0, name: '', description: '', type: '', user: {id:0}});
  const settings = useAppSelector<SettingState>(setting);
  const [answer, setAnswer] = useState({});
  const [problems, setProblems] = useState([]);
  useEffect(() => {
    fetchData();
  }, []);
  function fetchData() {
    listProblemsetProblems(id, {perPage: 100})
      .then(res => {
        setProblems(res.data.data);
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
      });
  }
  return (
    <div className='container'>
      <Head>
        <title>{`${problemset.name} - ${settings.name}`}</title>
      </Head>
      <>
        <div className='container'>
          <div>
            <div>
              <Link href={`/problemsets/${id}`}>
                <Typography.Title>
                  {problemset.name}
                </Typography.Title>
              </Link>
              <div>{problemset.description}</div>
            </div>
            <Divider />
            <Card>
              <ExamProblemsList problems={problems} answer={answer} />
            </Card>
          </div>
        </div>
      </>
    </div>
  );
}

export default Page;
