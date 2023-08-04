import React, { useEffect, useState } from 'react';
import { Card, Divider, PageHeader, Typography } from '@arco-design/web-react';
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
    <div>
      <PageHeader title={problemset.name} style={{ background: 'var(--color-bg-2)' }}>
        {problemset.description}
      </PageHeader>
      <Divider />
      <SimpleProblemList problemsetID={Number(id)} />
    </div>
  );
}

export default Problem;
