import Submission from '@/modules/submission/Submission';
import { useRouter } from 'next/router';

export default () => {
  const router = useRouter();
  return (
    <div className='container'>
      <Submission id={Number(router.query.id)} />
    </div>
  );
};
