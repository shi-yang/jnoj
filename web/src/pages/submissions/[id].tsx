import Submission from '@/modules/submission/Submission';
import { Card } from '@arco-design/web-react';
import Head from 'next/head';
import { useRouter } from 'next/router';

export default () => {
  const router = useRouter();
  return (
    <Card className='container'>
      <Head>
        <title>提交记录</title>
      </Head>
      <Submission id={Number(router.query.id)} />
    </Card>
  );
};
