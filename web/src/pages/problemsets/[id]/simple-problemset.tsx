import React, { useEffect, useState } from 'react';
import { Card, Divider, Typography } from '@arco-design/web-react';
import { useRouter } from 'next/router';
import SimpleProblemList from '@/modules/problemsets/list';
import useLocale from '@/utils/useLocale';
import styles from './style/index.module.less';
import locale from './locale';

function Problem({problemset}: {problemset:any}) {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  return (
    <div className='container'>
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
        <SimpleProblemList problemsetID={Number(id)} />
      </div>
    </div>
  );
}

export default Problem;
