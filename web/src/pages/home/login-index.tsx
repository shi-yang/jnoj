import ProblemSolvedProgress from '@/components/User/ProblemSolvedProgress';
import SubmissionCalHeatmap from '@/components/User/SubmissionCalHeatmap';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { isLogged } from '@/utils/auth';
import { Divider } from '@arco-design/web-react';
import React, { useEffect, useState } from 'react';

export default function LoginIndex() {
  const user = useAppSelector(userInfo);
  const [isMounted, setIsMounted] = useState(false);
  useEffect(() => {
    setIsMounted(true);
  }, []);
  return (
    <div className='container mt-5'>
      {isMounted && user.id && (
        <>
          <SubmissionCalHeatmap id={user.id} />
          <Divider type='horizontal' />
          <ProblemSolvedProgress id={user.id} />
        </>
      )}
    </div>
  );
}
